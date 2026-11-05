<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\CountryTel;
use App\Models\User;
use App\Services\Admin\OrderCustomerSearch;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderSearchFilters
{
    public static function painterOptions(): array
    {
        return User::query()->where('role_id', 3)->orderBy('id')->get()
            ->mapWithKeys(fn (User $user): array => [$user->id => trim((filled($user->nick) ? trim($user->nick).' - ' : '').$user->email)])->all();
    }

    public static function countryOptions(): array
    {
        return CountryTel::query()->with('translations')->orderBy('sort')->get()
            ->mapWithKeys(fn (CountryTel $country): array => [$country->country_code => $country->getTranslatedAttribute('country_name', 'ru')])->all();
    }

    public static function createdAt(): Filter
    {
        return Filter::make('created_at')->label('Период')->columns(2)
            ->columnSpan(['default' => 1, 'sm' => 2])
            ->schema([
                DatePicker::make('from')->label('С'),
                DatePicker::make('until')->label('По'),
            ])
            ->indicateUsing(function (array $data): array {
                $indicators = [];
                foreach (['from' => 'Создан с', 'until' => 'Создан по'] as $field => $label) {
                    if (filled($data[$field] ?? null)) {
                        $indicators[$field] = $label.': '.Carbon::parse($data[$field])->format('d.m.Y');
                    }
                }

                return $indicators;
            })
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query
                    ->where('orders.created_at', '>=', Carbon::parse($date)->startOfDay()))
                ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query
                    ->where('orders.created_at', '<=', Carbon::parse($date)->startOfDay())));
    }

    public static function manager(): SelectFilter
    {
        return SelectFilter::make('manager_id')->label('Заказы менеджеров')
            ->options(fn (): array => User::query()->where('role_id', 4)->orderBy('email')->get()
                ->mapWithKeys(fn (User $user): array => [$user->id => trim(($user->nick ? $user->nick.' — ' : '').$user->email)])
                ->all() + ['__admin__' => 'Админ'])
            ->query(function (Builder $query, array $data): Builder {
                $value = $data['value'] ?? null;
                if (blank($value)) {
                    return $query;
                }
                if ($value !== '__admin__') {
                    return $query->where('orders.manager_id', $value);
                }

                $managers = User::query()->where('role_id', 4)->pluck('id')->all();

                // Preserve the original NOT IN semantics, including exclusion of NULL.
                return $managers
                    ? $query->whereNotIn('orders.manager_id', $managers)
                    : $query->where('orders.manager_id', 'all');
            })
            ->searchable()->preload();
    }

    public static function size(): Filter
    {
        return Filter::make('size')->label('Размер')
            ->schema([TextInput::make('value')->label('Размер')->placeholder('30x40')])
            ->indicateUsing(fn (array $data): array => ($data['value'] ?? '') !== '' && ($data['value'] ?? null) !== null
                ? [Indicator::make('Размер: '.$data['value'])->removeField('value')] : [])
            ->query(function (Builder $query, array $data): Builder {
                $size = (string) ($data['value'] ?? '');
                if ($size === '') {
                    return $query;
                }

                return $query->where(function (Builder $query) use ($size): void {
                    foreach (range(0, 9) as $index) {
                        $method = $index === 0 ? 'whereJsonContains' : 'orWhereJsonContains';
                        // Legacy payloads use numeric object keys; newer payloads can be JSON arrays.
                        $query->{$method}("items->{$index}->sizeId", $size)
                            ->orWhereJsonContains("items->[{$index}]->sizeId", $size);
                    }
                });
            });
    }

    public static function customer(): Filter
    {
        return Filter::make('customer')->label('Клиент')
            ->schema([TextInput::make('value')->label('Клиент')
                ->placeholder('ID, email, имя, телефон, адрес или индекс')])
            ->indicateUsing(fn (array $data): array => filled(trim((string) ($data['value'] ?? '')))
                ? [Indicator::make('Клиент: '.trim($data['value']))->removeField('value')] : [])
            ->query(fn (Builder $query, array $data): Builder => app(OrderCustomerSearch::class)
                ->apply($query, (string) ($data['value'] ?? '')));
    }

    public static function phone(): Filter
    {
        return Filter::make('phone')->label('Телефон')
            ->schema([TextInput::make('value')->label('Телефон')])
            ->indicateUsing(fn (array $data): array => filled(preg_replace('/\s+/u', '', (string) ($data['value'] ?? '')))
                ? [Indicator::make('Телефон: '.$data['value'])->removeField('value')] : [])
            ->query(function (Builder $query, array $data): Builder {
                $phone = (string) ($data['value'] ?? '');
                $trimmed = preg_replace('/\s+/u', '', $phone);
                if ($trimmed === '') {
                    return $query;
                }

                $users = DB::table('users')->select('id')->where(function ($query) use ($phone, $trimmed): void {
                    $query->where('phone', $phone)->orWhere('phone', $trimmed)
                        ->orWhere('phone', 'like', '%'.$phone.'%')->orWhere('phone', 'like', '%'.$trimmed.'%');
                });
                $deliveryUsers = DB::table('orders')->select('user_id')->where(function ($query) use ($phone, $trimmed): void {
                    $query->whereJsonContains('delivery->phone', $phone)->orWhereJsonContains('delivery->phone', $trimmed);
                    foreach ([2, 3, 4] as $prefixLength) {
                        $query->orWhereJsonContains('delivery->phone', substr($trimmed, $prefixLength));
                    }
                });

                // Legacy phone search selects clients first, then all their orders.
                return $query->where(fn (Builder $query): Builder => $query
                    ->whereIn('orders.user_id', $users)->orWhereIn('orders.user_id', $deliveryUsers));
            });
    }

    public static function price(): Filter
    {
        return Filter::make('price')->label('Точная сумма')
            ->schema([TextInput::make('value')->label('Цена')->numeric()])
            ->indicateUsing(fn (array $data): array => filled($data['value'] ?? null)
                ? [Indicator::make('Сумма: '.$data['value'])->removeField('value')] : [])
            ->query(fn (Builder $query, array $data): Builder => $query->when(
                filled($data['value'] ?? null),
                fn (Builder $query): Builder => $query->where('orders.price', $data['value']),
            ));
    }

    public static function paymentRequest(): Filter
    {
        return Filter::make('payment_request')->label('Номер запроса оплаты')
            ->schema([TextInput::make('value')->label('Заявка на оплату')->placeholder('OPR-000001')])
            ->indicateUsing(fn (array $data): array => filled(trim((string) ($data['value'] ?? '')))
                ? [Indicator::make('Заявка на оплату: '.strtoupper(trim($data['value'])))->removeField('value')] : [])
            ->query(fn (Builder $query, array $data): Builder => $query->when(
                filled(trim((string) ($data['value'] ?? ''))),
                fn (Builder $query): Builder => $query->whereHas('order_payment_requests',
                    fn (Builder $query): Builder => $query->where('public_number', 'like', '%'.strtoupper(trim($data['value'])).'%')),
            ));
    }
}
