@extends('voyager::master')

@section('page_title', 'Alt-подсказки')

@section('css')
    <style>
        .alt-suggestions-table td {
            vertical-align: top !important;
        }

        .alt-suggestion-thumb {
            width: 96px;
            height: 72px;
            object-fit: cover;
            background: #f5f5f5;
            border: 1px solid #e4e4e4;
            border-radius: 3px;
        }

        .alt-suggestion-text {
            min-width: 240px;
        }

        .alt-suggestion-path {
            max-width: 220px;
            word-break: break-all;
            color: #777;
            font-size: 12px;
            margin-top: 6px;
        }

        .alt-suggestion-actions form {
            display: inline-block;
            margin: 0 0 5px 4px;
        }

        .alt-status-chips {
            margin-bottom: 15px;
        }

        .alt-status-chips .btn {
            margin: 0 4px 6px 0;
        }

        .alt-detail-row {
            display: none;
            background: #fafafa;
        }

        .alt-detail-drawer {
            padding: 18px;
            border-top: 1px solid #e9e9e9;
        }

        .alt-detail-thumb {
            max-width: 360px;
            max-height: 260px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .alt-detail-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 12px;
        }

        .alt-detail-box {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 3px;
            padding: 10px;
            min-height: 120px;
        }

        .alt-preview-code {
            display: block;
            white-space: normal;
            word-break: break-word;
            background: #fff;
            padding: 10px;
            border: 1px solid #e5e5e5;
            border-radius: 3px;
        }

        .alt-webp-summary {
            margin: 0 0 15px 0;
        }

        .alt-webp-summary .label {
            display: inline-block;
            margin: 0 8px 6px 0;
            padding: 7px 10px;
            font-size: 12px;
        }

        .alt-webp-cell {
            min-width: 130px;
        }
    </style>
