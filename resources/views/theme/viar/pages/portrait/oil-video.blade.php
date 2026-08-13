<section class="example-section divider-container" id="examples">
	<div class="section-frame">

		<div class="example-bottom">
			<div class="eb-grid">
				<div class="eb-grid-img">
					<picture>
						<source media="(max-width: 576px)" srcset="{{ ver_asset(env('THEME') . 'images/oil/video_1.webp') }}"
							type="image/webp">
						<source srcset="{{ ver_asset(env('THEME') . 'images/oil/video_1.webp') }}" type="image/webp">
						<img width="652" height="744" src="{{ ver_asset(env('THEME') . 'images/oil/video_1.png') }}" alt="">
					</picture>
					<div class="pmo-block">
						@lang('pages.portrait_oil.oil-video.t11')
					</div>


					<div class="video-container">
						{{-- <video width="1128" height="500" id="videoPlayer" preload="none" poster="{{ asset( 'storage/'.$page['video_img'] ) }}" controls="controls">
                          <source src="@isset(json_decode($page['video_file'])[0]->download_link){{ asset( 'storage/'.json_decode($page['video_file'])[0]->download_link ) }}@endisset" type="video/mp4">
                        </video> --}}
						<div class="video-btn video-open-btn">
							<img width="72" height="72" src="{{ ver_asset(env('THEME') . 'images/oil/video_btn_play.svg') }}"
								alt="">
						</div>
					</div>

				</div>
				<div class="eb-grid-content">
					<div class="page-title">
						@lang('pages.portrait_oil.oil-video.t12')
					</div>
					@lang('pages.portrait_oil.oil-video.t13')
					@lang('pages.portrait_oil.oil-video.t14')
				</div>
			</div>
		</div>

	</div>
</section>
@if($item->video && ($item->video != '[]'))
    @php $vid = (json_decode($item->video))[0]->download_link; @endphp

    <div class="video-section">
        <!-- Модалка -->
        <div class="video-modal" id="videoModal">
            <div class="video-modal__overlay"></div>
            <div class="video-modal__content">
                <button class="video-close-btn" type="button" aria-label="Close video">
                    &times;
                </button>
                <video id="videoPlayer" preload="metadata" controls>
                    <source src="{{ Voyager::image($vid) }}#t=0.5" type="video/mp4" />
                </video>
            </div>
        </div>
    </div>
@endif

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('videoModal');
                const openBtn = document.querySelector('.video-open-btn');
                const closeBtn = modal.querySelector('.video-close-btn');
                const video = modal.querySelector('video');
                const overlay = modal.querySelector('.video-modal__overlay');

                function openModal() {
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    video.play();
                }

                function closeModal() {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                    video.pause();
                    video.currentTime = 0;
                }

                openBtn.addEventListener('click', openModal);
                closeBtn.addEventListener('click', closeModal);
                overlay.addEventListener('click', closeModal);

                // ESC закрытие
                document.addEventListener('keydown', e => {
                    if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
                });
            });
        </script>
