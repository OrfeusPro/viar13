@php
    $seoMetaTargets = config('seo_meta_generation.targets', []);
    $seoMetaTargetBySlug = [];

    foreach ($seoMetaTargets as $seoMetaModel => $seoMetaTarget) {
        if (is_array($seoMetaTarget) && isset($seoMetaTarget['voyager_slug'])) {
            $seoMetaTargetBySlug[$seoMetaTarget['voyager_slug']] = [
                'model' => $seoMetaModel,
                'target' => $seoMetaTarget,
            ];
        }
    }

    $seoMetaButtonConfig = $seoMetaTargetBySlug[$dataType->slug] ?? null;
    $seoMetaCanRender = $seoMetaButtonConfig && isset($dataTypeContent) && $dataTypeContent->getKey();
@endphp

@if ($seoMetaCanRender)
    <button type="button"
            class="btn btn-default seo-meta-generator-button"
            data-model="{{ $seoMetaButtonConfig['model'] }}"
            data-id="{{ $dataTypeContent->getKey() }}">
        <i class="voyager-magic"></i> Generate Meta Title + Meta Description
    </button>

    <script>
            (function ($) {
                function currentLocale() {
                    var active = $('.language-selector:first input[name="i18n_selector"]').filter(function () {
                        return $(this).parent().hasClass('active') || this.checked;
                    }).first();

                    return active.length ? active.attr('id') : null;
                }

                function parseTranslations($input) {
                    try {
                        return JSON.parse($input.val() || '{}') || {};
                    } catch (e) {
                        return {};
                    }
                }

                function fieldInput($hidden, field) {
                    var $input = $hidden.data('inpUsr');

                    if ($input && $input.length) {
                        return $input.first();
                    }

                    $input = $hidden.nextAll('.form-control').first();

                    if ($input.length) {
                        return $input;
                    }

                    return $('[name="' + field + '"]').first();
                }

                function writeVisibleField($input, value) {
                    if (!$input.length) {
                        return;
                    }

                    $input.val(value).trigger('change');

                    if ($input.hasClass('easymde') && $input.nextAll('.CodeMirror').length) {
                        $input.nextAll('.CodeMirror')[0].CodeMirror.getDoc().setValue(value);
                    }
                }

                function fillField(field, locale, value) {
                    if (!field || typeof value === 'undefined' || value === null) {
                        return;
                    }

                    var $hidden = $('#' + field + '_i18n');
                    var activeLocale = currentLocale();

                    if ($hidden.length) {
                        var translations = parseTranslations($hidden);
                        var $input = fieldInput($hidden, field);

                        translations[locale] = value;
                        $hidden.val(JSON.stringify(translations));
                        $hidden.data(locale, value);

                        if (activeLocale === locale) {
                            writeVisibleField($input, value);
                        }

                        return;
                    }

                    writeVisibleField($('[name="' + field + '"]').first(), value);
                }

                $(document).on('click', '.seo-meta-generator-button', function () {
                    var $button = $(this);
                    var originalText = $button.html();

                    $button.prop('disabled', true).html('<i class="voyager-refresh"></i> Generating...');

                    $.ajax({
                        method: 'POST',
                        url: '{{ route('voyager.seo-meta.generate') }}',
                        data: {
                            model: $button.data('model'),
                            id: $button.data('id'),
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (!response || !response.ok || !response.locales) {
                                if (window.toastr) {
                                    toastr.error('SEO meta generation failed.');
                                }
                                return;
                            }

                            $.each(response.locales, function (locale, values) {
                                fillField(response.title_field, locale, values.meta_title);
                                fillField(response.description_field, locale, values.meta_description);
                            });

                            if (window.toastr) {
                                toastr.success('SEO meta fields were generated and saved.');
                            }
                        },
                        error: function (xhr) {
                            var message = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : 'SEO meta generation failed.';

                            if (window.toastr) {
                                toastr.error(message);
                            }
                        },
                        complete: function () {
                            $button.prop('disabled', false).html(originalText);
                        }
                    });
                });
            })(jQuery);
    </script>
@endif
