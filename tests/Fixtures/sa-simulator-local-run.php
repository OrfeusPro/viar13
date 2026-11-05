<?php
// Local integration harness: never uses the application DB for writes.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Services\Admin\SaSimulatorService;

$sourceName = config('database.default');
$sourceConfig = config('database.connections.'.$sourceName);
if (($sourceConfig['driver'] ?? '') !== 'mysql' || !in_array($sourceConfig['host'] ?? '', ['localhost', '127.0.0.1', '::1'], true)) {
    throw new RuntimeException('Only local MySQL is allowed.');
}
$source = DB::connection($sourceName);
$originalSqlMode = $source->selectOne('SELECT @@SESSION.sql_mode AS mode')->mode;
$source->statement("SET SESSION sql_mode = ''");
$originalDatabase = $source->getDatabaseName();
$testDatabase = 'viar13_sa_test_'.date('Ymd_His').'_'.bin2hex(random_bytes(3));
$quote = fn ($name) => '`'.str_replace('`', '``', $name).'`';
$source->statement('CREATE DATABASE '.$quote($testDatabase).' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
register_shutdown_function(function () use ($source, $testDatabase, $originalDatabase, $quote, $originalSqlMode): void {
    if ($testDatabase === $originalDatabase || !preg_match('/^viar13_sa_test_\d{8}_\d{6}_[a-f0-9]{6}$/D', $testDatabase)) { return; }
    $source->statement('DROP DATABASE '.$quote($testDatabase));
    $source->statement('SET SESSION sql_mode = ?', [$originalSqlMode]);
});
$tables = $source->select('SHOW FULL TABLES WHERE Table_type = ?', ['BASE TABLE']);
$copied = [];
foreach ($tables as $row) {
    $table = array_values((array) $row)[0];
    $source->statement('CREATE TABLE '.$quote($testDatabase).'.'.$quote($table).' LIKE '.$quote($originalDatabase).'.'.$quote($table));
    // Only catalog/config/content reference rows. No existing clients, orders or chats.
    if (preg_match('/^(header_menu|translations|countries|country_|a_collage_head|canvas_|gallery_|modular_|family_constructor|gift_card$|gift_card_noms|newhome_|blog_|settings|roles|permissions|permission_role|user_types|forms|decor|compl|ram)/', $table)) {
        $source->statement('INSERT INTO '.$quote($testDatabase).'.'.$quote($table).' SELECT * FROM '.$quote($originalDatabase).'.'.$quote($table));
        $copied[] = $table;
    }
}
$testConfig = $sourceConfig;
$testConfig['database'] = $testDatabase;
unset($testConfig['url']);
config(['database.connections.sa_simulator_local' => $testConfig, 'database.default' => 'sa_simulator_local',
    'services.sa_integration.api_key' => 'local-fixture-key', 'services.synvolve.enabled' => false,
    'admin_migration.sa_commands_enabled' => true, 'cache.default' => 'array', 'queue.default' => 'sync', 'app.env' => 'testing']);
$app->detectEnvironment(fn () => 'testing');
if (DB::connection()->getDatabaseName() !== $testDatabase || $testDatabase === $originalDatabase) {
    throw new RuntimeException('Database isolation failed.');
}
Http::preventStrayRequests();
Mail::fake(); Queue::fake(); Storage::fake('public');
$synvolve = Mockery::mock(App\Services\SynvolveWebhookService::class)->shouldIgnoreMissing(true);
$app->instance(App\Services\SynvolveWebhookService::class, $synvolve);
// Block native remote streams, including the legacy attachment downloader.
class SaLocalImageStream {
    public $context;
    private string $data = '';
    private int $offset = 0;
    public function stream_open($path, $mode, $options, &$openedPath): bool {
        if ($path !== 'https://fixture.invalid/storage/temp_test_image.jpg') { return false; }
        $this->data = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6jAAAAABJRU5ErkJggg==');
        return true;
    }
    public function stream_read($count): string { $chunk = substr($this->data, $this->offset, $count); $this->offset += strlen($chunk); return $chunk; }
    public function stream_eof(): bool { return $this->offset >= strlen($this->data); }
    public function stream_stat(): array { return []; }
}
foreach (['http', 'https'] as $scheme) { stream_wrapper_unregister($scheme); stream_wrapper_register($scheme, SaLocalImageStream::class); }
$insert = function (string $table, array $data): int {
    foreach (DB::select('DESCRIBE `'.$table.'`') as $column) {
        if (array_key_exists($column->Field, $data) || $column->Null === 'YES' || $column->Default !== null || str_contains($column->Extra, 'auto_increment')) { continue; }
        $data[$column->Field] = preg_match('/int|decimal|float|double/', $column->Type) ? 0 : (preg_match('/date|time/', $column->Type) ? now()->format('Y-m-d H:i:s') : '');
    }
    return (int) DB::table($table)->insertGetId($data);
};
$userId = $insert('users', ['email' => 'sa-local@example.invalid', 'phone' => '+12025550123', 'first_name' => 'Local', 'last_name' => 'Fixture',
    'password' => bcrypt('fixture-only'), 'role_id' => 2, 'country' => 'LV', 'bonuses' => 100, 'settings' => '{"locale":"en"}', 'created_at' => now(), 'updated_at' => now()]);
$orderId = $insert('orders', ['user_id' => $userId, 'status' => 'watching', 'items' => '[]', 'price' => 20, 'country' => 'LV',
    'delivery' => '{"payer_phone":"+12025550123"}', 'sa_client_phone' => '+12025550123', 'sa_conversation_id' => 'LOCAL-SIM-CONV', 'sa_bot_mode' => 'paused', 'created_at' => now(), 'updated_at' => now()]);
$insert('sa_conversations', ['conversation_id' => 'LOCAL-SIM-CONV', 'orders_id' => $orderId, 'channel' => 'whatsapp', 'client_phone' => '+12025550123', 'client_name' => 'Local Fixture', 'bot_mode' => 'paused', 'unread_for_manager' => 1, 'created_at' => now(), 'updated_at' => now()]);
$insert('sa_messages', ['message_id' => 'LOCAL-SIM-MESSAGE', 'conversation_id' => 'LOCAL-SIM-CONV', 'orders_id' => $orderId, 'direction' => 'outbound', 'text' => 'Fixture', 'status' => 'sent', 'sent_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
$insert('coupons', ['text' => 'LOCAL-SIM-10', 'value' => 10, 'is_active' => 1, 'is_universal' => 1, 'is_multiuse' => 1, 'user_id' => 0, 'created_at' => now(), 'updated_at' => now()]);
$defaults = app(App\Http\Controllers\Admin\AdminSaIntegrationController::class)->resolveSimulatorDefaults();
$defaults = array_replace($defaults, ['lead_id' => (string) $orderId, 'conversation_id' => 'LOCAL-SIM-CONV', 'message_id' => 'LOCAL-SIM-MESSAGE',
    'client_phone' => '+12025550123', 'client_name' => 'Local Fixture', 'coupon_code' => 'LOCAL-SIM-10',
    'bonus_client_phone' => '+12025550123', 'bonus_client_name' => 'Local Fixture', 'bonus_client_email' => 'sa-local@example.invalid']);
$root = storage_path('app/testing/sa-simulator-local');
if (!is_dir($root)) { mkdir($root, 0777, true); }
file_put_contents($root.'/defaults.json', json_encode($defaults));
$json = shell_exec(escapeshellarg('C:/Program Files/nodejs/node.exe').' '.escapeshellarg(__DIR__.'/sa-simulator-presets.cjs').' '.escapeshellarg($root.'/defaults.json'));
$presets = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
$editor = Mockery::mock(App\Models\User::class)->makePartial(); $editor->id = $userId; $editor->shouldReceive('hasPermission')->andReturn(true);
$results = [];
foreach ($presets as $key => $preset) {
    $endpoint = $preset['endpoint'];
    if (! str_contains($endpoint, '?') && $endpoint !== '/api/blog/categories' && !preg_match('#/api/sa/services/.+/(sizes|price-by-size)#', $endpoint)) {
        try {
            $payload = json_encode($preset['payload'], JSON_THROW_ON_ERROR);
            $before = ['orders' => DB::table('orders')->count(), 'messages' => DB::table('sa_messages')->count(), 'events' => DB::table('sa_events')->count(), 'posts' => DB::table('blog_posts')->count()];
            if ($key === 'blog_media_upload') {
                $request = Illuminate\Http\Request::create('/api/blog/media', 'POST', ['slug' => 'local-simulator-image', 'alt' => 'Local image'], [],
                    ['file' => Illuminate\Http\UploadedFile::fake()->image('fixture.png', 4, 4)]);
                $response = app(App\Http\Controllers\Api\BlogIntegrationController::class)->storeMedia($request);
                $result = ['http_code' => $response->getStatusCode(), 'api_response' => $response->getData(true)];
                $duplicate = null;
            } else {
                $result = app(SaSimulatorService::class)->execute($editor, $endpoint, $payload);
                $firstCounts = ['orders' => DB::table('orders')->count(), 'messages' => DB::table('sa_messages')->count(), 'events' => DB::table('sa_events')->count(), 'posts' => DB::table('blog_posts')->count()];
                $duplicate = app(SaSimulatorService::class)->execute($editor, $endpoint, $payload);
            }
            $after = ['orders' => DB::table('orders')->count(), 'messages' => DB::table('sa_messages')->count(), 'events' => DB::table('sa_events')->count(), 'posts' => DB::table('blog_posts')->count()];
            $results[$key] = ['http_code' => $result['http_code'], 'status' => data_get($result, 'api_response.status'), 'before' => $before, 'after' => $after,
                'duplicate_status' => data_get($duplicate, 'api_response.status'), 'error' => data_get($result, 'api_response.error') ?? ($result['message'] ?? null),
                'api_response' => $result['api_response'] ?? null];
            PHPUnit\Framework\Assert::assertContains($result['http_code'], [200, 201], $key);
            PHPUnit\Framework\Assert::assertSame('ok', data_get($result, 'api_response.status'), $key);
            if ($duplicate !== null) {
                PHPUnit\Framework\Assert::assertSame($firstCounts, $after, $key.' duplicate changed record counts');
                PHPUnit\Framework\Assert::assertSame($key === 'blog_post_update' ? 'ok' : 'duplicate', data_get($duplicate, 'api_response.status'), $key);
            }
            if (str_starts_with($key, 'new_lead')) {
                $saved = DB::table('orders')->where('id', data_get($result, 'api_response.data.lead_id'))->first();
                PHPUnit\Framework\Assert::assertNotNull($saved, $key.' order missing');
                $results[$key]['saved_order'] = array_intersect_key((array) $saved, array_flip(['id','price','items','delivery','coupon','bonus','user_id']));
                PHPUnit\Framework\Assert::assertSame($before['orders'] + 1, $after['orders'], $key);
            }
            if ($key === 'inbound_media') {
                $attachments = json_decode(DB::table('sa_messages')->where('message_id', data_get($preset, 'payload.data.message.message_id'))->value('attachments_json'), true);
                PHPUnit\Framework\Assert::assertSame('stored', $attachments[0]['status'] ?? null);
                Storage::disk('public')->assertExists(substr($attachments[0]['local_path'], strlen('storage/')));
                $results[$key]['attachment'] = $attachments[0];
            }
            if ($key === 'message_status') { PHPUnit\Framework\Assert::assertSame('delivered', DB::table('sa_messages')->where('message_id', 'LOCAL-SIM-MESSAGE')->value('status')); }
            if ($key === 'update_stage') { PHPUnit\Framework\Assert::assertSame('pegging', DB::table('orders')->where('id', $orderId)->value('status')); }
            if ($key === 'bot_handoff' || $key === 'escalation') { PHPUnit\Framework\Assert::assertSame('handoff_to_manager', DB::table('orders')->where('id', $orderId)->value('sa_bot_mode')); }
            if ($key === 'pipeline_changed') { PHPUnit\Framework\Assert::assertSame('paused', DB::table('orders')->where('id', $orderId)->value('sa_bot_mode')); }
            if ($key === 'escalation') { PHPUnit\Framework\Assert::assertSame(1, DB::table('sa_escalations')->where('orders_id', $orderId)->where('reason_code', 'complex_case')->count()); }
            if ($key === 'new_lead_coupon') { PHPUnit\Framework\Assert::assertSame('28.00', data_get(json_decode($saved->items, true), 'sale_price')); }
            if ($key === 'new_lead_bonus') { PHPUnit\Framework\Assert::assertSame('0.00', data_get(json_decode($saved->items, true), 'sale_price')); }
            if ($key === 'blog_media_upload') { Storage::disk('public')->assertExists(data_get($result, 'api_response.data.media.path')); }
            if ($key === 'blog_author_create') { PHPUnit\Framework\Assert::assertNotNull(DB::table('blog_authors')->where('id', data_get($result, 'api_response.data.author.id'))->first()); }
            if ($key === 'blog_post_create') { PHPUnit\Framework\Assert::assertSame($before['posts'] + 1, $after['posts']); }
            if ($key === 'blog_post_update') {
                $postId = $defaults['blog_post_id'];
                PHPUnit\Framework\Assert::assertSame('blog/2026/05/article-main-updated.jpg', DB::table('blog_posts')->where('id', $postId)->value('image'));
                PHPUnit\Framework\Assert::assertSame(data_get($preset, 'payload.translations.ru.title'), DB::table('translations')->where('table_name', 'blog_posts')->where('column_name', 'title')->where('foreign_key', $postId)->where('locale', 'ru')->value('value'));
            }
            $results[$key]['persistence_checks'] = 'PASS';
        } catch (Throwable $e) { $results[$key] = ['exception' => $e::class, 'message' => $e->getMessage()]; }
        echo $key.' '.json_encode(array_intersect_key($results[$key], array_flip(['http_code', 'status', 'persistence_checks', 'duplicate_status', 'exception', 'message'])), JSON_UNESCAPED_UNICODE).PHP_EOL;
    }
}
$report = ['database' => $testDatabase, 'source_writes' => false, 'external_http' => 'blocked; Synvolve fake; stream fixture only', 'mail' => 'fake', 'queue' => 'fake', 'results' => $results];
$report['database_cleanup'] = 'Drop this process-owned schema on shutdown; never drop source.';
// Invalid requests must neither reserve idempotency keys nor mutate business rows.
$snapshot = fn () => collect(['orders', 'users', 'sa_events', 'sa_messages', 'sa_conversations', 'sa_escalations', 'blog_posts', 'blog_authors', 'translations'])
    ->mapWithKeys(fn ($table) => [$table => hash('sha256', json_encode(DB::table($table)->orderBy('id')->get()))])->all();
$checked = [];
foreach ($presets as $key => $preset) {
    $endpoint = $preset['endpoint'];
    if (!isset($results[$key]) || isset($checked[$endpoint]) || $key === 'blog_post_update') { continue; }
    $checked[$endpoint] = true;
    $beforeInvalid = $snapshot();
    if ($key === 'blog_media_upload') {
        $response = app(App\Http\Controllers\Api\BlogIntegrationController::class)->storeMedia(Illuminate\Http\Request::create($endpoint, 'POST'));
        $invalid = ['http_code' => $response->getStatusCode(), 'api_response' => $response->getData(true)];
    } else { $invalid = app(SaSimulatorService::class)->execute($editor, $endpoint, '{}'); }
    PHPUnit\Framework\Assert::assertContains($invalid['http_code'], [400, 422], $endpoint);
    PHPUnit\Framework\Assert::assertSame('error', data_get($invalid, 'api_response.status'), $endpoint);
    PHPUnit\Framework\Assert::assertSame($beforeInvalid, $snapshot(), $endpoint.' invalid request wrote data');
    $report['validation'][$endpoint] = ['http_code' => $invalid['http_code'], 'unchanged' => true];
}
foreach (['pause_bot' => 'paused', 'resume_bot' => 'active'] as $action => $mode) {
    $preset = $presets['bot_handoff'];
    $preset['payload']['event_id'] = (string) Illuminate\Support\Str::uuid();
    $preset['payload']['idempotency_key'] = 'local-'.$action;
    $preset['payload']['data']['action'] = $action;
    $result = app(SaSimulatorService::class)->execute($editor, $preset['endpoint'], json_encode($preset['payload']));
    PHPUnit\Framework\Assert::assertSame(200, $result['http_code']);
    PHPUnit\Framework\Assert::assertSame($mode, DB::table('orders')->where('id', $orderId)->value('sa_bot_mode'));
    PHPUnit\Framework\Assert::assertSame($mode, DB::table('sa_conversations')->where('conversation_id', 'LOCAL-SIM-CONV')->value('bot_mode'));
    $report['additional'][$action] = 'PASS';
}
$beforeInvalid = $snapshot();
$invalid = app(SaSimulatorService::class)->execute($editor, '/api/blog/posts/'.$defaults['blog_post_id'], '{"author_id":999999999}');
PHPUnit\Framework\Assert::assertContains($invalid['http_code'], [400, 422]);
PHPUnit\Framework\Assert::assertSame($beforeInvalid, $snapshot());
$report['validation']['blog_post_patch'] = ['http_code' => $invalid['http_code'], 'unchanged' => true];
// Probe the documented PATCH fields/tags/notes contract independently of its stage preset.
$beforeProbe = DB::table('orders')->where('id', $orderId)->first();
$probePayload = json_encode([
    'idempotency_key' => 'local-fields-probe', 'source' => 'SA', 'update' => [
        'fields' => ['email' => 'changed@example.invalid', 'city' => 'LOCAL-CITY', 'budget' => 1500], 'tags_add' => ['local-probe'],
        'notes_append' => [['text' => 'LOCAL-NOTE-PROBE', 'created_at' => now()->toIso8601String()]],
    ],
]);
$probe = app(SaSimulatorService::class)->execute($editor, '/api/sa/leads/'.$orderId, $probePayload);
$afterProbe = DB::table('orders')->where('id', $orderId)->first();
PHPUnit\Framework\Assert::assertSame(200, $probe['http_code']);
$deliveryProbe = json_decode($afterProbe->delivery, true);
PHPUnit\Framework\Assert::assertSame('changed@example.invalid', $deliveryProbe['email']);
PHPUnit\Framework\Assert::assertSame('LOCAL-CITY', $deliveryProbe['city']);
PHPUnit\Framework\Assert::assertSame(1500, data_get($deliveryProbe, 'sa_lead.fields.budget'));
PHPUnit\Framework\Assert::assertSame(['local-probe'], data_get($deliveryProbe, 'sa_lead.tags'));
PHPUnit\Framework\Assert::assertStringContainsString('LOCAL-NOTE-PROBE', $afterProbe->admin_comment);
$beforeDuplicate = $snapshot();
$duplicate = app(SaSimulatorService::class)->execute($editor, '/api/sa/leads/'.$orderId, $probePayload);
PHPUnit\Framework\Assert::assertSame('duplicate', data_get($duplicate, 'api_response.status'));
PHPUnit\Framework\Assert::assertSame($beforeDuplicate, $snapshot());
$lookup = app(SaSimulatorService::class)->execute($editor, '/api/sa/orders/lookup?order_id='.$orderId, '{}');
PHPUnit\Framework\Assert::assertSame('changed@example.invalid', data_get($lookup, 'api_response.data.orders.0.client.email'));
$report['additional']['lead_fields_tags_notes'] = ['http_code' => $probe['http_code'], 'reported' => $probe['api_response'],
    'business_columns_changed' => array_keys(array_diff_assoc((array) $afterProbe, (array) $beforeProbe)), 'persistence_duplicate_lookup' => 'PASS'];
$fail = true;
DB::listen(function (Illuminate\Database\Events\QueryExecuted $query) use (&$fail): void {
    if ($fail && str_starts_with($query->sql, 'update `orders`')) { $fail = false; throw new RuntimeException('Injected local MySQL write failure'); }
});
$beforeFailure = $snapshot();
$retryPayload = json_encode(['idempotency_key' => 'local-rollback-probe', 'source' => 'SA',
    'update' => ['fields' => ['city' => 'LOCAL-RETRY'], 'notes_append' => [['text' => 'LOCAL-RETRY-NOTE']]]]);
$failure = app(SaSimulatorService::class)->execute($editor, '/api/sa/leads/'.$orderId, $retryPayload);
PHPUnit\Framework\Assert::assertFalse($fail);
PHPUnit\Framework\Assert::assertSame(503, $failure['http_code']);
PHPUnit\Framework\Assert::assertSame($beforeFailure, $snapshot());
$retry = app(SaSimulatorService::class)->execute($editor, '/api/sa/leads/'.$orderId, $retryPayload);
PHPUnit\Framework\Assert::assertSame('ok', data_get($retry, 'api_response.status'));
$report['additional']['lead_update_mysql_rollback_retry'] = 'PASS';
file_put_contents($root.'/report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
Http::assertNothingSent();
$report['mail_intercepted'] = Mail::sent(App\Mail\SendUserYourOrderGiven::class)->count();
$report['presets_passed'] = count(array_filter($results, fn ($result) => ($result['persistence_checks'] ?? null) === 'PASS'));
file_put_contents($root.'/report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo 'Report: '.$root.'/report.json'.PHP_EOL;
exit($report['presets_passed'] === count($results) ? 0 : 1);
