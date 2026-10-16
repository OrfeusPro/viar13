<div style="margin-bottom:16px;overflow-wrap:anywhere">
    @if($type === 'image')
        @php($url = \App\Filament\Bread\BreadImage::urls($value)[0] ?? null)
        @if($url)<a href="{{ $url }}" target="_blank" rel="noopener noreferrer"><img src="{{ $url }}" alt="Текущее изображение" style="display:block;max-width:100%;height:160px;object-fit:contain"></a>@endif
    @else
        @foreach(\App\Filament\Bread\BreadBrowseFiles::links($value) as $file)
            <a href="{{ $file['url'] }}" target="_blank" rel="noopener noreferrer" style="display:block;color:#2563eb">{{ $file['name'] ?: 'Скачать файл' }}</a>
        @endforeach
    @endif
</div>
