<strong data-custom-browse="{{ $row->field }}">{{ $content }}</strong>
<x-filament::badge>Custom component</x-filament::badge>
<small data-custom-current>{{ $data->getAttribute($row->field) }}</small>
<span data-custom-contract>{{ $action }}|{{ $view }}|{{ $dataType->name }}|{{ $row->details->view }}|{{ $options->nested->label }}|{{ $data->getKey() }}|{{ $dataTypeContent->currentPage() }}|{{ $dataTypeContent->count() }}</span>
