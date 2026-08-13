<script>
        window.addEventListener('load', function () {
            if (typeof setForm === 'function') {
                setForm(0);
            }
        });

        (function () {
            const sliders = document.querySelectorAll('.canvas-form-step2-content.canvas-x-scroll-inline-flex');

            sliders.forEach(function (slider) {
                let isDown = false;
                let startX;
                let scrollLeft;

                slider.addEventListener('mousedown', function (e) {
                    isDown = true;
                    slider.classList.add('active');
                    startX = e.pageX - slider.offsetLeft;
                    scrollLeft = slider.scrollLeft;
                });

                slider.addEventListener('mouseleave', function () {
                    isDown = false;
                    slider.classList.remove('active');
                });

                slider.addEventListener('mouseup', function () {
                    isDown = false;
                    slider.classList.remove('active');
                });

                slider.addEventListener('mousemove', function (e) {
                    if (!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - slider.offsetLeft;
                    slider.scrollLeft = scrollLeft - (x - startX);
                });

                slider.addEventListener('touchstart', function (e) {
                    isDown = true;
                    startX = e.touches[0].pageX - slider.offsetLeft;
                    scrollLeft = slider.scrollLeft;
                });

                slider.addEventListener('touchend', function () {
                    isDown = false;
                });

                slider.addEventListener('touchmove', function (e) {
                    if (!isDown) return;
                    const x = e.touches[0].pageX - slider.offsetLeft;
                    slider.scrollLeft = scrollLeft - (x - startX);
                });
            });
        })();
</script>
<div class="form-types canvas-x-scroll">
        <div class="form-image calcForm active" onclick="setForm(0)" data-id="1">
            <img src="{{ asset('images/form1.svg') }}" alt="">
            <div class="selected-icon">
                <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                </svg>
            </div>
        </div>
        <div class="form-image calcForm" onclick="setForm(1)" data-id="2">
            <img src="{{ asset('images/form2.svg') }}" alt="">
            <div class="selected-icon">
                <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                </svg>
            </div>
        </div>
        <div class="form-image calcForm" onclick="setForm(2)" data-id="3">
            <img src="{{ asset('images/form3.svg') }}" alt="">
            <div class="selected-icon">
                <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                </svg>
            </div>
        </div>
        <div class="form-image calcForm" onclick="setForm(3)" data-id="4">
            <img src="{{ asset('images/form5.svg') }}" alt="">
            <div class="selected-icon">
                <svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.90391 8.96884C4.643 8.96884 4.3911 8.87105 4.19842 8.69487L0.337368 5.16245C-0.086973 4.77427 -0.114713 4.11735 0.275141 3.69409C0.665745 3.27307 1.32475 3.2447 1.74984 3.63213L4.83643 6.45688L10.1827 0.582689C10.5695 0.157186 11.23 0.125087 11.6574 0.510279C12.084 0.895472 12.117 1.55239 11.7293 1.97789L5.67762 8.62844C5.49019 8.83298 5.23003 8.9554 4.95189 8.96884C4.93539 8.96884 4.91965 8.96884 4.90391 8.96884Z" fill="white"></path>
                </svg>
            </div>
        </div>
</div>
