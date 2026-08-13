@extends('voyager::master')

@section('page_title', 'SEO Meta')

@section('css')
    <style>
        .seo-meta-table td { vertical-align: top !important; }
        .seo-meta-text { max-width: 360px; white-space: normal; word-break: break-word; }
        .seo-meta-muted { color: #777; font-size: 12px; }
        .seo-meta-current { border-left: 3px solid #ddd; padding-left: 8px; margin-bottom: 8px; }
        .seo-meta-suggested { border-left: 3px solid #5bc0de; padding-left: 8px; margin-bottom: 8px; }
        .seo-meta-approved { border-left: 3px solid #5cb85c; padding-left: 8px; }
        .seo-meta-summary { margin: 0 0 15px 0; }
        .seo-meta-summary .label { display: inline-block; margin: 0 8px 6px 0; padding: 7px 10px; font-size: 12px; }
        .seo-meta-status-chips { margin-bottom: 15px; }
        .seo-meta-status-chips .btn { margin: 0 4px 6px 0; }
        .seo-meta-actions form { display: inline-block; margin: 0 0 4px 4px; }
        .seo-meta-form-row .form-group { margin-right: 10px; margin-bottom: 10px; }
        textarea.seo-meta-edit { min-height: 55px; resize: vertical; }
        textarea.seo-meta-keywords { min-height: 44px; resize: vertical; }
        .seo-meta-action-note { display: block; margin-top: 4px; }
    </style>
@endsection

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-search"></i> Генерация SEO Meta
        </h1>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')


        <div class="panel panel-bordered">
            <div class="panel-body">
                @php
                    $statusQuery = request()->except('status', 'page');
                    $allStatusCount = array_sum($statusCounts);
                    $statusLabels = [
                        'new' => 'Новая',
                        'generated' => 'Сгенерирована',
                        'pending' => 'Ожидает проверки',
                        'approved' => 'Одобрена',
                        'applied' => 'Применена',
                        'rejected' => 'Отклонена',
                        'failed' => 'Ошибка',
                    ];
                    $statusClasses = [
                        'new' => 'label-default',
                        'generated' => 'label-primary',
                        'pending' => 'label-warning',
                        'approved' => 'label-success',
                        'applied' => 'label-info',
                        'rejected' => 'label-danger',
                        'failed' => 'label-danger',
                    ];
                @endphp

                <div class="seo-meta-summary">
                    <span class="label label-primary">Записей: {{ $summaryCounts['total'] ?? 0 }}</span>
                    <span class="label label-warning">Без Meta Title: {{ $summaryCounts['missing_title'] ?? 0 }}</span>
                    <span class="label label-default">Без Meta Description: {{ $summaryCounts['missing_description'] ?? 0 }}</span>
                    <span class="label label-info">Без любого поля: {{ $summaryCounts['missing_any'] ?? 0 }}</span>
                    <span class="text-muted">счётчик по всем SEO meta-подсказкам</span>
                </div>

                <div class="seo-meta-status-chips">
                    <a href="{{ route('voyager.seo-meta-suggestions.index', $statusQuery) }}" class="btn btn-sm {{ ($filters['status'] ?? '') === '' ? 'btn-primary' : 'btn-default' }}">
                        Всего <span class="badge">{{ $allStatusCount }}</span>
                    </a>
                    @foreach($statuses as $status)
                        <a href="{{ route('voyager.seo-meta-suggestions.index', array_merge($statusQuery, ['status' => $status])) }}" class="btn btn-sm {{ ($filters['status'] ?? '') === $status ? 'btn-primary' : 'btn-default' }}">
                            {{ $statusLabels[$status] ?? $status }} <span class="badge">{{ $statusCounts[$status] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>

                <form method="get" class="form-inline seo-meta-form-row" action="{{ route('voyager.seo-meta-suggestions.index') }}">
                    <div class="form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">Все</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @if(($filters['status'] ?? '') === $status) selected @endif>{{ $statusLabels[$status] ?? $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="metaable_type">Модель</label>
                        <select id="metaable_type" name="metaable_type" class="form-control">
                            <option value="">Все</option>
                            @foreach($metaableTypes as $type => $label)
                                <option value="{{ $type }}" @if(($filters['metaable_type'] ?? '') === $type) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="locale">Язык</label>
                        <select id="locale" name="locale" class="form-control">
                            <option value="">Все</option>
                            @foreach($locales as $locale)
                                <option value="{{ $locale }}" @if(($filters['locale'] ?? '') === $locale) selected @endif>{{ $locale }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="missing">Пустые поля</label>
                        <select id="missing" name="missing" class="form-control">
                            <option value="" @if(($filters['missing'] ?? '') === '') selected @endif>Все</option>
                            <option value="title" @if(($filters['missing'] ?? '') === 'title') selected @endif>Без Meta Title</option>
                            <option value="description" @if(($filters['missing'] ?? '') === 'description') selected @endif>Без Meta Description</option>
                            <option value="any" @if(($filters['missing'] ?? '') === 'any') selected @endif>Без любого поля</option>
                            <option value="both" @if(($filters['missing'] ?? '') === 'both') selected @endif>Без обоих полей</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="has_error">Ошибки</label>
                        <select id="has_error" name="has_error" class="form-control">
                            <option value="" @if(($filters['has_error'] ?? '') === '') selected @endif>Все</option>
                            <option value="1" @if(($filters['has_error'] ?? '') === '1') selected @endif>С ошибками</option>
                            <option value="0" @if(($filters['has_error'] ?? '') === '0') selected @endif>Без ошибок</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="page_url">URL</label>
                        <input id="page_url" type="text" name="page_url" class="form-control" value="{{ $filters['page_url'] ?? '' }}" placeholder="содержит">
                    </div>

                    <div class="form-group">
                        <label for="search">Поиск</label>
                        <input id="search" type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}">
                    </div>

                    <button type="submit" class="btn btn-info"><i class="voyager-search"></i> Искать</button>
                    <a href="{{ route('voyager.seo-meta-suggestions.index') }}" class="btn btn-default">Сбросить</a>
                </form>
            </div>
        </div>

        <div class="panel panel-bordered">
            <div class="panel-heading">
                <h3 class="panel-title">Сканирование сущностей</h3>
            </div>
            <div class="panel-body">
                <form method="post" class="form-inline seo-meta-form-row" action="{{ route('voyager.seo-meta-suggestions.scan') }}">
                    @csrf
                    <div class="form-group">
                        <label for="scan-model">Модель</label>
                        <select id="scan-model" name="model" class="form-control">
                            <option value="">Все</option>
                            @foreach($metaableTypes as $type => $label)
                                <option value="{{ class_basename($type) }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="scan-locale">Язык</label>
                        <select id="scan-locale" name="locale" class="form-control">
                            <option value="">Все</option>
                            @foreach($locales as $locale)
                                <option value="{{ $locale }}">{{ $locale }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="scan-limit">Лимит</label>
                        <input id="scan-limit" type="number" name="limit" value="1000" min="1" class="form-control" style="width: 90px;">
                    </div>
                    <label class="checkbox-inline"><input type="checkbox" name="only_empty" value="1"> только пустые</label>
                    <label class="checkbox-inline"><input type="checkbox" name="force" value="1"> обновить существующие</label>
                    <button type="submit" class="btn btn-primary"><i class="voyager-refresh"></i> Сканировать</button>
                </form>
            </div>
        </div>

        <div class="panel panel-bordered">
            <div class="panel-body">
                <form id="bulk-form" method="post" action="{{ route('voyager.seo-meta-suggestions.bulk') }}" class="form-inline">
                    @csrf
                    <div class="form-group">
                        <select name="action" class="form-control">
                            <option value="generate">Сгенерировать выбранное</option>
                            <option value="approve">Одобрить выбранное</option>
                            <option value="apply">Применить выбранное</option>
                            <option value="reject">Отклонить выбранное</option>
                        </select>
                    </div>
                    <span id="bulk-selected-container"></span>
                    <button type="submit" class="btn btn-primary"><i class="voyager-check"></i> Применить</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover seo-meta-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="js-select-all"></th>
                            <th>Сущность</th>
                            <th>Страница</th>
                            <th>Текущие meta</th>
                            <th>Предложение</th>
                            <th>Статус</th>
                            <th class="actions text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($suggestions as $suggestion)
                        @php
                            $target = config('seo_meta_generation.targets.' . $suggestion->metaable_type, []);
                            $voyagerSlug = is_array($target) && isset($target['voyager_slug']) ? (string) $target['voyager_slug'] : null;
                            $editUrl = $voyagerSlug ? url('/admin/' . $voyagerSlug . '/' . $suggestion->metaable_id . '/edit') : null;
                            $approveFormId = 'approve-seo-meta-' . $suggestion->id;
                            $titleValue = $suggestion->approved_meta_title ?: ($suggestion->suggested_meta_title ?: '');
                            $descriptionValue = $suggestion->approved_meta_description ?: ($suggestion->suggested_meta_description ?: '');
                            $keywordsValue = $suggestion->seo_keywords ?: '';
                            $hasSuggestedMeta = trim((string) $suggestion->suggested_meta_title) !== '' || trim((string) $suggestion->suggested_meta_description) !== '';
                            $hasApprovedMeta = trim((string) $suggestion->approved_meta_title) !== '' || trim((string) $suggestion->approved_meta_description) !== '';
                            $canApprove = in_array($suggestion->status, ['pending', 'generated'], true) && $hasSuggestedMeta;
                            $canApply = $suggestion->status === 'approved' && $hasApprovedMeta;
                            $canReject = in_array($suggestion->status, ['pending', 'generated', 'approved', 'failed'], true);
                            $generateLabel = $hasSuggestedMeta || in_array($suggestion->status, ['approved', 'applied', 'rejected', 'failed'], true) ? 'Заново' : 'Сгенерировать';
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="js-bulk-row" value="{{ $suggestion->id }}"></td>
                            <td>
                                <strong>{{ $suggestion->entity_label ?: class_basename($suggestion->metaable_type) }}</strong>
                                <div class="seo-meta-muted">#{{ $suggestion->metaable_id }} · {{ $suggestion->locale }}</div>
                                @if($suggestion->entity_title)
                                    <div class="seo-meta-text">{{ $suggestion->entity_title }}</div>
                                @endif
                                @if($editUrl)
                                    <a href="{{ $editUrl }}" target="_blank">Открыть в Voyager</a>
                                @endif
                            </td>
                            <td class="seo-meta-text">
                                @if($suggestion->page_url)
                                    <a href="{{ $suggestion->page_url }}" target="_blank">{{ $suggestion->page_url }}</a>
                                @else
                                    <span class="text-muted">Без URL страницы</span>
                                @endif
                                <div class="seo-meta-muted">{{ $suggestion->title_field }} / {{ $suggestion->description_field }}</div>
                            </td>
                            <td class="seo-meta-text">
                                <div class="seo-meta-current">
                                    <strong>Meta Title</strong>
                                    <div>{{ $suggestion->current_meta_title ?: '—' }}</div>
                                    <span class="seo-meta-muted">{{ mb_strlen((string) $suggestion->current_meta_title, 'UTF-8') }} / {{ config('seo_meta_generation.limits.meta_title_max', 60) }}</span>
                                </div>
                                <div class="seo-meta-current">
                                    <strong>Meta Description</strong>
                                    <div>{{ $suggestion->current_meta_description ?: '—' }}</div>
                                    <span class="seo-meta-muted">{{ mb_strlen((string) $suggestion->current_meta_description, 'UTF-8') }} / {{ config('seo_meta_generation.limits.meta_description_max', 155) }}</span>
                                </div>
                            </td>
                            <td class="seo-meta-text">
                                <form id="{{ $approveFormId }}" method="post" action="{{ route('voyager.seo-meta-suggestions.approve', $suggestion->id) }}">
                                    @csrf
                                    <div class="seo-meta-suggested">
                                        <strong>Ключевые слова</strong>
                                        <textarea name="seo_keywords" class="form-control seo-meta-keywords" placeholder="Например: canvas print, custom portrait, gift">{{ $keywordsValue }}</textarea>
                                        <span class="seo-meta-muted">Будут учтены при следующей генерации.</span>
                                    </div>
                                    <div class="seo-meta-suggested">
                                        <strong>Meta Title</strong>
                                        <textarea name="meta_title" class="form-control seo-meta-edit">{{ $titleValue }}</textarea>
                                        <span class="seo-meta-muted">{{ mb_strlen((string) $titleValue, 'UTF-8') }} / {{ config('seo_meta_generation.limits.meta_title_max', 60) }}</span>
                                    </div>
                                    <div class="seo-meta-suggested">
                                        <strong>Meta Description</strong>
                                        <textarea name="meta_description" class="form-control seo-meta-edit">{{ $descriptionValue }}</textarea>
                                        <span class="seo-meta-muted">{{ mb_strlen((string) $descriptionValue, 'UTF-8') }} / {{ config('seo_meta_generation.limits.meta_description_max', 155) }}</span>
                                    </div>
                                </form>
                                @if($suggestion->error)
                                    <div class="alert alert-danger" style="margin-top: 8px; padding: 6px 8px;">{{ $suggestion->error }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="label {{ $statusClasses[$suggestion->status] ?? 'label-default' }}">{{ $statusLabels[$suggestion->status] ?? $suggestion->status }}</span>
                                @if($suggestion->generated_at)
                                    <div class="seo-meta-muted">{{ $suggestion->generated_at }}</div>
                                @endif
                            </td>
                            <td class="seo-meta-actions text-right">
                                <button type="submit"
                                        form="{{ $approveFormId }}"
                                        formaction="{{ route('voyager.seo-meta-suggestions.generate', $suggestion->id) }}"
                                        formmethod="post"
                                        class="btn btn-sm btn-info">
                                    <i class="voyager-refresh"></i> {{ $generateLabel }}
                                </button>

                                @if($canApprove)
                                    <button type="submit" form="{{ $approveFormId }}" class="btn btn-sm btn-success">
                                        <i class="voyager-check"></i> Одобрить
                                    </button>
                                @endif

                                @if($canApply)
                                    <button type="submit"
                                            form="{{ $approveFormId }}"
                                            formaction="{{ route('voyager.seo-meta-suggestions.apply', $suggestion->id) }}"
                                            formmethod="post"
                                            class="btn btn-sm btn-primary">
                                        <i class="voyager-upload"></i> Применить
                                    </button>
                                @endif

                                @if($canReject)
                                    <button type="submit"
                                            form="{{ $approveFormId }}"
                                            formaction="{{ route('voyager.seo-meta-suggestions.reject', $suggestion->id) }}"
                                            formmethod="post"
                                            class="btn btn-sm btn-danger">
                                        <i class="voyager-x"></i> Отклонить
                                    </button>
                                @endif

                                @if(!$canApprove && !$canApply && !$canReject)
                                    <span class="seo-meta-muted seo-meta-action-note">Доступна только генерация.</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">SEO meta-подсказки не найдены.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="panel-footer">
                {{ $suggestions->links() }}
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        (function () {
            var bulkForm = document.getElementById('bulk-form');
            var selectedContainer = document.getElementById('bulk-selected-container');
            var selectAll = document.querySelector('.js-select-all');

            function selectedRows() {
                return Array.prototype.slice.call(document.querySelectorAll('.js-bulk-row:checked'));
            }

            function rebuildBulkInputs() {
                if (!selectedContainer) {
                    return;
                }

                selectedContainer.innerHTML = '';

                selectedRows().forEach(function (checkbox) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = checkbox.value;
                    selectedContainer.appendChild(input);
                });
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    Array.prototype.slice.call(document.querySelectorAll('.js-bulk-row')).forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                    rebuildBulkInputs();
                });
            }

            Array.prototype.slice.call(document.querySelectorAll('.js-bulk-row')).forEach(function (checkbox) {
                checkbox.addEventListener('change', rebuildBulkInputs);
            });

            if (bulkForm) {
                bulkForm.addEventListener('submit', function (event) {
                    rebuildBulkInputs();

                    if (selectedRows().length === 0) {
                        event.preventDefault();
                        alert('Выберите хотя бы одну запись.');
                    }
                });
            }
        }());
    </script>
@endsection
