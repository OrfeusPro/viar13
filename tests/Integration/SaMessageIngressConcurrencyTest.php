<?php

namespace Tests\Integration;

use App\Models\Orders;
use App\Models\SaConversation;
use App\Services\Admin\OrderSaChatService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaMessageIngressConcurrencyTest extends TestCase
{
    private ?string $scratchDatabase = null;

    private $worker = null;

    private array $pipes = [];

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('RUN_SA_MYSQL_CONCURRENCY') !== '1') {
            $this->markTestSkipped('Opt-in: requires CREATE DATABASE permission on local MySQL/MariaDB.');
        }
        $connection = config('database.connections.mysql');
        $connection['url'] = null;
        $connection['database'] = null;
        config(['database.connections.ingress_test_admin' => $connection]);
        $this->scratchDatabase = 'admfil_sa_test_'.bin2hex(random_bytes(8));
        DB::connection('ingress_test_admin')->statement('CREATE DATABASE `'.$this->scratchDatabase.'`');
        $connection['database'] = $this->scratchDatabase;
        config(['database.default' => 'ingress_test', 'database.connections.ingress_test' => $connection,
            'services.sa_integration.api_key' => 'test-key', 'cache.default' => 'array', 'cache.limiter' => 'array']);
        require_once database_path('migrations/2026_02_24_200000_create_sa_integration_tables.php');
        require_once database_path('migrations/2026_04_02_220000_add_unread_for_manager_to_sa_conversations.php');
        (new \CreateSaIntegrationTables)->up();
        (new \AddUnreadForManagerToSaConversations)->up();
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('sa_conversation_id')->nullable();
            $table->timestamps();
        });
        Schema::create('order_user_comments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('comment');
            foreach (['is_admin', 'is_read', 'admin_is_read', 'is_img_sketch', 'is_img_painter', 'order_painter_image_id', 'order_user_image_id'] as $name) {
                $table->integer($name)->default(0);
            }
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        if (is_resource($this->worker)) {
            if (proc_get_status($this->worker)['running']) {
                proc_terminate($this->worker);
            }
            foreach ($this->pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            proc_close($this->worker);
        }
        if ($this->scratchDatabase !== null && preg_match('/^admfil_sa_test_[a-f0-9]{16}$/', $this->scratchDatabase)) {
            DB::purge('ingress_test');
            DB::connection('ingress_test_admin')->statement('DROP DATABASE `'.$this->scratchDatabase.'`');
        }
        parent::tearDown();
    }

    public function test_read_waits_for_ingress_commit_then_rejects_old_snapshot(): void
    {
        $order = Orders::query()->create();
        $conversation = SaConversation::query()->create(['conversation_id' => 'CONCURRENT-1',
            'orders_id' => $order->id, 'unread_for_manager' => true]);
        $token = app(OrderSaChatService::class)->readToken($order, $conversation->fresh());
        $intercepted = false;
        DB::listen(function (QueryExecuted $query) use (&$intercepted, $order, $token): void {
            if ($intercepted || ! str_starts_with($query->sql, 'update `sa_conversations`')) {
                return;
            }
            $intercepted = true;
            $this->assertGreaterThan(0, DB::transactionLevel());
            $this->worker = proc_open([PHP_BINARY, base_path('tests/Fixtures/sa-ingress-read-worker.php')],
                [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $this->pipes, base_path());
            $this->assertIsResource($this->worker);
            fwrite($this->pipes[0], json_encode(['connection' => config('database.connections.ingress_test'),
                'key' => config('app.key'), 'order' => $order->id, 'token' => $token], JSON_THROW_ON_ERROR));
            fclose($this->pipes[0]);
            stream_set_blocking($this->pipes[1], false);
            $this->assertSame("READY\n", $this->readUntil("READY\n"));
            usleep(200000);
            $this->assertTrue(proc_get_status($this->worker)['running'], 'Read must wait while ingress holds its lock.');
        });
        $this->postJson('/api/sa/webhooks/messages', [
            'event_id' => '9e5a5f6d-8bd2-403c-8247-900d773d15b6', 'event_type' => 'message.created',
            'idempotency_key' => 'concurrent-1', 'occurred_at' => now()->toIso8601String(), 'source' => 'SA',
            'data' => ['lead_id' => $order->id, 'conversation_id' => 'CONCURRENT-1', 'channel' => 'whatsapp',
                'message' => ['message_id' => 'CONCURRENT-MESSAGE', 'direction' => 'inbound', 'from' => ['type' => 'client'],
                    'to' => ['type' => 'agent'], 'text' => 'Never mark unseen message read', 'attachments' => [],
                    'sent_at' => now()->toIso8601String(), 'status' => 'received']],
        ], ['X-Api-Key' => 'test-key'])->assertOk();
        $this->assertTrue($intercepted);
        $this->assertSame("STALE\n", $this->readUntil("STALE\n"));
        $this->assertTrue($conversation->fresh()->unread_for_manager);
        $this->assertDatabaseCount('sa_messages', 1);
        $this->assertDatabaseCount('order_user_comments', 1);
        $this->assertDatabaseHas('sa_events', ['status' => 'processed']);
    }

    private function readUntil(string $suffix): string
    {
        $output = '';
        $deadline = microtime(true) + 15;
        do {
            // Read one line, not to EOF: Windows PHP pipes may remain blocking.
            $output .= fgets($this->pipes[1]) ?: '';
            if (str_ends_with($output, $suffix)) {
                return $output;
            }
            usleep(10000);
        } while (microtime(true) < $deadline && proc_get_status($this->worker)['running']);

        return $output;
    }
}
