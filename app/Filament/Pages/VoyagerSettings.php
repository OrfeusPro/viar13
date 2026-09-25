<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class VoyagerSettings extends Page
{
    protected static ?string $slug = 'voyager-settings';

    protected static ?string $navigationLabel = 'Настройки Voyager';

    protected string $view = 'filament.pages.voyager-settings';

    public ?int $settingId = null;

    public array $data = [];

    public static function canAccess(): bool
    {
        return static::permitted('browse');
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function hydrate(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function getTitle(): string | Htmlable
    {
        return 'Настройки Voyager';
    }

    public function groups(): array
    {
        return DB::table('settings')->orderBy('group')->orderBy('order')->get()
            ->groupBy('group')->all();
    }

    public function form(Schema $schema): Schema
    {
        $setting = $this->setting();
        if (! $setting) {
            return $schema->statePath('data')->components([]);
        }
        $component = match ($setting->type) {
            'image' => FileUpload::make('value')->image()->disk('public')->directory('settings')->maxSize(10240),
            'code_editor' => Textarea::make('value')->rows(16),
            'checkbox' => Toggle::make('value'),
            default => TextInput::make('value')->maxLength(5000),
        };

        return $schema->statePath('data')->components([
            $component->label($setting->display_name ?: $setting->key),
        ]);
    }

    public function openEdit(int $id): void
    {
        abort_unless(static::permitted('edit'), 403);
        $setting = DB::table('settings')->where('id', $id)->first();
        abort_unless($setting, 404);
        $this->settingId = $id;
        $this->form->fill(['value' => $setting->type === 'checkbox' ? (bool) $setting->value : $setting->value]);
    }

    public function save(): void
    {
        abort_unless(static::permitted('edit'), 403);
        $setting = $this->setting();
        abort_unless($setting, 404);
        $value = $this->form->getState()['value'] ?? null;
        DB::table('settings')->where('id', $setting->id)->update([
            'value' => $setting->type === 'checkbox' ? ($value ? '1' : '0') : $value,
        ]);
        $this->settingId = null;
        Notification::make()->title('Настройка сохранена')->success()->send();
    }

    public function cancel(): void
    {
        $this->settingId = null;
    }

    private function setting(): ?object
    {
        return $this->settingId ? DB::table('settings')->where('id', $this->settingId)->first() : null;
    }

    private static function permitted(string $action): bool
    {
        if (! DatabaseSchema::hasTable('settings')) {
            return false;
        }
        $user = auth('filament')->user();

        return $user && method_exists($user, 'hasPermission') && $user->hasPermission($action . '_settings');
    }
}