@stop

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="{{ $dataType ? $dataType->icon : 'voyager-photo' }}"></i> Генерация Alt / Title
        </h1>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')

        <div class="panel panel-bordered">
            <div class="panel-body">
                @php
                    $statusQuery = request()->except('page', 'status');
                    $allStatusCount = array_sum($statusCounts);
                @endphp
                <div class="alt-webp-summary">
                    <span class="label label-primary">Изображений: {{ $webpStats['total'] ?? 0 }}</span>
                    <span class="label label-success">WebP есть: {{ $webpStats['with_webp'] ?? 0 }}</span>
                    <span class="label label-default">WebP нет: {{ $webpStats['without_webp'] ?? 0 }}</span>
                    <span class="label label-info">Покрытие: {{ $webpStats['coverage_percent'] ?? 0 }}%</span>
                    <span class="text-muted">счётчик по уникальным image_path с текущими фильтрами</span>
                </div>

                <div class="alt-status-chips">
                    <a
                        href="{{ route('voyager.alt-suggestions.index', $statusQuery) }}"
                        class="btn btn-sm {{ $filters['status'] === '' ? 'btn-primary' : 'btn-default' }}"
                    >
                        Всего <span class="badge">{{ $allStatusCount }}</span>
                    </a>
                    @foreach($statuses as $status)
                        <a
                            href="{{ route('voyager.alt-suggestions.index', array_merge($statusQuery, ['status' => $status])) }}"
                            class="btn btn-sm {{ $filters['status'] === $status ? 'btn-primary' : 'btn-default' }}"
                        >
                            {{ $status }} <span class="badge">{{ $statusCounts[$status] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>

                <form method="get" class="form-inline" action="{{ route('voyager.alt-suggestions.index') }}">
                    <div class="form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">Все</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @if($filters['status'] === $status) selected @endif>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="imageable_type">Модель</label>
                        <select id="imageable_type" name="imageable_type" class="form-control">
                            <option value="">Все</option>
                            @foreach($imageableTypes as $type)
                                <option value="{{ $type }}" @if($filters['imageable_type'] === $type) selected @endif>{{ class_basename($type) }}</option>
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
                        <label for="page_url">URL страницы</label>
                        <input id="page_url" type="text" name="page_url" class="form-control" value="{{ $filters['page_url'] }}" placeholder="содержит">
                    </div>

                    <div class="form-group">
                        <label for="has_error">Ошибки</label>
                        <select id="has_error" name="has_error" class="form-control">
                            <option value="" @if($filters['has_error'] === '') selected @endif>Все</option>
                            <option value="1" @if($filters['has_error'] === '1') selected @endif>С ошибками</option>
                            <option value="0" @if($filters['has_error'] === '0') selected @endif>Без ошибок</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="webp">WebP</label>
                        <select id="webp" name="webp" class="form-control">
                            <option value="" @if(($filters['webp'] ?? '') === '') selected @endif>Все</option>
                            <option value="1" @if(($filters['webp'] ?? '') === '1') selected @endif>Есть WebP</option>
                            <option value="0" @if(($filters['webp'] ?? '') === '0') selected @endif>Нет WebP</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="search">Поиск</label>
                        <input id="search" type="text" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="">
                    </div>

                    <button type="submit" class="btn btn-info">
                        <i class="voyager-search"></i> Искать
                    </button>
                    <a href="{{ route('voyager.alt-suggestions.index') }}" class="btn btn-default">Сбросить</a>
                </form>
            </div>
        </div>

        <div class="panel panel-bordered">
            <div class="panel-body">
                <form id="bulk-form" method="post" action="{{ route('voyager.alt-suggestions.bulk') }}" class="form-inline">
                    @csrf
                    <div class="form-group">
                        <select name="action" class="form-control">
                            <option value="approve">Одобрить выбранное</option>
                            <option value="reject">Отклонить выбранное</option>
                            <option value="regenerate">Сгенерировать выбранное заново</option>
                            <option value="apply">Применить выбранное одобренное</option>
                        </select>
                    </div>
                    <span id="bulk-selected-container"></span>
                    <button type="submit" class="btn btn-primary">
                        <i class="voyager-check"></i> Применить
                    </button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover alt-suggestions-table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="js-select-all">
                            </th>
                            <th>Изображение</th>
                            <th>WebP</th>
                            <th>Страница</th>
                            <th>Источник</th>
                            <th>Оригинал</th>
                            <th>Предложение</th>
                            <th>Статус</th>
                            <th>Сгенерировано</th>
                            <th class="actions text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suggestions as $suggestion)
                            @php
                                $statusClasses = [
                                    'new' => 'label-default',
                                    'generated' => 'label-primary',
                                    'pending' => 'label-warning',
                                    'approved' => 'label-success',
                                    'applied' => 'label-info',
                                    'rejected' => 'label-danger',
                                    'failed' => 'label-danger',
                                ];
                                $imagePath = (string) $suggestion->image_path;
                                $thumbnail = $imagePath;

                                if (
                                    $thumbnail !== ''
                                    && strpos($thumbnail, 'data:image/') !== 0
                                    && !preg_match('#^https?://#i', $thumbnail)
                                    && strpos($thumbnail, '/') !== 0
                                ) {
                                    $thumbnail = Storage::url(ltrim($thumbnail, '/'));
                                }

                                $approveFormId = 'approve-alt-suggestion-' . $suggestion->id;
                                $promptContext = (array) $suggestion->prompt_context;
                                $context = isset($promptContext['context']) && is_array($promptContext['context'])
                                    ? $promptContext['context']
                                    : $promptContext;
                                $blockLabel = $context['section_block'] ?? $context['purpose'] ?? $suggestion->field ?? 'Неизвестный блок';
                                $previewAlt = $suggestion->approved_alt ?: ($suggestion->suggested_alt ?: '');
                                $previewTitle = $suggestion->approved_title ?: ($suggestion->suggested_title ?: '');
                                $previewTag = '<img alt="' . htmlspecialchars($previewAlt, ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars($previewTitle, ENT_QUOTES, 'UTF-8') . '">';
                                $webpInfo = $webpByImagePath[$imagePath] ?? [
                                    'exists' => false,
                                    'path' => null,
                                    'url' => null,
                                    'original_is_webp' => false,
                                ];
                            @endphp
                            <tr data-suggestion-id="{{ $suggestion->id }}">
                                <td>
                                    <input type="checkbox" class="js-bulk-row" value="{{ $suggestion->id }}">
                                </td>
                                <td>
                                    @if($thumbnail !== '')
                                        <a href="{{ $thumbnail }}" target="_blank">
                                            <img src="{{ $thumbnail }}" class="alt-suggestion-thumb" alt="">
                                        </a>
                                    @else
                                        <span class="text-muted">Без изображения</span>
                                    @endif
                                    <div class="alt-suggestion-path">{{ $imagePath }}</div>
                                </td>
                                <td class="alt-webp-cell">
                                    @if(!empty($webpInfo['exists']))
                                        <span class="label label-success">Есть</span>
                                        @if(!empty($webpInfo['url']))
                                            <div class="alt-suggestion-path">
                                                <a href="{{ $webpInfo['url'] }}" target="_blank">Открыть WebP</a>
                                            </div>
                                        @endif
                                        @if(!empty($webpInfo['path']))
                                            <div class="alt-suggestion-path">{{ $webpInfo['path'] }}</div>
                                        @endif
                                    @else
                                        <span class="label label-default">Нет</span>
                                        @if(!empty($webpInfo['path']))
                                            <div class="alt-suggestion-path">ожидался: {{ $webpInfo['path'] }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($suggestion->page_url)
                                        <a href="{{ $suggestion->page_url }}" target="_blank">{{ $suggestion->page_url }}</a>
                                    @else
                                        <span class="text-muted">Без URL страницы</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $suggestion->imageable_type ? class_basename($suggestion->imageable_type) : '' }}</strong>
                                    @if($suggestion->imageable_id)
                                        <div>#{{ $suggestion->imageable_id }}</div>
                                    @endif
                                    @if($suggestion->field)
                                        <div class="text-muted">{{ $suggestion->field }}</div>
                                    @endif
                                    @if($suggestion->locale)
                                        <div class="text-muted">язык: {{ $suggestion->locale }}</div>
                                    @endif
                                </td>
                                <td class="alt-suggestion-text">
                                    <strong>Alt</strong>
                                    <p>{{ $suggestion->current_alt ?: '-' }}</p>
                                    <strong>Title</strong>
                                    <p>{{ $suggestion->current_title ?: '-' }}</p>
                                </td>
                                <td class="alt-suggestion-text">
                                    <div class="form-group">
                                        <label for="alt-{{ $suggestion->id }}">Alt</label>
                                        <textarea id="alt-{{ $suggestion->id }}" form="{{ $approveFormId }}" name="alt" class="form-control" rows="3">{{ old('alt', $suggestion->approved_alt ?: $suggestion->suggested_alt) }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="title-{{ $suggestion->id }}">Title</label>
                                        <input id="title-{{ $suggestion->id }}" form="{{ $approveFormId }}" type="text" name="title" class="form-control" value="{{ old('title', $suggestion->approved_title ?: $suggestion->suggested_title) }}">
                                    </div>
                                </td>
                                <td>
                                    <span class="label {{ $statusClasses[$suggestion->status] ?? 'label-default' }} js-status-badge">{{ $suggestion->status }}</span>
                                    @if($suggestion->error)
                                        <div class="text-danger alt-suggestion-path">{{ $suggestion->error }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($suggestion->generated_at)
                                        <div>{{ $suggestion->generated_at->format('Y-m-d H:i') }}</div>
                                    @else
                                        <span class="text-muted">Никогда</span>
                                    @endif
                                </td>
                                <td class="no-sort no-click bread-actions text-right alt-suggestion-actions">
                                    <button type="button" class="btn btn-sm btn-default js-toggle-detail">
                                        <i class="voyager-eye"></i> Детали
                                    </button>

                                    @if($suggestion->status !== 'applied')
                                    <form id="{{ $approveFormId }}" class="js-alt-action" method="post" action="{{ route('voyager.alt-suggestions.approve', $suggestion->id) }}" data-action="approve">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="voyager-check"></i> Подтвердить
                                        </button>
                                    </form>
                                    @endif

                                    <form method="post" action="{{ route('voyager.alt-suggestions.reject', $suggestion->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="voyager-x"></i> Отклонить
                                        </button>
                                    </form>

                                    <form class="js-alt-action" method="post" action="{{ route('voyager.alt-suggestions.regenerate', $suggestion->id) }}" data-action="regenerate">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="voyager-refresh"></i> Заново
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <tr class="alt-detail-row">
                                <td colspan="10">
                                    <div class="alt-detail-drawer">
                                        <div class="row">
                                            <div class="col-md-4">
                                                @if($thumbnail !== '')
                                                    <img src="{{ $thumbnail }}" class="alt-detail-thumb" alt="">
                                                @else
                                                    <div class="text-muted">Нет превью изображения</div>
                                                @endif
                                                <p class="alt-suggestion-path">{{ $imagePath }}</p>
                                                <h4>WebP</h4>
                                                @if(!empty($webpInfo['exists']))
                                                    <p>
                                                        <span class="label label-success">Есть</span>
                                                        @if(!empty($webpInfo['url']))
                                                            <a href="{{ $webpInfo['url'] }}" target="_blank">Открыть WebP</a>
                                                        @endif
                                                    </p>
                                                    @if(!empty($webpInfo['path']))
                                                        <p class="alt-suggestion-path">{{ $webpInfo['path'] }}</p>
                                                    @endif
                                                @else
                                                    <p><span class="label label-default">Нет</span></p>
                                                    @if(!empty($webpInfo['path']))
                                                        <p class="alt-suggestion-path">ожидался: {{ $webpInfo['path'] }}</p>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="col-md-8">
                                                <h4>Где используется</h4>
                                                <p>
                                                    @if($suggestion->page_url)
                                                        <a href="{{ $suggestion->page_url }}" target="_blank">{{ $suggestion->page_url }}</a>
                                                    @else
                                                        <span class="text-muted">URL страницы не найден</span>
                                                    @endif
                                                </p>
                                                <p><strong>Блок/позиция:</strong> {{ $blockLabel }}</p>
                                                <p><strong>Источник:</strong> {{ $suggestion->imageable_type ? class_basename($suggestion->imageable_type) : 'Неизвестно' }} #{{ $suggestion->imageable_id }} {{ $suggestion->field ? '(' . $suggestion->field . ')' : '' }} {{ $suggestion->locale ? '[' . $suggestion->locale . ']' : '' }}</p>

                                                <div class="alt-detail-grid">
                                                    <div class="alt-detail-box">
                                                        <h5>Оригинал</h5>
                                                        <strong>Alt</strong>
                                                        <p>{{ $suggestion->current_alt ?: 'None' }}</p>
                                                        <strong>Title</strong>
                                                        <p>{{ $suggestion->current_title ?: 'None' }}</p>
                                                    </div>
                                                    <div class="alt-detail-box">
                                                        <h5>Предложенное</h5>
                                                        <strong>Alt</strong>
                                                        <p>{{ $suggestion->suggested_alt ?: 'None' }}</p>
                                                        <strong>Title</strong>
                                                        <p>{{ $suggestion->suggested_title ?: 'None' }}</p>
                                                    </div>
                                                    <div class="alt-detail-box">
                                                        <h5>Одобренное</h5>
                                                        <strong>Alt</strong>
                                                        <p>{{ $suggestion->approved_alt ?: 'None' }}</p>
                                                        <strong>Title</strong>
                                                        <p>{{ $suggestion->approved_title ?: 'None' }}</p>
                                                    </div>
                                                </div>

                                                <h4>Превью после применения</h4>
                                                <code class="alt-preview-code">{{ $previewTag }}</code>

                                                <div class="alt-suggestion-actions" style="margin-top: 12px;">
                                                    @if($suggestion->status !== 'applied')
                                                    <button type="submit" form="{{ $approveFormId }}" class="btn btn-sm btn-success">
                                                        <i class="voyager-check"></i> Подтвердить
                                                    </button>
                                                    @endif
                                                    <form method="post" action="{{ route('voyager.alt-suggestions.reject', $suggestion->id) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="voyager-x"></i> Отклонить
                                                        </button>
                                                    </form>
                                                    <form class="js-alt-action" method="post" action="{{ route('voyager.alt-suggestions.regenerate', $suggestion->id) }}" data-action="regenerate">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning">
                                                            <i class="voyager-refresh"></i> Заново
                                                        </button>
                                                    </form>
                                                    @if((int) ($suggestion->apply_logs_count ?? 0) > 0)
                                                        <form method="post" action="{{ route('voyager.alt-suggestions.revert', $suggestion->id) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-default">
                                                                <i class="voyager-undo"></i> Откатить
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">Alt-подсказки не найдены.</td>
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
@stop

@section('javascript')
    <script>
        (function ($) {
            var statusClasses = {
                'new': 'label-default',
                'generated': 'label-primary',
                'pending': 'label-warning',
                'approved': 'label-success',
                'applied': 'label-info',
                'rejected': 'label-danger',
                'failed': 'label-danger'
            };

            function refreshStatus($row, status) {
                var $badge = $row.find('.js-status-badge');
                $badge
                    .removeClass('label-default label-primary label-warning label-success label-danger label-info')
                    .addClass(statusClasses[status] || 'label-default')
                    .text(status);
            }

            $('.js-toggle-detail').on('click', function () {
                $(this).closest('tr').next('.alt-detail-row').toggle();
            });

            $('.js-select-all').on('change', function () {
                $('.js-bulk-row').prop('checked', $(this).is(':checked'));
            });

            $('#bulk-form').on('submit', function (event) {
                var $container = $('#bulk-selected-container');
                var selected = $('.js-bulk-row:checked');

                $container.empty();

                if (selected.length === 0) {
                    event.preventDefault();
                    toastr.warning('Выберите хотя бы одну подсказку.');
                    return;
                }

                selected.each(function () {
                    $('<input>', {
                        type: 'hidden',
                        name: 'ids[]',
                        value: $(this).val()
                    }).appendTo($container);
                });

                $(this).find('button[type="submit"]')
                    .prop('disabled', true)
                    .html('<i class="voyager-refresh"></i> Обработка...');
            });

            $('.js-alt-action').on('submit', function (event) {
                var $form = $(this);
                var $row = $form.closest('tr');

                event.preventDefault();

                if ($row.hasClass('alt-detail-row')) {
                    $row = $row.prev('tr');
                }

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    success: function (response) {
                        if (response.suggestion && response.suggestion.status) {
                            refreshStatus($row, response.suggestion.status);
                        }

                        toastr.success(response.message || 'Обновлено.');
                    },
                    error: function (xhr) {
                        var response = xhr.responseJSON || {};
                        toastr.error(response.message || 'Не удалось обновить подсказку.');
                    }
                });
            });
        }(jQuery));
    </script>
@stop
