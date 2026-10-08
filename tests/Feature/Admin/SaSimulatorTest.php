<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\SaSimulator;
use App\Models\User;
use App\Services\Admin\SaSimulatorService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class SaSimulatorTest extends TestCase
{
    use \Tests\Support\CreatesSaChatSchema;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSaChatSchema();
        config(['admin_migration.sa_commands_enabled' => false]);
        $this->editor = \Mockery::mock(User::class)->makePartial();
        $this->editor->id = 123;
        $this->editor->shouldReceive('hasPermission')->andReturn(true);
    }

    public function test_inbound_success_validation_and_duplicate_use_existing_api(): void
    {
        $service = app(SaSimulatorService::class);
        $payload = json_encode(['event_id' => (string) Str::uuid(), 'event_type' => 'message.created',
            'idempotency_key' => 'simulator-test', 'occurred_at' => now()->toIso8601String(), 'source' => 'SA',
            'data' => ['conversation_id' => 'SIM-TEST', 'channel' => 'whatsapp', 'message' => [
                'message_id' => 'SIM-MESSAGE', 'direction' => 'inbound', 'from' => ['type' => 'client', 'phone' => '+12025550123', 'name' => 'Test'],
                'to' => ['type' => 'agent', 'id' => 'BOT'], 'text' => 'Simulator test', 'attachments' => [],
                'sent_at' => now()->toIso8601String(), 'status' => 'received']]]);
        $original = app('request');
        $this->assertSame('ok', $service->execute($this->editor, '/api/sa/webhooks/messages', $payload)['api_response']['status']);
        $this->assertSame('duplicate', $service->execute($this->editor, '/api/sa/webhooks/messages', $payload)['api_response']['status']);
        $invalid = $service->execute($this->editor, '/api/sa/webhooks/messages', '{}');
        $this->assertSame('error', $invalid['api_response']['status']);
        $this->assertSame($original, app('request'));
        $this->assertDatabaseCount('sa_messages', 1);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_disallowed_urls_invalid_json_and_disabled_outbound_do_not_dispatch(): void
    {
        foreach ([['https://example.com/api/sa/leads', '{}'], ['/api/sa/leads/1/../2', '{}'], ['/api/sa/leads', 'broken'],
            ['/api/sa/leads', '[]'], ['/api/crm/webhooks/send-message', '{}'], ['/api/blog/media', '{}']] as [$endpoint, $payload]) {
            try { app(SaSimulatorService::class)->execute($this->editor, $endpoint, $payload); $this->fail('Invalid request dispatched'); }
            catch (ValidationException $exception) { $this->assertNotEmpty($exception->errors()); }
        }
        $this->assertDatabaseCount('sa_events', 0);
        Http::assertNothingSent();
    }

    public function test_missing_key_keeps_api_authentication_and_reader_cannot_execute(): void
    {
        config(['services.sa_integration.api_key' => '']);
        $this->assertSame(401, app(SaSimulatorService::class)->execute($this->editor, '/api/sa/webhooks/messages', '{}')['http_code']);
        $reader = \Mockery::mock(User::class)->makePartial();
        $reader->shouldReceive('hasPermission')->andReturn(false);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(SaSimulatorService::class)->execute($reader, '/api/sa/webhooks/messages', '{}');
    }

    public function test_get_catalog_selects_get_and_merges_query_without_exposing_key(): void
    {
        $this->mock(\App\Http\Controllers\Api\SaIntegrationController::class, function ($mock): void {
            $mock->shouldReceive('servicesCatalog')->once()->withArgs(fn ($request) => $request->method() === 'GET' && $request->input('lang') === 'ru')
                ->andReturn(response()->json(['status' => 'ok', 'data' => []]));
        });
        $result = app(SaSimulatorService::class)->execute($this->editor, '/api/sa/services-catalog?lang=en', '{"lang":"ru"}');
        $this->assertSame(200, $result['http_code']);
        $this->assertStringNotContainsString('test-key', json_encode($result));
    }

    public function test_pipeline_change_persists_bot_mode_and_duplicate_has_no_second_control(): void
    {
        \Illuminate\Support\Facades\DB::table('orders')->insert(['id' => 1, 'status' => 'watching', 'sa_conversation_id' => 'PIPELINE-TEST', 'sa_bot_mode' => 'active']);
        \Illuminate\Support\Facades\DB::table('sa_conversations')->insert(['conversation_id' => 'PIPELINE-TEST', 'orders_id' => 1, 'bot_mode' => 'active']);
        $payload = json_encode(['event_id' => (string) Str::uuid(), 'event_type' => 'crm.lead.stage_changed',
            'idempotency_key' => 'pipeline-bot-test', 'occurred_at' => now()->toIso8601String(), 'source' => 'CRM',
            'data' => ['lead_id' => '1', 'pipeline' => ['id' => 'PIPE-1'],
                'stage' => ['from' => ['id' => 'watching'], 'to' => ['id' => 'pegging']],
                'changed_by' => ['type' => 'manager', 'id' => '123'], 'bot_control' => ['mode' => 'paused']]]);
        $service = app(SaSimulatorService::class);
        $this->assertSame(200, $service->execute($this->editor, '/api/crm/webhooks/pipeline-changed', $payload)['http_code']);
        $this->assertDatabaseHas('orders', ['id' => 1, 'status' => 'pegging', 'sa_bot_mode' => 'paused']);
        $this->assertDatabaseHas('sa_conversations', ['conversation_id' => 'PIPELINE-TEST', 'bot_mode' => 'paused']);
        $this->assertSame('duplicate', $service->execute($this->editor, '/api/crm/webhooks/pipeline-changed', $payload)['api_response']['status']);
        $this->assertDatabaseCount('sa_bot_controls', 1);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_enabled_outbound_still_rejects_uat_conversation(): void
    {
        config(['admin_migration.sa_commands_enabled' => true]);
        try {
            app(SaSimulatorService::class)->execute($this->editor, '/api/crm/webhooks/send-message', '{"data":{"conversation_id":"ADM-FIL-UAT-18451"}}');
            $this->fail('UAT conversation dispatched');
        } catch (ValidationException $exception) { $this->assertArrayHasKey('payload', $exception->errors()); }
        Http::assertNothingSent();
        $this->assertDatabaseCount('sa_events', 0);
    }

    public function test_page_has_presets_and_curl_placeholder_and_rechecks_permission(): void
    {
        $this->mock(\App\Http\Controllers\Admin\AdminSaIntegrationController::class, fn ($mock) => $mock->shouldReceive('resolveSimulatorDefaults')->andReturn([]));
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));
        $this->actingAs($this->editor, 'filament');
        Livewire::test(SaSimulator::class)->assertSee('Готовые сценарии')->assertDontSee('test-key');
        $reader = \Mockery::mock(User::class)->makePartial();
        $reader->shouldReceive('hasPermission')->andReturn(false);
        $this->actingAs($reader, 'filament');
        Livewire::test(SaSimulator::class)->assertForbidden();
    }
}
