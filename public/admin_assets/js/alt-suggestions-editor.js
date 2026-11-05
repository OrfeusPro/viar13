(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    var record = currentBreadRecord();

    if (!record || !$('.form-edit-add').length) {
        return;
    }

    var state = {
        rows: [],
        groups: [],
        locales: [],
        canEdit: false,
        filter: null,
        busy: false
    };

    $(function () {
        loadSuggestions();
    });

    function currentBreadRecord() {
        var parts = window.location.pathname.split('/').filter(function (part) {
            return part !== '';
        });

        if (parts.length < 4 || parts[parts.length - 1] !== 'edit') {
            return null;
        }

        var id = parseInt(parts[parts.length - 2], 10);

        if (!id) {
            return null;
        }

        return {
            slug: decodeURIComponent(parts[parts.length - 3]),
            id: id,
            baseUrl: '/' + parts.slice(0, parts.length - 3).join('/')
        };
    }

    function loadSuggestions() {
        $.ajax({
            url: record.baseUrl + '/alt-suggestions/entity',
            method: 'GET',
            dataType: 'json',
            data: {
                slug: record.slug,
                id: record.id
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function (response) {
            state.rows = response.suggestions || [];
            state.locales = response.locales || [];
            state.canEdit = response.can_edit === true;
            state.groups = groupSuggestions(state.rows);

            if (!state.groups.length) {
                return;
            }

            ensureModal();
            addGlobalButton();
            addFieldButtons();
            addMediaButtons();
        }).fail(function (xhr) {
            if (xhr.status !== 403 && xhr.status !== 404) {
                notify('error', responseMessage(xhr, 'Не удалось загрузить ALT Suggestions.'));
            }
        });
    }

    function groupSuggestions(rows) {
        var groups = {};
        var result = [];

        rows.forEach(function (row) {
            var key = row.field + '\n' + row.image_path;

            if (!groups[key]) {
                groups[key] = {
                    key: key,
                    field: row.field,
                    sourceField: row.source_field || row.field,
                    imagePath: row.image_path,
                    previewUrl: row.preview_url,
                    sourceType: row.source_type,
                    mediaId: row.media_id,
                    rows: [],
                    byLocale: {}
                };
                result.push(groups[key]);
            }

            groups[key].rows.push(row);
            groups[key].byLocale[row.locale || 'default'] = row;
        });

        return result;
    }

    function ensureModal() {
        if ($('#alt-suggestions-editor-modal').length) {
            return;
        }

        $('body').append(
            '<div class="modal fade" id="alt-suggestions-editor-modal" tabindex="-1" role="dialog">' +
                '<div class="modal-dialog modal-lg" role="document">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header">' +
                            '<button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>' +
                            '<h4 class="modal-title"><i class="voyager-images"></i> ALT/TITLE изображений</h4>' +
                        '</div>' +
                        '<div class="modal-body">' +
                            '<div class="alt-editor-summary"></div>' +
                            '<div class="alt-editor-items"></div>' +
                        '</div>' +
                        '<div class="modal-footer">' +
                            '<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>' +
                            '<button type="button" class="btn btn-primary js-alt-apply-visible"><i class="voyager-check"></i> Сохранить и применить всё</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );

        var $modal = $('#alt-suggestions-editor-modal');

        $modal.on('click', '.js-alt-locale', function () {
            var $card = $(this).closest('.alt-editor-item');
            activateLocale($card, String($(this).data('locale')));
        });

        $modal.on('input', '.js-alt-value', function () {
            var row = rowById(parseInt($(this).data('rowId'), 10));

            if (row) {
                row[$(this).data('attribute')] = $(this).val();
            }
        });

        $modal.on('click', '.js-alt-apply-locale', function () {
            var row = rowById(parseInt($(this).data('rowId'), 10));

            if (row) {
                saveAndApply([row]);
            }
        });

        $modal.on('click', '.js-alt-apply-image', function () {
            var group = groupByIndex(parseInt($(this).closest('.alt-editor-item').data('groupIndex'), 10));

            if (group) {
                saveAndApply(group.rows);
            }
        });

        $modal.on('click', '.js-alt-apply-visible', function () {
            saveAndApply(visibleGroups().reduce(function (rows, group) {
                return rows.concat(group.rows);
            }, []));
        });
    }

    function addGlobalButton() {
        if ($('.js-alt-editor-global').length) {
            return;
        }

        var $button = $(
            '<button type="button" class="btn btn-default js-alt-editor-global alt-editor-global-button">' +
                '<i class="voyager-images"></i> ALT/TITLE <span class="badge">' + state.groups.length + '</span>' +
            '</button>'
        );

        $button.on('click', function () {
            openEditor(null);
        });

        var $save = $('.form-edit-add .panel-footer .save').first();

        if ($save.length) {
            $button.insertBefore($save);
        }
    }

    function addFieldButtons() {
        var fields = {};

        state.groups.forEach(function (group) {
            fields[group.sourceField] = (fields[group.sourceField] || 0) + 1;
        });

        Object.keys(fields).forEach(function (field) {
            var $formGroup = formGroupForField(field);

            if (!$formGroup.length || $formGroup.find('.js-alt-editor-field').length) {
                return;
            }

            var $button = $(
                '<button type="button" class="btn btn-xs btn-default js-alt-editor-field alt-editor-field-button" title="ALT/TITLE для изображений поля">' +
                    '<i class="voyager-images"></i> ALT/TITLE <span class="badge">' + fields[field] + '</span>' +
                '</button>'
            );

            $button.on('click', function (event) {
                event.preventDefault();
                openEditor({ field: field });
            });

            var $label = $formGroup.children('.control-label').first();

            if ($label.length) {
                $button.insertAfter($label);
            } else {
                $formGroup.prepend($button);
            }
        });
    }

    function formGroupForField(field) {
        var $result = $();

        $('[data-field-name]').each(function () {
            if (!$result.length && String($(this).data('fieldName')) === field) {
                $result = $(this).closest('.form-group');
            }
        });

        if ($result.length) {
            return $result;
        }

        $('.form-group').each(function () {
            var $group = $(this);

            $group.find('[name]').each(function () {
                var name = String($(this).attr('name') || '').replace(/\[\]$/, '');

                if (!$result.length && name === field) {
                    $result = $group;
                }
            });
        });

        return $result;
    }

    function addMediaButtons() {
        state.groups.forEach(function (group, index) {
            if (!group.mediaId) {
                return;
            }

            $('.adv-media-files-item-holder').each(function () {
                var $holder = $(this);

                if (parseInt($holder.data('fileId'), 10) !== parseInt(group.mediaId, 10)) {
                    return;
                }

                var $actions = $holder.find('.adv-media-files-actions').first();

                if (!$actions.length || $actions.find('.js-alt-editor-media').length) {
                    return;
                }

                var $button = $('<span class="js-alt-editor-media icon voyager-info-circled" title="ALT/TITLE по языкам"></span>');
                $button.on('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    openEditor({ groupIndex: index });
                });
                $actions.prepend($button);
            });
        });
    }

    function openEditor(filter) {
        state.filter = filter;
        renderEditor();
        $('#alt-suggestions-editor-modal').modal('show');
    }

    function visibleGroups() {
        if (!state.filter) {
            return state.groups;
        }

        if (typeof state.filter.groupIndex !== 'undefined') {
            var group = groupByIndex(state.filter.groupIndex);
            return group ? [group] : [];
        }

        return state.groups.filter(function (group) {
            return group.sourceField === state.filter.field;
        });
    }

    function renderEditor() {
        var groups = visibleGroups();
        var suggestionCount = groups.reduce(function (count, group) {
            return count + group.rows.length;
        }, 0);
        var $modal = $('#alt-suggestions-editor-modal');

        $modal.find('.alt-editor-summary').text(
            'Изображений: ' + groups.length + '. Языковых значений: ' + suggestionCount + '.'
        );
        $modal.find('.js-alt-apply-visible').toggle(state.canEdit).prop('disabled', state.busy);

        var html = groups.map(function (group) {
            var groupIndex = state.groups.indexOf(group);
            var locales = orderedLocales(group);
            var tabs = locales.map(function (locale) {
                return '<button type="button" class="btn btn-xs btn-default js-alt-locale" data-locale="' + escapeHtml(locale) + '">' +
                    escapeHtml(locale.toUpperCase()) +
                '</button>';
            }).join('');

            return '<div class="alt-editor-item" data-group-index="' + groupIndex + '">' +
                '<div class="alt-editor-preview">' +
                    (group.previewUrl ? '<img src="' + escapeHtml(group.previewUrl) + '" alt="">' : '<i class="voyager-image"></i>') +
                '</div>' +
                '<div class="alt-editor-content">' +
                    '<div class="alt-editor-heading">' +
                        '<strong>' + escapeHtml(group.sourceField) + '</strong>' +
                        '<span class="text-muted">' + escapeHtml(group.sourceType) + '</span>' +
                    '</div>' +
                    '<div class="alt-editor-path" title="' + escapeHtml(group.imagePath) + '">' + escapeHtml(group.imagePath) + '</div>' +
                    '<div class="btn-group alt-editor-locales" role="group">' + tabs + '</div>' +
                    '<div class="alt-editor-fields"></div>' +
                    (state.canEdit ? '<div class="alt-editor-actions">' +
                        '<button type="button" class="btn btn-sm btn-primary js-alt-apply-locale"><i class="voyager-check"></i> Сохранить язык</button>' +
                        '<button type="button" class="btn btn-sm btn-default js-alt-apply-image"><i class="voyager-check"></i> Все языки изображения</button>' +
                    '</div>' : '') +
                '</div>' +
            '</div>';
        }).join('');

        $modal.find('.alt-editor-items').html(html || '<div class="alert alert-info">Для этого поля suggestions не найдены.</div>');

        $modal.find('.alt-editor-item').each(function () {
            var $card = $(this);
            var group = groupByIndex(parseInt($card.data('groupIndex'), 10));
            var locales = group ? orderedLocales(group) : [];
            activateLocale($card, preferredLocale(locales));
        });
    }

    function activateLocale($card, locale) {
        var group = groupByIndex(parseInt($card.data('groupIndex'), 10));
        var row = group ? group.byLocale[locale] : null;

        if (!row) {
            return;
        }

        $card.find('.js-alt-locale').removeClass('btn-primary active').addClass('btn-default');
        $card.find('.js-alt-locale').filter(function () {
            return String($(this).data('locale')) === locale;
        }).removeClass('btn-default').addClass('btn-primary active');

        $card.find('.alt-editor-fields').html(
            '<div class="alt-editor-status">Статус: <span class="label ' + statusClass(row.status) + '">' + escapeHtml(row.status) + '</span></div>' +
            '<div class="form-group">' +
                '<label>ALT (' + escapeHtml(locale.toUpperCase()) + ')</label>' +
                '<textarea class="form-control js-alt-value" rows="3" maxlength="125" data-row-id="' + row.id + '" data-attribute="alt"' + (state.canEdit ? '' : ' readonly') + '>' + escapeHtml(row.alt) + '</textarea>' +
            '</div>' +
            '<div class="form-group">' +
                '<label>TITLE (' + escapeHtml(locale.toUpperCase()) + ')</label>' +
                '<input class="form-control js-alt-value" type="text" maxlength="70" data-row-id="' + row.id + '" data-attribute="title" value="' + escapeHtml(row.title) + '"' + (state.canEdit ? '' : ' readonly') + '>' +
            '</div>'
        );
        $card.find('.js-alt-apply-locale').data('rowId', row.id);
    }

    function orderedLocales(group) {
        var available = Object.keys(group.byLocale);
        var ordered = state.locales.filter(function (locale) {
            return available.indexOf(locale) !== -1;
        });

        available.forEach(function (locale) {
            if (ordered.indexOf(locale) === -1) {
                ordered.push(locale);
            }
        });

        return ordered;
    }

    function preferredLocale(locales) {
        var documentLocale = String(document.documentElement.lang || '').toLowerCase().split('-')[0];

        if (locales.indexOf(documentLocale) !== -1) {
            return documentLocale;
        }

        return locales.indexOf('ru') !== -1 ? 'ru' : locales[0];
    }

    function saveAndApply(rows) {
        if (!state.canEdit || state.busy || !rows.length) {
            return;
        }

        var chunks = [];

        for (var index = 0; index < rows.length; index += 500) {
            chunks.push(rows.slice(index, index + 500));
        }

        state.busy = true;
        setBusy(true);
        applyChunk(chunks, 0, 0, function (error, applied) {
            state.busy = false;
            setBusy(false);

            if (error) {
                notify('error', error);
                return;
            }

            notify('success', 'Применено значений: ' + applied + '.');
            renderEditor();
        });
    }

    function applyChunk(chunks, index, applied, done) {
        if (index >= chunks.length) {
            done(null, applied);
            return;
        }

        $.ajax({
            url: record.baseUrl + '/alt-suggestions/entity/apply',
            method: 'POST',
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            data: JSON.stringify({
                slug: record.slug,
                id: record.id,
                suggestions: chunks[index].map(function (row) {
                    return {
                        id: row.id,
                        alt: row.alt,
                        title: row.title
                    };
                })
            }),
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).done(function (response) {
            var statuses = response.statuses || {};

            chunks[index].forEach(function (row) {
                if (typeof statuses[row.id] !== 'undefined') {
                    row.status = statuses[row.id];
                }
            });

            applyChunk(chunks, index + 1, applied + parseInt(response.applied || 0, 10), done);
        }).fail(function (xhr) {
            done(responseMessage(xhr, 'Не удалось применить ALT/TITLE.'), applied);
        });
    }

    function setBusy(busy) {
        var $modal = $('#alt-suggestions-editor-modal');
        $modal.find('.btn').prop('disabled', busy);
        $modal.toggleClass('is-busy', busy);
    }

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val() || '';
    }

    function rowById(id) {
        for (var index = 0; index < state.rows.length; index++) {
            if (parseInt(state.rows[index].id, 10) === id) {
                return state.rows[index];
            }
        }

        return null;
    }

    function groupByIndex(index) {
        return index >= 0 && index < state.groups.length ? state.groups[index] : null;
    }

    function statusClass(status) {
        var classes = {
            applied: 'label-info',
            approved: 'label-success',
            generated: 'label-primary',
            pending: 'label-warning',
            rejected: 'label-danger',
            failed: 'label-danger'
        };

        return classes[status] || 'label-default';
    }

    function responseMessage(xhr, fallback) {
        var response = xhr && xhr.responseJSON ? xhr.responseJSON : null;

        if (response && response.message) {
            return response.message;
        }

        if (response && response.errors) {
            var keys = Object.keys(response.errors);
            if (keys.length && response.errors[keys[0]].length) {
                return response.errors[keys[0]][0];
            }
        }

        return fallback;
    }

    function notify(type, message) {
        if (typeof window.toastr !== 'undefined' && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }

        window.alert(message);
    }

    function escapeHtml(value) {
        return $('<div>').text(value === null || typeof value === 'undefined' ? '' : String(value)).html();
    }
})(window.jQuery);
