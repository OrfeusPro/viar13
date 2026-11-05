<?php

namespace App\Services\Admin;

use App\Models\VenipakData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VenipakCourierService
{
    public const COUNTRIES = ['DK' => 'Denmark', 'EE' => 'Estonia', 'FI' => 'Finland', 'LV' => 'Latvia', 'LT' => 'Lithuania', 'PL' => 'Poland', 'SE' => 'Sweden'];

    public function defaults(): array
    {
        $sender = VenipakData::query()->where('is_active', true)->first();
        $data = [];
        foreach (['s_name', 's_code', 's_country', 's_address', 's_city', 's_post', 's_contact_p', 's_contact_t'] as $key) {
            $data[$key] = $sender?->{$key} ?? ($key === 's_country' ? 'LV' : '');
        }

        return $data + ['contact_mail' => $sender?->email_sender ?? '', 'svoris_s' => null, 'turis_s' => null,
            'pallets_s' => null, 'pastabos_s' => '', 'doknr_s' => '', 'metai_s' => null,
            'val_nuo_s' => '09', 'min_nuo_s' => '00', 'val_iki_s' => '17', 'min_iki_s' => '00'];
    }

    public function send(array $input): array
    {
        $rules = [];
        foreach (['s_name' => 60, 's_code' => 60, 's_address' => 50, 's_city' => 40, 's_post' => 8, 's_contact_p' => 50, 's_contact_t' => 30, 'pastabos_s' => 160, 'doknr_s' => 16] as $key => $length) {
            $rules[$key] = ['nullable', 'string', 'max:'.$length];
        }
        $data = validator($input, $rules + [
            's_country' => ['required', Rule::in(array_keys(self::COUNTRIES))], 'contact_mail' => ['nullable', 'email', 'max:255'],
            'svoris_s' => ['required', 'numeric', 'gt:0'], 'turis_s' => ['nullable', 'numeric', 'min:0'],
            'pallets_s' => ['nullable', 'integer', 'between:0,40'], 'metai_s' => ['required', 'date_format:Y-m-d'],
            'val_nuo_s' => ['required', Rule::in(self::hours(8, 22))], 'val_iki_s' => ['required', Rule::in(self::hours(10, 23))],
            'min_nuo_s' => ['required', Rule::in(['00', '15', '30', '45'])], 'min_iki_s' => ['required', Rule::in(['00', '15', '30', '45'])],
        ])->validate();
        if ($data['val_nuo_s'].$data['min_nuo_s'] >= $data['val_iki_s'].$data['min_iki_s']) {
            throw ValidationException::withMessages(['val_iki_s' => 'Конец интервала должен быть позже начала.']);
        }
        $credentials = VenipakData::query()->where('is_active', true)->first();
        if (! $credentials || blank($credentials->user) || blank($credentials->pass) || blank($credentials->login_id) || blank($credentials->import_url)) {
            throw ValidationException::withMessages(['venipak' => 'Активные реквизиты Venipak не настроены.']);
        }
        [$year, $month, $day] = explode('-', $data['metai_s']);
        $fields = ['name' => 's_name', 'company_code' => 's_code', 'country' => 's_country', 'city' => 's_city', 'address' => 's_address',
            'post_code' => 's_post', 'contact_person' => 's_contact_p', 'contact_tel' => 's_contact_t', 'contact_email' => 'contact_mail'];
        $xml = '<?xml version="1.0" encoding="UTF-8"?><description type="3"><sender>';
        foreach ($fields as $tag => $key) {
            $xml .= $this->element($tag, $data[$key] ?? '');
        }
        $xml .= '</sender>';
        foreach (['weight' => 'svoris_s', 'volume' => 'turis_s', 'pallets' => 'pallets_s'] as $tag => $key) {
            $xml .= $this->element($tag, $data[$key] ?? '');
        }
        $xml .= $this->element('date_y', $year).$this->element('date_m', $month).$this->element('date_d', $day);
        foreach (['hour_from' => 'val_nuo_s',
            'min_from' => 'min_nuo_s', 'hour_to' => 'val_iki_s', 'min_to' => 'min_iki_s', 'comment' => 'pastabos_s', 'doc_no' => 'doknr_s'] as $tag => $key) {
            $xml .= $this->element($tag, $data[$key] ?? '');
        }
        $xml .= '</description>';
        $multipart = collect(['user' => $credentials->user, 'pass' => $credentials->pass, 'login_id' => $credentials->login_id, 'xml_text' => $xml])
            ->map(fn ($value, $name): array => ['name' => $name, 'contents' => (string) $value])->values()->all();
        try {
            // A courier call has external effects. Never retry an uncertain response automatically.
            $response = Http::timeout(20)->connectTimeout(5)->send('POST', $credentials->import_url, ['multipart' => $multipart]);
        } catch (\Throwable $exception) {
            Log::warning('Admin Venipak courier connection failed.', ['exception_type' => $exception::class]);
            throw ValidationException::withMessages(['venipak' => 'Не удалось получить ответ Venipak. Перед повторной отправкой проверьте, создан ли вызов курьера.']);
        }
        if (! $response->successful()) {
            Log::warning('Admin Venipak courier HTTP error.', ['http_status' => $response->status()]);
            throw ValidationException::withMessages(['venipak' => 'Venipak вернул ошибку. Перед повторной отправкой проверьте статус вызова курьера.']);
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $result = simplexml_load_string($response->body(), \SimpleXMLElement::class, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        if ($result === false || (string) $result['type'] === 'error' || isset($result->error)) {
            Log::warning('Admin Venipak courier response rejected.');
            $message = $result !== false ? trim((string) ($result->error->text ?? $result->text ?? '')) : '';
            throw ValidationException::withMessages(['venipak' => $message ?: 'Venipak не подтвердил вызов. Проверьте статус перед повторной отправкой.']);
        }
        $numbers = [];
        foreach ($result->text as $text) {
            if (trim((string) $text) !== '') {
                $numbers[] = trim((string) $text);
            }
        }
        if ($numbers === []) {
            throw ValidationException::withMessages(['venipak' => 'Venipak не вернул подтверждение вызова. Проверьте статус перед повторной отправкой.']);
        }
        Log::info('Admin Venipak courier call accepted.', ['confirmations_count' => count($numbers)]);

        return $numbers;
    }

    public static function hours(int $from, int $to): array
    {
        return array_map(fn ($hour): string => sprintf('%02d', $hour), range($from, $to));
    }

    private function element(string $tag, mixed $value): string
    {
        return '<'.$tag.'>'.htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</'.$tag.'>';
    }
}
