<?php

namespace App\Services\Admin;

use App\Http\Controllers\Api\BlogIntegrationController;
use App\Http\Controllers\Api\SaIntegrationController;
use App\Http\Middleware\VerifyIntegrationApiKey;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class SaSimulatorService
{
    public static function permitted(?User $user): bool
    {
        return $user && collect(['browse_admin', 'browse_orders', 'read_orders', 'edit_orders', 'add_orders'])
            ->every(fn ($permission) => $user->hasPermission($permission));
    }

    public function execute(User $user, string $endpoint, string $payload): array
    {
        abort_unless(static::permitted($user), 403);
        validator(compact('endpoint', 'payload'), ['endpoint' => 'required|string|max:2000', 'payload' => 'required|string|max:2097152'])->validate();
        $parts = parse_url($endpoint);
        if (! $parts || isset($parts['host']) || isset($parts['scheme']) || isset($parts['fragment'])) {
            throw ValidationException::withMessages(['endpoint' => 'Используйте относительный адрес API из списка сценариев.']);
        }
        $path = $parts['path'] ?? '';
        $exact = [
            '/api/sa/leads' => ['POST', 'createLead'],
            '/api/sa/webhooks/messages' => ['POST', 'messagesWebhook'],
            '/api/sa/escalations' => ['POST', 'createEscalation'],
            '/api/crm/webhooks/send-message' => ['POST', 'sendMessageWebhook'],
            '/api/crm/webhooks/bot-control' => ['POST', 'botControlWebhook'],
            '/api/crm/webhooks/pipeline-changed' => ['POST', 'pipelineChangedWebhook'],
            '/api/sa/services-catalog' => ['GET', 'servicesCatalog'],
            '/api/sa/catalog-full' => ['GET', 'catalogFull'],
            '/api/sa/orders/lookup' => ['GET', 'lookupOrders'],
        ];
        $blog = [
            '/api/blog/categories' => ['GET', 'categories'],
            '/api/blog/authors' => [isset($parts['query']) ? 'GET' : 'POST', isset($parts['query']) ? 'authors' : 'storeAuthor'],
            '/api/blog/posts' => [isset($parts['query']) ? 'GET' : 'POST', isset($parts['query']) ? 'posts' : 'storePost'],
        ];
        $controller = SaIntegrationController::class;
        $args = [];
        $target = $exact[$path] ?? null;
        if (isset($blog[$path])) { $controller = BlogIntegrationController::class; $target = $blog[$path]; }
        if (preg_match('#^/api/sa/leads/(\d+)$#D', $path, $match)) {
            $target = ['PATCH', 'updateLead']; $args = [$match[1]];
        } elseif (preg_match('#^/api/sa/services/(HM-\d+|GC-5|FC-1)/(sizes|price-by-size)$#D', $path, $match)) {
            $target = ['GET', $match[2] === 'sizes' ? 'serviceSizes' : 'servicePriceBySize']; $args = [$match[1]];
        } elseif (preg_match('#^/api/blog/posts/(\d+)$#D', $path, $match)) {
            $controller = BlogIntegrationController::class; $target = ['PATCH', 'updatePost']; $args = [(int) $match[1]];
        }
        if (! $target) { throw ValidationException::withMessages(['endpoint' => $path === '/api/blog/media' ? 'Для загрузки медиа используйте multipart cURL из справки.' : 'Этот endpoint недоступен в симуляторе.']); }
        [$method, $handler] = $target;
        if ($controller === BlogIntegrationController::class && $method !== 'GET') {
            abort_unless($user->hasPermission(($method === 'PATCH' ? 'edit_' : 'add_').($handler === 'storeAuthor' ? 'blog_authors' : 'blog_posts')), 403);
        }
        if (in_array($handler, ['sendMessageWebhook', 'botControlWebhook'], true) && ! config('admin_migration.sa_commands_enabled', false)) {
            throw ValidationException::withMessages(['endpoint' => 'Внешние SA-команды отключены. Для тестового ответа используйте чат SA в режиме UAT.']);
        }
        try { $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw ValidationException::withMessages(['payload' => 'Некорректный JSON.']); }
        if (! is_array($data) || ! str_starts_with(ltrim($payload), '{')) {
            throw ValidationException::withMessages(['payload' => 'Тело запроса должно быть JSON-объектом.']);
        }
        if (in_array($handler, ['sendMessageWebhook', 'botControlWebhook'], true)
            && str_starts_with((string) data_get($data, 'data.conversation_id', ''), 'ADM-FIL-UAT-')) {
            throw ValidationException::withMessages(['payload' => 'Тестовые ADM-FIL-UAT диалоги не отправляются наружу. Используйте чат SA в режиме UAT.']);
        }
        parse_str($parts['query'] ?? '', $query);
        $request = Request::create($endpoint, $method, $method === 'GET' ? array_replace($query, $data) : [], [], [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json', 'HTTP_X_API_KEY' => (string) config('services.sa_integration.api_key')],
            $method === 'GET' ? null : $payload);
        $original = app('request');
        try {
            app()->instance('request', $request);
            $response = app(VerifyIntegrationApiKey::class)->handle($request,
                fn ($request) => app($controller)->{$handler}($request, ...$args));
            Log::info('SA simulator completed.', ['author_id' => $user->id, 'endpoint' => $path, 'method' => $method, 'http_code' => $response->getStatusCode()]);
            return ['status' => 'ok', 'http_code' => $response->getStatusCode(), 'api_response' => json_decode($response->getContent(), true)];
        } catch (ValidationException $exception) { throw $exception; }
        catch (Throwable $exception) {
            Log::error('SA simulator failed.', ['author_id' => $user->id, 'endpoint' => $path, 'exception_type' => $exception::class]);
            return ['status' => 'error', 'http_code' => 500, 'message' => 'Ошибка обработки. Проверьте журнал приложения.'];
        } finally { app()->instance('request', $original); }
    }
}
