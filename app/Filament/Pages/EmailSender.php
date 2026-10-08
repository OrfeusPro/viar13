<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\Admin\EmailCampaignService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as DbSchema;
use Livewire\Attributes\Locked;

class EmailSender extends Page
{
    protected static ?string $slug = 'email-sender';
    protected static ?string $navigationLabel = 'Email Рассылка';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';
    protected string $view = 'filament.pages.email-sender';
    public ?array $data = [];
    #[Locked] public ?array $prepared = null;
    #[Locked] public ?array $result = null;

    public static function canAccess(): bool { return EmailCampaignService::permitted(auth('filament')->user()); }
    public function getTitle(): string { return 'Email Рассылка'; }
    public function ready(): bool { return DbSchema::hasTable('admin_email_campaigns'); }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $selected = [];
        if (request()->filled('id')) {
            $id = request()->integer('id');
            abort_unless($id > 0 && User::whereKey($id)->exists(), 404);
            $selected = [$id];
        }
        $this->form->fill(['users' => $selected, 'user_types' => [], 'locales' => [],
            'subject' => 'Скидки!', 'greetings' => 'Привет от viarcanvas', 'line' => '',
            'salutation' => 'Спасибо, что пользуетесь нашим ресурсом!']);
    }

    private function userLabel(User $user): string
    {
        return $user->email.' — '.trim($user->first_name.' '.$user->last_name)
            .($user->preferredLocale() ? ' · '.$user->preferredLocale() : '')
            .($user->news !== 'YES' ? ' · без подписки' : '');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->columns(2)->components([
            Select::make('users')->label('Пользователи')->multiple()->searchable()
                ->getSearchResultsUsing(function (string $search): array {
                    abort_unless(static::canAccess(), 403);
                    return User::where(fn ($query) => $query->where('email', 'like', '%'.$search.'%')
                        ->orWhere('first_name', 'like', '%'.$search.'%')->orWhere('last_name', 'like', '%'.$search.'%'))
                        ->orderBy('id')->limit(30)->get()->mapWithKeys(fn ($user) => [$user->id => $this->userLabel($user)])->all();
                })
                ->getOptionLabelsUsing(function (array $values): array {
                    abort_unless(static::canAccess(), 403);
                    return User::whereIn('id', $values)->get()->mapWithKeys(fn ($user) => [$user->id => $this->userLabel($user)])->all();
                })
                ->helperText('Пустой выбор — все пользователи. Письма получают только подписчики.'),
            Select::make('user_types')->label('Статус пользователей')->multiple()
                ->options(fn () => DB::table('user_types')->orderBy('id')->pluck('name', 'id')->all()),
            Select::make('locales')->label('Язык пользователей')->multiple()
                ->options(fn () => DB::table('locales')->orderBy('id')->pluck('prefix', 'prefix')->all())
                ->helperText('Фильтры применяются совместно. Текст письма не переводится автоматически.'),
            TextInput::make('subject')->label('Тема')->required()->maxLength(255),
            TextInput::make('greetings')->label('Приветствие')->required()->maxLength(255),
            TextInput::make('salutation')->label('Прощание')->required()->maxLength(255),
            Textarea::make('line')->label('Основной текст (HTML)')->required()->rows(12)->maxLength(10000)->columnSpanFull(),
        ]);
    }

    public function updatedData(): void { $this->prepared = null; $this->result = null; }

    public function prepare(): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless($this->ready(), 503);
        $this->resetErrorBag();
        $this->prepared = null; $this->result = null;
        try { $this->prepared = app(EmailCampaignService::class)->prepare(auth('filament')->user(), $this->form->getState()); }
        catch (\Illuminate\Validation\ValidationException $exception) {
            foreach ($exception->errors() as $key => $messages) { $this->addError('data.'.$key, implode(' ', $messages)); }
        }
    }

    public function confirm(): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless($this->prepared, 422);
        try { $this->result = app(EmailCampaignService::class)->send(auth('filament')->user(), $this->prepared['token']); }
        catch (\Illuminate\Validation\ValidationException $exception) {
            foreach ($exception->errors() as $key => $messages) { $this->addError('data.'.$key, implode(' ', $messages)); }
            return;
        }
        Notification::make()->title($this->result['status'] === 'suppressed' ? 'Тестовый режим: письма не отправлены' : 'Результат рассылки сохранён')
            ->color(in_array($this->result['status'], ['error', 'uncertain', 'processing']) ? 'warning' : 'success')->send();
    }
}
