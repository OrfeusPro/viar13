<?php

namespace App\Services\Admin;

use App\Models\Orders;
use App\Models\VenipakData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderVenipakLabelService
{
    private const PICKUP_METHODS = [
        'pickup_at_viar_workshop', 'pickup_Riga', 'pickup_Daugavplis', 'pickup_Daugavpils',
    ];

    public function defaultData(Orders $order): array
    {
        $delivery = $this->decodeJson($order->delivery);
        $items = $this->decodeJson($order->items);
        $sender = VenipakData::query()->where('is_active', true)->first();
        $destination = in_array((string) ($delivery['sposob'] ?? ''), self::PICKUP_METHODS, true)
            ? 'pickup'
            : 'address';
        $sizes = collect($items)
            ->filter(fn ($item): bool => is_array($item))
            ->map(fn (array $item): mixed => $item['sizeId'] ?? $item['size_name'] ?? null)
            ->filter()
            ->implode(', ');

        return [
            'doc_no' => '',
            'destination' => $destination,
            'g_name' => trim((string) ($order->user?->last_name).' '.(string) ($order->user?->first_name)),
            'g_code' => '',
            'r_country' => (string) ($delivery['country'] ?? ''),
            'g_city' => (string) ($delivery['city'] ?? ''),
            'g_address' => (string) ($delivery['address'] ?? ''),
            'g_house' => '',
            'g_flat' => '',
            'g_post' => (string) ($delivery['postal_index'] ?? ''),
            'door_code' => '',
            'office_no' => '',
            'warehous_no' => '',
            'g_contact_p' => $order->id.' ('.$sizes.')',
            'g_contact_t' => (string) ($order->user?->phone ?: ($delivery['payer_phone'] ?? $delivery['phone'] ?? '')),
            'email_receiver' => (string) ($order->user?->email ?: ($delivery['email'] ?? '')),
            'g_city_pickup' => (string) ($delivery['city'] ?? ''),
            'g_address_pickup' => (string) ($delivery['address'] ?? ''),
            'g_post_pickup' => (string) ($delivery['postal_index'] ?? ''),
            'g_name_pickup' => '',
            'g_code_pickup' => '',
            's_name' => (string) ($sender?->s_name ?? ''),
            's_code' => (string) ($sender?->s_code ?? ''),
            's_country' => (string) ($sender?->s_country ?? 'LV'),
            's_city' => (string) ($sender?->s_city ?? ''),
            's_address' => (string) ($sender?->s_address ?? ''),
            's_post' => (string) ($sender?->s_post ?? ''),
            's_contact_p' => (string) ($sender?->s_contact_p ?? ''),
            's_contact_t' => (string) ($sender?->s_contact_t ?? ''),
            'email_sender' => (string) ($sender?->email_sender ?? ''),
            'delivery_type' => 'nwd',
            'delivery_express' => '0',
            'cod' => '',
            'cod_type' => 'EUR',
            'comment_call' => false,
            'four_hands' => false,
            'packages' => [['weight' => null, 'volume' => null, 'pallet' => '0']],
        ];
    }

    public function create(Orders $order, array $data): array
    {
        $delivery = $this->decodeJson($order->delivery);
        $isPickupOrder = in_array((string) ($delivery['sposob'] ?? ''), self::PICKUP_METHODS, true);

        if ($isPickupOrder && ($data['destination'] ?? null) !== 'pickup') {
            throw ValidationException::withMessages([
                'destination' => 'Заказ со способом доставки pickup нужно оформить в режиме «На отделение».',
            ]);
        }

        $data = validator($data, [
            'doc_no' => ['nullable', 'string', 'max:16'],
            'destination' => ['required', Rule::in(['address', 'pickup'])],
            'g_name' => ['required', 'string', 'max:60'],
            'g_code' => ['nullable', 'string', 'max:20'],
            'r_country' => ['required', 'string', 'size:2'],
            'g_city' => ['required_if:destination,address', 'nullable', 'string', 'max:40'],
            'g_address' => ['required_if:destination,address', 'nullable', 'string', 'max:100'],
            'g_house' => ['nullable', 'string', 'max:15'],
            'g_flat' => ['nullable', 'string', 'max:15'],
            'g_post' => ['required_if:destination,address', 'nullable', 'string', 'max:12'],
            'door_code' => ['nullable', 'string', 'max:10'],
            'office_no' => ['nullable', 'string', 'max:10'],
            'warehous_no' => ['nullable', 'string', 'max:10'],
            'g_contact_p' => ['required', 'string', 'max:80'],
            'g_contact_t' => ['required', 'string', 'max:30'],
            'email_receiver' => ['nullable', 'email', 'max:100'],
            'g_city_pickup' => ['required_if:destination,pickup', 'nullable', 'string', 'max:40'],
            'g_address_pickup' => ['required_if:destination,pickup', 'nullable', 'string', 'max:120'],
            'g_post_pickup' => ['required_if:destination,pickup', 'nullable', 'string', 'max:12'],
            'g_name_pickup' => ['required_if:destination,pickup', 'nullable', 'string', 'max:100'],
            'g_code_pickup' => ['required_if:destination,pickup', 'nullable', 'string', 'max:30'],
            's_name' => ['required', 'string', 'max:60'],
            's_code' => ['required', 'string', 'max:20'],
            's_country' => ['required', 'string', 'size:2'],
            's_city' => ['required', 'string', 'max:40'],
            's_address' => ['required', 'string', 'max:100'],
            's_post' => ['required', 'string', 'max:12'],
            's_contact_p' => ['required', 'string', 'max:60'],
            's_contact_t' => ['required', 'string', 'max:30'],
            'email_sender' => ['nullable', 'email', 'max:100'],
            'delivery_type' => ['required', Rule::in(['nwd', 'nwd10', 'nwd12', 'nwd8_14', 'nwd14_17', 'nwd18_22', 'sat'])],
            'delivery_express' => ['required', Rule::in(['0', '1', 0, 1])],
            'cod' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'cod_type' => ['required', Rule::in(['EUR', 'PLN', 'CZK'])],
            'comment_call' => ['nullable', 'boolean'],
            'four_hands' => ['nullable', 'boolean'],
            'packages' => ['required', 'array', 'min:1', 'max:20'],
            'packages.*.weight' => ['required', 'numeric', 'gt:0', 'max:9999'],
            'packages.*.volume' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'packages.*.pallet' => ['required', Rule::in(['0', '2', '6', '7', '3', '4'])],
        ])->validate();

        $credentials = $this->credentials();
        $payload = $this->normalizePayload($data);
        $xml = $this->buildCreateXml($order->id, $payload, $data['packages'], $credentials->login_id);

        Log::info('Admin Venipak label request prepared.', [
            'order_id' => $order->id,
            'destination' => $payload['destination'],
            'packages_count' => count($data['packages']),
        ]);

        try {
            $response = Http::timeout(20)->connectTimeout(5)->send('POST', $credentials->import_url, [
                'multipart' => $this->multipart($credentials, ['xml_text' => $xml]),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Admin Venipak label request failed.', [
                'order_id' => $order->id,
                'exception' => $exception,
            ]);
            throw ValidationException::withMessages(['venipak' => 'Не удалось подключиться к Venipak.']);
        }

        if (! $response->successful()) {
            Log::warning('Admin Venipak label request rejected.', [
                'order_id' => $order->id,
                'http_status' => $response->status(),
            ]);
            throw ValidationException::withMessages(['venipak' => 'Venipak отклонил запрос на создание этикетки.']);
        }

        $labels = $this->parseLabels($response->body());
        DB::transaction(function () use ($order, $labels): void {
            Orders::query()->lockForUpdate()->findOrFail($order->id)->forceFill([
                'labels' => implode(',', $labels),
            ])->save();
        });

        Log::info('Admin Venipak labels created.', [
            'order_id' => $order->id,
            'labels_count' => count($labels),
        ]);

        return $labels;
    }

    public function print(Orders $order, string $label): array
    {
        $labels = $this->labels($order);
        if (! in_array($label, $labels, true)) {
            throw ValidationException::withMessages(['label' => 'Этикетка не принадлежит заказу.']);
        }

        $credentials = $this->credentials();
        try {
            $response = Http::timeout(20)->connectTimeout(5)->send('POST', $credentials->print_url, [
                'multipart' => $this->multipart($credentials, ['pack_no' => $label]),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Admin Venipak label print failed.', [
                'order_id' => $order->id,
                'label' => $label,
                'exception' => $exception,
            ]);
            throw ValidationException::withMessages(['label' => 'Не удалось получить PDF-этикетку Venipak.']);
        }

        $content = $response->body();
        if (! $response->successful() || $content === '' || str_starts_with($content, 'Error')) {
            Log::warning('Admin Venipak label print rejected.', [
                'order_id' => $order->id,
                'label' => $label,
                'http_status' => $response->status(),
            ]);
            throw ValidationException::withMessages(['label' => 'Venipak не вернул PDF-этикетку.']);
        }

        return ['filename' => $label.'.pdf', 'content' => $content];
    }

    public function labels(Orders $order): array
    {
        return array_values(array_filter(array_map('trim', explode(',', trim((string) $order->labels, ',')))));
    }

    private function credentials(): VenipakData
    {
        $credentials = VenipakData::query()->where('is_active', true)->first();
        if (! $credentials || blank($credentials->user) || blank($credentials->pass) || blank($credentials->login_id)) {
            throw ValidationException::withMessages(['venipak' => 'Активные реквизиты Venipak не настроены.']);
        }

        return $credentials;
    }

    private function normalizePayload(array $data): array
    {
        if ($data['destination'] === 'pickup') {
            $data['g_name'] = $data['g_name_pickup'];
            $data['g_code'] = $data['g_code_pickup'];
        }
        $data['comment_call'] = ! empty($data['comment_call']) ? '1' : '';
        $data['four_hands'] = ! empty($data['four_hands']) ? '1' : '';

        return $data;
    }

    private function buildCreateXml(int $orderId, array $data, array $packages, string $loginId): string
    {
        $date = date('ymd');
        $fullAddress = $data['destination'] === 'pickup'
            ? $data['g_address_pickup']
            : trim($data['g_address'].', '.$data['g_house'].' - '.$data['g_flat'], ', -');
        $city = $data['destination'] === 'pickup' ? $data['g_city_pickup'] : $data['g_city'];
        $post = $data['destination'] === 'pickup' ? $data['g_post_pickup'] : $data['g_post'];
        $packXml = '';
        foreach (array_values($packages) as $index => $package) {
            $position = str_pad((string) ($orderId + $index + 2), 7, '7', STR_PAD_LEFT);
            $packXml .= '<pack><pack_no>V'.$this->xml($loginId).'E'.$position.'</pack_no><doc_no></doc_no>'
                .'<weight>'.$this->xml($package['weight']).'</weight><volume>'.$this->xml($package['volume'] ?? '').'</volume>'
                .'<pallets>'.$this->xml($package['pallet']).'</pallets></pack>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?><description type="1">'
            .'<manifest title="'.$this->xml($loginId.$date.'001').'" name="Отправка'.$date.'">'
            .'<sender><name>'.$this->xml($data['s_name']).'</name><company_code>'.$this->xml($data['s_code']).'</company_code>'
            .'<country>'.$this->xml($data['s_country']).'</country><city>'.$this->xml($data['s_city']).'</city>'
            .'<address>'.$this->xml($data['s_address']).'</address><post_code>'.$this->xml($data['s_post']).'</post_code>'
            .'<contact_person>'.$this->xml($data['s_contact_p']).'</contact_person><contact_tel>'.$this->xml($data['s_contact_t']).'</contact_tel>'
            .'<contact_email>'.$this->xml($data['email_sender']).'</contact_email></sender>'
            .'<doc_no>'.$this->xml($data['doc_no']).'</doc_no><shipment><consignee>'
            .'<name>'.$this->xml($data['g_name']).'</name><company_code>'.$this->xml($data['g_code']).'</company_code>'
            .'<country>'.$this->xml($data['r_country']).'</country><city>'.$this->xml($city).'</city>'
            .'<address>'.$this->xml($fullAddress).'</address><post_code>'.$this->xml($post).'</post_code>'
            .'<contact_person>'.$this->xml($data['g_contact_p']).'</contact_person><contact_tel>'.$this->xml($data['g_contact_t']).'</contact_tel>'
            .'<contact_email>'.$this->xml($data['email_receiver']).'</contact_email></consignee>'
            .'<comment_door_code>'.$this->xml($data['door_code'] ?? '').'</comment_door_code>'
            .'<comment_office_no>'.$this->xml($data['office_no'] ?? '').'</comment_office_no>'
            .'<comment_warehous_no>'.$this->xml($data['warehous_no'] ?? '').'</comment_warehous_no>'
            .'<attribute><shipment_code>'.$orderId.'</shipment_code><delivery_type>'.$this->xml($data['delivery_type']).'</delivery_type>'
            .'<delivery_mode>'.$this->xml($data['delivery_express']).'</delivery_mode><cod>'.$this->xml($data['cod']).'</cod>'
            .'<cod_type>'.$this->xml($data['cod_type']).'</cod_type><comment_call>'.$this->xml($data['comment_call']).'</comment_call>'
            .'<four_hands>'.$this->xml($data['four_hands']).'</four_hands></attribute>'.$packXml
            .'</shipment></manifest></description>';
    }

    private function parseLabels(string $body): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        if ($xml === false) {
            throw ValidationException::withMessages(['venipak' => 'Venipak вернул некорректный XML.']);
        }
        if (isset($xml->error)) {
            throw ValidationException::withMessages(['venipak' => trim((string) ($xml->error->text ?? $xml->error)) ?: 'Venipak отклонил этикетку.']);
        }

        $labels = [];
        foreach ($xml->text as $label) {
            if (trim((string) $label) !== '') {
                $labels[] = trim((string) $label);
            }
        }
        if ($labels === []) {
            throw ValidationException::withMessages(['venipak' => 'Venipak не вернул номер этикетки.']);
        }

        return $labels;
    }

    private function multipart(VenipakData $credentials, array $fields): array
    {
        $fields = ['user' => $credentials->user, 'pass' => $credentials->pass, 'login_id' => $credentials->login_id] + $fields;

        return collect($fields)->map(fn ($value, $name): array => [
            'name' => $name,
            'contents' => (string) $value,
        ])->values()->all();
    }

    private function xml(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
