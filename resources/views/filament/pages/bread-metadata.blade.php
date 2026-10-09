<x-filament-panels::page class="viar-management-page">
    @include('filament.components.management-styles')
    <x-filament::button tag="a" href="{{ \App\Filament\Pages\BreadTables::getUrl() }}" color="gray" icon="heroicon-o-arrow-left">Все таблицы BREAD</x-filament::button>
    <x-filament::section>
        @if($fullEditor)
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px" aria-label="Язык настроек BREAD">
                @foreach(app(\App\Filament\Bread\BreadRegistry::class)->locales() as $language)
                    <x-filament::button size="sm" color="{{ $metadataLocale === $language ? 'primary' : 'gray' }}" wire:click="switchMetadataLanguage('{{ $language }}')">{{ strtoupper($language) }}</x-filament::button>
                @endforeach
            </div>
        @endif
        <div class="viar-management-toolbar">
            <label for="bread-metadata-type" style="display:grid;gap:8px;max-width:100%;width:420px">Раздел
                <x-filament::input.wrapper><x-filament::input.select id="bread-metadata-type" wire:change="selectType($event.target.value)">
                    @foreach($this->types() as $type)<option value="{{ $type->id }}" @selected($typeId === $type->id)>{{ $type->display_name_plural ?: $type->slug }} — {{ $type->name }}</option>@endforeach
                </x-filament::input.select></x-filament::input.wrapper>
            </label>
            @if(! $fullEditor)<div style="display:flex;gap:12px;flex-wrap:wrap">
                <x-filament::button wire:click="openFullEditor" icon="heroicon-o-table-cells">Редактировать BREAD целиком</x-filament::button>
                <x-filament::button wire:click="openSettings" color="gray" icon="heroicon-o-cog-6-tooth">Настройки раздела</x-filament::button>
                <x-filament::button wire:click="openCreate" icon="heroicon-o-plus">Добавить поле</x-filament::button>
            </div>@endif
        </div>
        @if(! $fullEditor)
        <div style="margin-bottom:24px;max-width:560px">
            <label for="bread-create-table" style="display:block;margin-bottom:8px;font-weight:600">Создать BREAD для таблицы</label>
            <x-filament::input.wrapper><x-filament::input.select id="bread-create-table" wire:change="openSectionCreation($event.target.value)">
                <option value="" @selected(! $creatingSection)>Выберите таблицу без BREAD</option>
                @foreach($this->availableTables() as $table)<option value="{{ $table }}" @selected($creatingSection && $creationTable === $table)>{{ $table }}</option>@endforeach
            </x-filament::input.select></x-filament::input.wrapper>
        </div>
        @endif
        <p class="viar-management-muted" style="margin-bottom:24px">Настройки определяют отображение существующих полей. Для новой колонки сначала нужна миграция. Специальные формы и таблицы могут иметь собственное оформление.</p>
        @if($editing)
            @if($creatingSection)
                <h2 style="font-size:18px;font-weight:600;margin-bottom:16px">Создание BREAD</h2>
                <p class="viar-management-muted" style="margin-bottom:24px">Поля подготовлены по структуре существующей таблицы. Сохранение создаст настройки раздела, без изменения колонок или записей таблицы. Сортировку и поиск можно настроить после создания.</p>
            @endif
            @if($editingSettings)
                @php($settingsContext = $this->getSettingsContext())
                <h2 style="font-size:18px;font-weight:600;margin-bottom:16px">Настройки BREAD — {{ $settingsContext?->name }}</h2>
                <p class="viar-management-muted" style="margin-bottom:24px">Таблица: {{ $settingsContext?->name }}<br>Контроллер: {{ $settingsContext?->controller ?: 'Общий BREAD' }} · Политика: {{ $settingsContext?->policy_name ?: 'Права BREAD' }}. Совместимые варианты доступны в настройках ниже. Права ролей здесь не меняются.</p>
            @endif
            <form wire:submit="save" class="viar-management-form">
                {{ $this->form }}
                @if($fullEditor)
                    @include('filament.components.bread-field-editor')
                @endif
                @if($errors->any())<div role="alert" style="color:#dc2626">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
                <div class="viar-management-form-actions"><x-filament::button type="submit">Сохранить</x-filament::button><x-filament::button color="gray" wire:click="cancel" type="button">Отмена</x-filament::button></div>
            </form>
        @else
            <div class="viar-management-scroll" tabindex="0" role="region" aria-label="Поля BREAD">
                <table class="viar-management-table"><thead><tr><th>Порядок</th><th>Поле</th><th>Подпись</th><th>Тип</th><th>Обязательное</th><th>Список</th><th>Просмотр</th><th>Создание</th><th>Редактирование</th><th>Удаление</th><th>Действия</th></tr></thead><tbody>
                    @forelse($this->rows() as $row)
                        <tr wire:key="metadata-row-{{ $row->id }}"><td>{{ $row->order }}</td><td>{{ $row->field }}</td><td>{{ $row->display_name }}</td><td>{{ $row->type }}</td>
                            @foreach(\App\Services\Admin\BreadMetadataService::FLAGS as $flag)<td>{{ $row->{$flag} ? 'Да' : 'Нет' }}</td>@endforeach
                            <td><x-filament::icon-button icon="heroicon-o-pencil-square" label="Редактировать {{ $row->field }}" wire:click="openEdit({{ $row->id }})" /></td>
                        </tr>
                    @empty<tr><td colspan="11">Поля не настроены.</td></tr>@endforelse
                </tbody></table>
            </div>
            <div style="margin-top:24px" class="viar-management-muted">Колонки таблицы: {{ implode(', ', $this->columns()) ?: 'Таблица недоступна' }}</div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
