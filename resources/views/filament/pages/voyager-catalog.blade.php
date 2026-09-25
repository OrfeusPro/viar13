<x-filament-panels::page>
    <x-filament::section>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->sections() as $section)
                <div class="rounded-lg border p-3">
                    @if ($section['url'])
                        <a class="font-semibold text-primary-600" href="{{ $section['url'] }}">{{ $section['name'] }}</a>
                    @else
                        <span class="font-semibold">{{ $section['name'] }}</span>
                        <p class="text-sm text-gray-500">Модель или таблица требует исправления</p>
                    @endif
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
