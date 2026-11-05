<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Notifications\AdminMailNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class EmailCampaignService
{
    public static function permitted(?User $user): bool
    {
        return $user && collect(['browse_admin', 'browse_users', 'edit_users'])
            ->every(fn ($permission) => $user->hasPermission($permission));
    }

    public function validate(array $data): array
    {
        return validator($data, [
            'users' => ['present', 'array', 'max:1000'],
            'users.*' => ['integer', 'distinct', Rule::exists('users', 'id')],
            'user_types' => ['present', 'array'],
            'user_types.*' => ['integer', 'distinct', Rule::exists('user_types', 'id')],
            'locales' => ['present', 'array'],
            'locales.*' => ['string', 'distinct', Rule::exists('locales', 'prefix')],
            'subject' => ['required', 'string', 'max:255', 'not_regex:/[\r\n]/'],
            'greetings' => ['required', 'string', 'max:255'],
            'line' => ['required', 'string', 'max:10000'],
            'salutation' => ['required', 'string', 'max:255'],
        ])->validate();
    }

    private function query(array $data): Builder
    {
        return User::query()->where('news', 'YES')
            ->when($data['users'], fn ($query, $ids) => $query->whereIn('id', $ids))
            ->when($data['user_types'], fn ($query, $ids) => $query->whereIn('type_id', $ids))
            ->when($data['locales'], fn ($query, $locales) => $query->whereIn('settings->locale', $locales));
    }

    private function notification(array $data): AdminMailNotification
    {
        return new AdminMailNotification($data['subject'], $data['greetings'], $data['line'], $data['salutation']);
    }

    public function prepare(User $actor, array $input): array
    {
        abort_unless(static::permitted($actor), 403);
        $data = $this->validate($input);
        $recipients = [];
        $first = null;
        foreach ($this->query($data)->orderBy('id')->cursor() as $user) {
            if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) { continue; }
            $recipients[] = ['id' => $user->id, 'email' => $user->email];
            $first ??= $user;
            if (count($recipients) > 1000) {
                throw ValidationException::withMessages(['users' => 'Сузьте выборку до 1000 получателей за одну рассылку.']);
            }
        }
        if (!$first) { throw ValidationException::withMessages(['users' => 'Нет подписчиков с корректным email по выбранным фильтрам.']); }
        $html = (string) $this->notification($data)->toMail($first)->render();
        $token = (string) Str::uuid();
        DB::table('admin_email_campaigns')->insert(['token' => $token, 'user_id' => $actor->id,
            'payload' => json_encode($data, JSON_THROW_ON_ERROR), 'recipients' => json_encode($recipients, JSON_THROW_ON_ERROR),
            'status' => 'prepared', 'created_at' => now(), 'updated_at' => now()]);
        return ['token' => $token, 'count' => count($recipients), 'sample' => $first->email, 'html' => $html];
    }

    public function send(User $actor, string $token): array
    {
        abort_unless(static::permitted($actor), 403);
        $campaign = DB::transaction(function () use ($actor, $token) {
            $row = DB::table('admin_email_campaigns')->where('token', $token)->lockForUpdate()->first();
            abort_unless($row && (int) $row->user_id === (int) $actor->id, 404);
            if ($row->status !== 'prepared') { return $row; }
            if (now()->diffInMinutes($row->created_at, true) > 30) {
                throw ValidationException::withMessages(['users' => 'Предпросмотр устарел. Подготовьте рассылку снова.']);
            }
            if (config('admin_migration.bulk_email_enabled')
                && !in_array(config('queue.connections.'.config('queue.default').'.driver'), ['database', 'redis', 'sqs', 'beanstalkd'], true)) {
                throw ValidationException::withMessages(['users' => 'Для массовой отправки нужна асинхронная очередь.']);
            }
            // Commit before queue calls; interrupted/uncertain attempts must never replay.
            DB::table('admin_email_campaigns')->where('id', $row->id)->update(['status' => 'processing', 'updated_at' => now()]);
            $row->claimed = true;
            return $row;
        });
        if (empty($campaign->claimed)) {
            return json_decode($campaign->result ?: '{"status":"processing"}', true) + ['duplicate' => true];
        }
        $data = json_decode($campaign->payload, true, 512, JSON_THROW_ON_ERROR);
        $recipients = json_decode($campaign->recipients, true, 512, JSON_THROW_ON_ERROR);
        $result = ['status' => 'queued', 'queued' => 0, 'skipped' => 0, 'suppressed' => 0, 'uncertain' => 0, 'not_attempted' => 0];
        try {
            if (!config('admin_migration.bulk_email_enabled')) {
                $result['status'] = 'suppressed'; $result['suppressed'] = count($recipients);
            } else {
                $targets = [];
                foreach ($recipients as $recipient) {
                    $user = User::find($recipient['id']);
                    if (!$user || $user->news !== 'YES' || $user->email !== $recipient['email']
                        || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) { $result['skipped']++; continue; }
                    $this->notification($data)->toMail($user);
                    $targets[] = $user;
                }
                foreach ($targets as $index => $user) {
                    try {
                        $notification = $this->notification($data);
                        $notification->campaignRecipientEmail = $user->email;
                        $user->notify($notification);
                        $result['queued']++;
                    }
                    catch (Throwable $exception) {
                        $result['status'] = 'uncertain'; $result['uncertain'] = 1;
                        $result['not_attempted'] = count($targets) - $index - 1;
                        Log::warning('Admin campaign queue outcome uncertain.', ['campaign_id' => $campaign->id, 'user_id' => $user->id]);
                        break;
                    }
                }
            }
        } catch (Throwable $exception) {
            $result['status'] = 'error'; $result['not_attempted'] = count($recipients) - $result['skipped'];
            Log::warning('Admin campaign preparation failed.', ['campaign_id' => $campaign->id]);
        }
        DB::table('admin_email_campaigns')->where('id', $campaign->id)->update([
            'status' => $result['status'], 'result' => json_encode($result), 'updated_at' => now()]);
        Log::info('Admin campaign processed.', ['campaign_id' => $campaign->id, 'actor_id' => $actor->id] + $result);
        return $result;
    }
}
