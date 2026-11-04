<div class="filter-picture tabs-item tabs-item1 active">
    <div class="canvas-form-step2-slot"></div>
    <div class="filter-accordion">
        @include(env('THEME_RESOURCES') . 'pages.canvas.form.step1')
        @include(env('THEME_RESOURCES') . 'pages.canvas.form.step2')

        @include(env('THEME_RESOURCES') . 'pages.canvas.form.step3')

        @include(env('THEME_RESOURCES') . 'pages.canvas.form.step4')

{{--        @include(env('THEME_RESOURCES') . 'pages.canvas.form.step5')--}}

        @include(env('THEME_RESOURCES') . 'pages.canvas.form.step4_show')

        @include(env('THEME_RESOURCES') . 'pages.canvas.form.canvas')

        @include(env('THEME_RESOURCES') . 'pages.canvas.form.step5_show')

    </div>
</div>
<script>
    (function () {
        var mobileQuery = window.matchMedia ? window.matchMedia('(max-width: 700px)') : null;
        var restoreBefore = null;

        function syncCanvasFormStep2() {
            var slot = document.querySelector('.tabs-item1 .canvas-form-step2-slot');
            var accordion = document.querySelector('.tabs-item1 .filter-accordion');
            var title = document.querySelector('.tabs-item1 .canvas-form-step2-title');
            var content = document.querySelector('.tabs-item1 .canvas-form-step2-content');

            if (!slot || !accordion || !title || !content) {
                return;
            }

            if (!restoreBefore && content.parentNode === accordion) {
                restoreBefore = content.nextElementSibling;
            }

            title.classList.add('open');
            content.style.display = 'block';

            if (mobileQuery ? mobileQuery.matches : window.innerWidth <= 700) {
                if (!slot.contains(title)) {
                    slot.appendChild(title);
                    slot.appendChild(content);
                }

                return;
            }

            if (slot.contains(title) || slot.contains(content)) {
                var before = restoreBefore && restoreBefore.parentNode === accordion ? restoreBefore : null;

                accordion.insertBefore(title, before);
                accordion.insertBefore(content, before);
            }
        }

        syncCanvasFormStep2();
        window.addEventListener('load', syncCanvasFormStep2);

        if (mobileQuery) {
            if (mobileQuery.addEventListener) {
                mobileQuery.addEventListener('change', syncCanvasFormStep2);
            } else if (mobileQuery.addListener) {
                mobileQuery.addListener(syncCanvasFormStep2);
            }
        }
    })();
</script>
