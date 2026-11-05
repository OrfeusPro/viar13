@php($groups = $this->altGroups())
@if (count($groups))
    <div style="margin-top:24px;">
        <x-filament::button type="button" color="gray" icon="heroicon-o-language" wire:click="openAltPanel">ALT/TITLE ({{ count($groups) }})</x-filament::button>
    </div>
    @if ($this->altPanelOpen)
        <section style="margin-top:24px; padding:20px; border:1px solid #d1d5db; border-radius:12px;" aria-label="ALT/TITLE изображений">
            <h2 style="font-weight:600; margin-bottom:16px;">ALT/TITLE изображений</h2>
            <div class="flex flex-wrap gap-2" style="margin-bottom:24px;">
                @foreach (app(\App\Filament\Bread\BreadRegistry::class)->locales() as $language)
                    <x-filament::button type="button" size="sm" :color="$this->altLocale === $language ? 'primary' : 'gray'" :aria-pressed="$this->altLocale === $language ? 'true' : 'false'" wire:click="changeAltLocale('{{ $language }}')" wire:loading.attr="disabled" wire:target="changeAltLocale">{{ strtoupper($language) }}</x-filament::button>
                @endforeach
            </div>
            @foreach ($groups as $group)
                @php($row = $group['rows']->get($this->altLocale))
                <div wire:key="alt-image-{{ $group['id'] }}-{{ $this->altLocale }}" style="margin-bottom:24px;">
                    <strong>{{ $group['field'] }}</strong>
                    <p style="overflow-wrap:anywhere; margin-bottom:12px;">{{ $group['path'] }}</p>
                    @if ($row)
                        <p>Статус: {{ $row->status }}</p>
                        <label style="display:block; margin:12px 0;">ALT ({{ strtoupper($this->altLocale) }})
                            <x-filament::input.wrapper><x-filament::input wire:model="altValues.{{ $row->id }}.alt" :disabled="! $this->altPermitted('edit')" /></x-filament::input.wrapper>
                        </label>
                        @error('altValues.' . $row->id . '.alt') <p class="text-danger-600">{{ $message }}</p> @enderror
                        <label style="display:block; margin:12px 0;">TITLE ({{ strtoupper($this->altLocale) }})
                            <x-filament::input.wrapper><x-filament::input wire:model="altValues.{{ $row->id }}.title" :disabled="! $this->altPermitted('edit')" /></x-filament::input.wrapper>
                        </label>
                        @error('altValues.' . $row->id . '.title') <p class="text-danger-600">{{ $message }}</p> @enderror
                    @else
                        <p>Для этого языка ALT/TITLE ещё не подготовлены.</p>
                    @endif
                    @if ($this->altPermitted('edit'))
                        <div class="flex flex-wrap gap-2" style="margin-top:16px;">
                            @if ($row)
                                <x-filament::button type="button" wire:click="saveAltPanel('language', {{ $group['id'] }})" wire:loading.attr="disabled" wire:target="saveAltPanel" wire:confirm="Сохранить и применить ALT/TITLE выбранного языка?">Сохранить язык</x-filament::button>
                            @endif
                            <x-filament::button type="button" color="gray" wire:click="saveAltPanel('image', {{ $group['id'] }})" wire:loading.attr="disabled" wire:target="saveAltPanel" wire:confirm="Сохранить и применить все языки этого изображения?">Все языки изображения</x-filament::button>
                        </div>
                    @endif
                </div>
            @endforeach
            @error('altValues') <p class="text-danger-600">{{ $message }}</p> @enderror
            <div class="flex flex-wrap gap-2" style="margin-top:24px;">
                <x-filament::button type="button" color="gray" wire:click="$set('altPanelOpen', false)">Закрыть ALT/TITLE</x-filament::button>
                @if ($this->altPermitted('edit'))
                    <x-filament::button type="button" wire:click="saveAltPanel('all')" wire:loading.attr="disabled" wire:target="saveAltPanel" wire:confirm="Сохранить и применить ALT/TITLE всех изображений и языков?">Сохранить и применить всё</x-filament::button>
                @endif
            </div>
        </section>
    @endif
@endif
