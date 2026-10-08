<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\SaConversations\Pages\ListSaConversations;
use App\Filament\Resources\SaConversations\Pages\ViewSaConversation;
use App\Models\Orders;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SaConversation;
use App\Models\SaMessage;
use App\Models\User;
use App\Services\Admin\SaInboxService;
use App\Services\Admin\OrderSaCommandService;
use Filament\Facades\Filament;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\CreatesSaChatSchema;
use Tests\TestCase;

class SaInboxTest extends TestCase
{
    use CreatesSaChatSchema;
    private User $editor;
    private SaConversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSaChatSchema();
        config(['admin_migration.sa_commands_enabled' => false]);
        foreach (['roles' => 'name', 'permissions' => 'key'] as $name => $field) {
            Schema::create($name, function (Blueprint $table) use ($field) {
                $table->id(); $table->string($field); $table->timestamps();
            });
        }
        Schema::create('permission_role', function (Blueprint $table) { $table->integer('role_id'); $table->integer('permission_id'); });
        Schema::create('user_roles', function (Blueprint $table) { $table->integer('user_id'); $table->integer('role_id'); });
        $this->editor = $this->user(['browse_admin', 'browse_orders', 'read_orders', 'edit_orders', 'add_orders']);
        $this->conversation = SaConversation::create(['conversation_id' => 'INBOX-TEST', 'channel' => 'whatsapp',
            'client_phone' => '+12025550123', 'client_name' => 'Inbox client', 'bot_mode' => 'paused', 'unread_for_manager' => true]);
        $this->actingAs($this->editor, 'filament');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_list_filters_search_and_card_preserve_unread(): void
    {
        $this->message('M-1');
        $other = SaConversation::create(['conversation_id' => 'OTHER', 'client_name' => 'Other', 'unread_for_manager' => false, 'orders_id' => 99]);
        Livewire::test(ListSaConversations::class)->assertCanSeeTableRecords([$this->conversation, $other])
            ->filterTable('scope', 'unread')->assertCanSeeTableRecords([$this->conversation])->assertCanNotSeeTableRecords([$other])
            ->resetTableFilters()->searchTable('Inbox client')->assertCanSeeTableRecords([$this->conversation])->assertCanNotSeeTableRecords([$other]);
        Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id])->assertSee('Incoming text');
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_bind_mirrors_once_and_rejects_conflicting_order(): void
    {
        $order = $this->order();
        $this->message('M-1');
        $this->message('M-2', ['direction' => 'outbound', 'status' => 'uat_suppressed']);
        $service = app(SaInboxService::class);
        $service->bind($this->conversation, $this->editor, $order->id);
        $service->bind($this->conversation->fresh(), $this->editor, $order->id);
        $this->assertDatabaseCount('order_user_comments', 1);
        $this->assertDatabaseHas('order_user_comments', ['order_id' => $order->id, 'sa_message_id' => 'M-1', 'admin_is_read' => 0]);
        $this->assertDatabaseHas('sa_messages', ['message_id' => 'M-2', 'orders_id' => $order->id]);
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        $this->assertSame('paused', $order->fresh()->sa_bot_mode);
        $this->expectException(ValidationException::class);
        $service->bind($this->conversation->fresh(), $this->editor, $this->order()->id);
    }

    public function test_stale_read_snapshot_preserves_new_messages(): void
    {
        $service = app(SaInboxService::class);
        $token = $service->readToken($this->conversation);
        $this->message('NEW');
        try { $service->acknowledge($this->conversation, $this->editor, $token); $this->fail('Stale read accepted'); }
        catch (ValidationException $exception) { $this->assertArrayHasKey('snapshot', $exception->errors()); }
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        $service->acknowledge($this->conversation, $this->editor, $service->readToken($this->conversation->fresh()));
        $this->assertFalse($this->conversation->fresh()->unread_for_manager);
        $this->assertSame('received', SaMessage::first()->status);
    }

    public function test_create_order_is_atomic_and_idempotent(): void
    {
        // Existing contact avoids unrelated registration notifications in this fixture.
        DB::table('users')->where('id', $this->editor->id)->update(['phone' => $this->conversation->client_phone]);
        $this->message('M-1');
        $service = app(SaInboxService::class);
        $order = $service->createOrder($this->conversation, $this->editor);
        $again = $service->createOrder($this->conversation->fresh(), $this->editor);
        $this->assertSame($order->id, $again->id);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_user_comments', 1);
        $this->assertSame('watching', $order->status);
        $this->assertSame('paused', $order->sa_bot_mode);
        $this->assertSame((int) $order->id, (int) $this->conversation->fresh()->orders_id);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_standalone_commands_use_uat_guard_and_deduplicate(): void
    {
        $service = app(OrderSaCommandService::class);
        $data = ['token' => $service->inboxToken($this->conversation, $this->editor), 'action' => 'send', 'text' => 'Hello'];
        $result = $service->executeInbox($this->conversation, $this->editor, $data);
        $again = $service->executeInbox($this->conversation, $this->editor, $data);
        $this->assertSame('uat_suppressed', $result['status']);
        $this->assertTrue($again['duplicate']);
        $this->assertDatabaseCount('sa_messages', 1);
        $this->assertDatabaseCount('order_user_comments', 0);
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        $service->executeInbox($this->conversation, $this->editor, ['token' => $service->inboxToken($this->conversation, $this->editor), 'action' => 'resume_bot']);
        $this->assertSame('paused', $this->conversation->fresh()->bot_mode);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_reply_modal_token_reaches_command_service(): void
    {
        Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id])
            ->callAction('reply', data: ['text' => 'From modal', 'handoff' => false])->assertHasNoErrors();
        $this->assertDatabaseHas('sa_messages', ['text' => 'From modal', 'status' => 'uat_suppressed']);
        Http::assertNothingSent();
    }

    public function test_inline_reply_validates_and_clears_only_confirmed_draft(): void
    {
        $page = Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id]);
        $token = $page->get('replyToken');
        $page->set('replyText', '   ')->call('sendReply')->assertHasErrors(['replyText']);
        $this->assertDatabaseCount('sa_messages', 0);
        $page->set('replyText', ' From composer ')->call('sendReply')->assertHasNoErrors()->assertSet('replyText', '');
        $this->assertNotSame($token, $page->get('replyToken'));
        $this->assertDatabaseHas('sa_messages', ['text' => 'From composer', 'status' => 'uat_suppressed']);
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_inline_reply_preserves_uncertain_draft_and_retry_is_duplicate(): void
    {
        config(['admin_migration.sa_commands_enabled' => true, 'services.synvolve.manager_message_webhook_url' => 'https://sa.test.invalid/message']);
        Http::fake(['sa.test.invalid/*' => Http::response([], 500)]);
        $page = Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id]);
        $token = $page->get('replyToken');
        $page->set('replyText', 'Uncertain reply')->call('sendReply')->assertSet('replyText', 'Uncertain reply')->assertSet('replyToken', $token);
        $page->call('sendReply')->assertSet('replyToken', $token);
        $this->assertDatabaseCount('sa_messages', 1);
        Http::assertSentCount(1);
    }

    public function test_read_only_user_cannot_call_inline_reply(): void
    {
        $this->actingAs($this->user(['browse_admin', 'browse_orders', 'read_orders']), 'filament');
        Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id])
            ->assertDontSee('Ответ менеджера')->set('replyText', 'Forbidden')->call('sendReply')->assertForbidden();
        $this->assertDatabaseCount('sa_messages', 0);
        Http::assertNothingSent();
    }

    public function test_inline_bot_control_uses_signed_command_without_live_send(): void
    {
        $token = app(OrderSaCommandService::class)->inboxToken($this->conversation, $this->editor);
        Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id])
            ->call('controlBot', 'resume_bot', $token)->assertHasNoErrors();
        $this->assertDatabaseHas('sa_events', ['event_type' => 'crm.bot_control', 'status' => 'uat_suppressed']);
        $this->assertSame('paused', $this->conversation->fresh()->bot_mode);
        Http::assertNothingSent();
        $this->actingAs($this->user(['browse_admin', 'browse_orders', 'read_orders']), 'filament');
        Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id])
            ->call('controlBot', 'resume_bot', $token)->assertForbidden();
    }

    public function test_read_only_user_cannot_bind_or_send(): void
    {
        $reader = $this->user(['browse_admin', 'browse_orders', 'read_orders']);
        $this->actingAs($reader, 'filament');
        Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id])->assertActionHidden('bind')->assertActionHidden('reply');
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(SaInboxService::class)->bind($this->conversation, $reader, $this->order()->id);
    }

    public function test_accepted_standalone_send_and_bot_use_conversation_phone(): void
    {
        config(['admin_migration.sa_commands_enabled' => true, 'services.synvolve.manager_message_webhook_url' => 'https://sa.test.invalid/message',
            'services.synvolve.bot_status_webhook_url' => 'https://sa.test.invalid/bot']);
        Http::fake(['sa.test.invalid/*' => Http::response([], 200)]);
        $service = app(OrderSaCommandService::class);
        $service->executeInbox($this->conversation, $this->editor, ['token' => $service->inboxToken($this->conversation, $this->editor), 'action' => 'send', 'text' => 'Accepted']);
        $result = $service->executeInbox($this->conversation, $this->editor, ['token' => $service->inboxToken($this->conversation->fresh(), $this->editor), 'action' => 'resume_bot']);
        $this->assertSame('accepted', $result['status']);
        $this->assertSame('active', $this->conversation->fresh()->bot_mode);
        $this->assertDatabaseCount('order_user_comments', 0);
        Http::assertSent(fn ($request) => $request['event'] === 'bot_status_changed' && $request['client_id'] === 'INBOX-TEST' && $request['phone'] === '+12025550123');
    }

    public function test_invalid_create_rolls_back_and_order_conflict_cannot_bind(): void
    {
        $this->conversation->update(['client_phone' => 'invalid']);
        try { app(SaInboxService::class)->createOrder($this->conversation, $this->editor); $this->fail('Invalid create accepted'); }
        catch (ValidationException $exception) { $this->assertArrayHasKey('conversation', $exception->errors()); }
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('sa_events', 0);
        $this->assertNull($this->conversation->fresh()->orders_id);
        $order = $this->order();
        $order->forceFill(['sa_conversation_id' => 'OTHER-CONVERSATION'])->save();
        try { app(SaInboxService::class)->bind($this->conversation, $this->editor, $order->id); $this->fail('Conflict accepted'); }
        catch (ValidationException $exception) { $this->assertArrayHasKey('order_id', $exception->errors()); }
        $this->assertNull($this->conversation->fresh()->orders_id);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_new_contact_create_and_badge_polling(): void
    {
        $order = app(SaInboxService::class)->createOrder($this->conversation, $this->editor);
        $this->assertDatabaseHas('users', ['id' => $order->user_id, 'phone' => '+12025550123', 'first_name' => 'Inbox']);
        $badge = Livewire::test(\App\Livewire\Admin\SaInboxBadge::class)->assertDispatched('sa-inbox-count', count: 1);
        $this->conversation->fresh()->update(['unread_for_manager' => false]);
        $badge->call('$refresh')->assertDispatched('sa-inbox-count', count: 0);
        $this->assertDatabaseCount('orders', 1);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_token_cannot_target_another_conversation_and_revocation_denies_page(): void
    {
        $service = app(OrderSaCommandService::class);
        $token = $service->inboxToken($this->conversation, $this->editor);
        $other = SaConversation::create(['conversation_id' => 'OTHER', 'client_phone' => '+12025550123', 'channel' => 'whatsapp']);
        try { $service->executeInbox($other, $this->editor, ['token' => $token, 'action' => 'send', 'text' => 'No']); $this->fail('Wrong conversation accepted'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) { $this->assertSame(403, $exception->getStatusCode()); }
        $this->assertDatabaseCount('sa_events', 0);
        $list = Livewire::test(ListSaConversations::class);
        $this->actingAs($this->user(['browse_admin']), 'filament');
        $list->call('$refresh')->assertForbidden();
        Livewire::test(ListSaConversations::class)->assertForbidden();
        Livewire::test(\App\Livewire\Admin\SaInboxBadge::class)->assertDontSee('SA-диалоги, непрочитанных');
        Http::assertNothingSent();
    }

    public function test_read_snapshot_only_acknowledges_displayed_messages(): void
    {
        $first = $this->message('DISPLAYED');
        $displayedConversation = $this->conversation->fresh();
        $this->message('AFTER-HISTORY');
        $service = app(SaInboxService::class);
        $token = $service->readToken($displayedConversation, $first->id);
        try { $service->acknowledge($this->conversation, $this->editor, $token); $this->fail('Unseen message was acknowledged'); }
        catch (ValidationException $exception) { $this->assertArrayHasKey('snapshot', $exception->errors()); }
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
    }

    public function test_accepted_send_updates_activity_without_acknowledging_or_overwriting_newer_message(): void
    {
        config(['admin_migration.sa_commands_enabled' => true, 'services.synvolve.manager_message_webhook_url' => 'https://sa.test.invalid/message']);
        Http::fake(['sa.test.invalid/*' => Http::response([], 200)]);
        $this->conversation->update(['last_message_at' => now()->subDays(3)]);
        $service = app(OrderSaCommandService::class);
        $service->executeInbox($this->conversation->fresh(), $this->editor, ['token' => $service->inboxToken($this->conversation->fresh(), $this->editor), 'action' => 'send', 'text' => 'First response']);
        $this->assertSame(SaMessage::first()->created_at->timestamp, $this->conversation->fresh()->last_message_at->timestamp);
        $this->assertTrue($this->conversation->fresh()->unread_for_manager);
        Http::assertSentCount(1);
        $later = now()->addMinute();
        Http::fake(function () use ($later) {
            $this->conversation->fresh()->update(['last_message_at' => $later]);
            return Http::response([], 200);
        });
        $service->executeInbox($this->conversation->fresh(), $this->editor, ['token' => $service->inboxToken($this->conversation->fresh(), $this->editor), 'action' => 'send', 'text' => 'Second response']);
        $this->assertSame($later->timestamp, $this->conversation->fresh()->last_message_at->timestamp);
        Http::assertSentCount(1);
    }

    public function test_list_order_status_history_legacy_attachments_and_unsupported_channel_actions(): void
    {
        $order = $this->order();
        $this->conversation->update(['orders_id' => $order->id]);
        $this->message('LEGACY-FILE', ['attachments_json' => json_encode([
            ['path' => 'sa/legacy-photo.jpg', 'original_name' => 'Legacy photo name'],
            ['path' => 'sa/legacy-document.pdf'],
        ])]);
        Livewire::test(ListSaConversations::class)->assertSee('watching');
        Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id])
            ->assertSee('Legacy photo name')->assertSee('legacy-document.pdf');
        $this->conversation->update(['channel' => 'email']);
        $page = Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id])
            ->assertActionHidden('reply')->assertActionHidden('bot')->assertDontSee('Ответ менеджера');
        $token = app(OrderSaCommandService::class)->inboxToken($this->conversation->fresh(), $this->editor);
        $page->call('controlBot', 'resume_bot', $token);
        $this->assertDatabaseCount('sa_events', 0);
        Http::assertNothingSent(); Mail::assertNothingSent();
    }

    public function test_partial_send_keeps_token_and_draft_and_does_not_send_twice(): void
    {
        config(['admin_migration.sa_commands_enabled' => true, 'services.synvolve.manager_message_webhook_url' => 'https://sa.test.invalid/message',
            'services.synvolve.bot_status_webhook_url' => 'https://sa.test.invalid/bot']);
        Http::fake(['sa.test.invalid/message' => Http::response([], 200), 'sa.test.invalid/bot' => Http::response([], 500)]);
        $page = Livewire::test(ViewSaConversation::class, ['record' => $this->conversation->id]);
        $token = $page->get('replyToken');
        $page->set('replyText', 'Partial response')->set('replyHandoff', true)->call('sendReply')
            ->assertSet('replyToken', $token)->assertSet('replyText', 'Partial response');
        $page->call('$refresh')->call('sendReply')->assertSet('replyToken', $token);
        $this->assertDatabaseHas('sa_events', ['event_type' => 'crm.message.send', 'status' => 'partial']);
        $this->assertDatabaseCount('sa_messages', 1);
        $this->assertSame('paused', $this->conversation->fresh()->bot_mode);
        Http::assertSentCount(2);
    }

    public function test_locally_stored_attachment_uses_local_public_disk_and_imported_path_keeps_media_base(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        config(['admin_migration.order_media_base_url' => 'https://viarcanvas.com']);
        $path = 'sa/attachments/Фото #1.jpg';
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, 'test');
        $attachment = ['disk' => 'public', 'local_path' => 'storage/'.$path, 'name' => 'Фото'];
        $file = \App\Support\Admin\SaChatAttachment::describe($attachment);
        $this->assertSame(\Illuminate\Support\Facades\Storage::disk('public')->url('sa/attachments/'.rawurlencode('Фото #1.jpg')), $file['url']);
        $this->assertTrue($file['image']);
        $imported = \App\Support\Admin\SaChatAttachment::describe(['disk' => 'public', 'local_path' => 'storage/sa/remote.pdf']);
        $this->assertSame('https://viarcanvas.com/storage/sa/remote.pdf', $imported['url']);
        $this->assertNull(\App\Support\Admin\SaChatAttachment::describe($attachment + ['status' => 'rejected'])['url']);
    }

    public function test_inbox_scope_filters_use_latest_direction_and_recent_activity(): void
    {
        $this->conversation->update(['last_message_at' => now()->subDays(2)]);
        $this->message('FIRST-IN', ['sent_at' => now()->subDays(2)]);
        $this->message('LAST-OUT', ['direction' => 'outbound', 'sent_at' => now()->subDay()]);
        $incoming = SaConversation::create(['conversation_id' => 'WAITING', 'channel' => 'email', 'bot_mode' => 'active', 'last_message_at' => now()]);
        SaMessage::create(['conversation_id' => 'WAITING', 'message_id' => 'LAST-IN', 'direction' => 'inbound', 'text' => 'Waiting', 'sent_at' => now()]);
        $linked = SaConversation::create(['conversation_id' => 'LINKED', 'orders_id' => $this->order()->id, 'last_message_at' => now()]);
        Livewire::test(ListSaConversations::class)
            ->filterTable('scope', 'awaiting_reply')->assertCanSeeTableRecords([$incoming])->assertCanNotSeeTableRecords([$this->conversation, $linked])
            ->resetTableFilters()->filterTable('scope', 'recent')->assertCanSeeTableRecords([$incoming, $linked])->assertCanNotSeeTableRecords([$this->conversation])
            ->resetTableFilters()->filterTable('scope', 'unlinked')->assertCanSeeTableRecords([$incoming, $this->conversation])->assertCanNotSeeTableRecords([$linked])
            ->resetTableFilters()->filterTable('channel', 'email')->assertCanSeeTableRecords([$incoming])->assertCanNotSeeTableRecords([$this->conversation])
            ->resetTableFilters()->filterTable('bot_mode', 'paused')->assertCanSeeTableRecords([$this->conversation])->assertCanNotSeeTableRecords([$incoming]);
        Http::assertNothingSent();
    }

    private function user(array $permissions): User
    {
        $role = Role::create(['name' => 'inbox-'.Role::count()]);
        foreach ($permissions as $key) { $role->permissions()->attach(Permission::firstOrCreate(['key' => $key])); }
        return User::findOrFail(DB::table('users')->insertGetId(['role_id' => $role->id, 'email' => 'test-'.$role->id.'@example.invalid']));
    }
    private function order(): Orders { return Orders::forceCreate(['user_id' => $this->editor->id, 'status' => 'watching']); }
    private function message(string $id, array $attributes = []): SaMessage
    {
        return SaMessage::create($attributes + ['conversation_id' => $this->conversation->conversation_id, 'message_id' => $id, 'direction' => 'inbound', 'text' => 'Incoming text', 'status' => 'received']);
    }
}
