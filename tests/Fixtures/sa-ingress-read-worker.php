<?php

use App\Models\Orders;
use App\Models\User;
use App\Services\Admin\OrderSaChatService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

// Private subprocess for the opt-in concurrency test. Credentials arrive only
// through stdin, never command-line arguments, files, or printed output.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
if (! preg_match('/^admfil_sa_test_[a-f0-9]{16}$/', $input['connection']['database'] ?? '')) {
    exit(2);
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'ingress_test', 'database.connections.ingress_test' => $input['connection'], 'app.key' => $input['key']]);
Gate::before(fn () => true);
$order = Orders::query()->findOrFail($input['order']);
$user = (new User)->forceFill(['id' => 1]);
echo "READY\n";
flush();
try {
    app(OrderSaChatService::class)->markAsRead($order, $user, $input['token']);
    echo "READ\n";
} catch (ValidationException $exception) {
    echo "STALE\n";
}
