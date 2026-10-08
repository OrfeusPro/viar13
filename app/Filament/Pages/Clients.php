<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class Clients extends Page
{
    use WithPagination;

    protected static ?string $slug = 'clients';
    protected static ?string $navigationLabel = 'Клиенты';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected string $view = 'filament.pages.clients';
    public string $search = '';
    public string $subscription = '';
    public string $country = '';
    public int $perPage = 25;
    public string $sort = 'id';
    public string $direction = 'desc';

    public static function canAccess(): bool
    {
        $user = auth('filament')->user();
        return $user && $user->hasPermission('browse_admin') && $user->hasPermission('browse_users');
    }

    public function getTitle(): string { return 'Клиенты'; }
    public function mount(): void { abort_unless(static::canAccess(), 403); }
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedSubscription(): void { $this->resetPage(); }
    public function updatedCountry(): void { $this->resetPage(); }
    public function updatedPerPage(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless(in_array($field, ['id', 'email', 'first_name', 'last_name', 'country'], true), 422);
        $this->direction = $this->sort === $field && $this->direction === 'asc' ? 'desc' : 'asc';
        $this->sort = $field;
        $this->resetPage();
    }

    public function clients(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        abort_unless(static::canAccess(), 403);
        $this->validate(['search' => 'string|max:200', 'subscription' => 'in:,YES,NO', 'country' => 'string|max:100',
            'perPage' => 'in:10,25,50,100', 'sort' => 'in:id,email,first_name,last_name,country', 'direction' => 'in:asc,desc']);
        return User::query()->where('role_id', 2)
            ->select(['users.id','email','first_name','last_name','phone','address','postal_index','country','news'])
            ->selectSub(DB::table('orders')->selectRaw('count(*)')->whereColumn('orders.user_id', 'users.id'), 'orders_count')
            ->when($this->subscription, fn ($query) => $query->where('news', $this->subscription))
            ->when($this->country, fn ($query) => $query->where('country', $this->country))
            ->when(trim($this->search) !== '', function ($query) {
                $search = trim($this->search);
                $query->where(function ($query) use ($search) {
                    foreach (['email','first_name','last_name','phone','address','postal_index','country'] as $field) {
                        $query->orWhere($field, 'like', '%'.$search.'%');
                    }
                    if (ctype_digit($search)) { $query->orWhere('users.id', (int) $search); }
                });
            })->orderBy($this->sort, $this->direction)->orderBy('users.id')->paginate($this->perPage);
    }

    public function countries(): array
    {
        abort_unless(static::canAccess(), 403);
        return User::where('role_id', 2)->whereNotNull('country')->where('country', '<>', '')
            ->distinct()->orderBy('country')->pluck('country')->all();
    }

    public function ordersUrl(int $userId): string
    {
        return '/filament/orders?'.http_build_query(['tab' => 'all', 'filters' => ['user_id' => ['value' => $userId]]]);
    }
}
