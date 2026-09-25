    <!-- doubt collage -->


    <div class="collage-doubt__screen">
        <div class="section-frame">
            <div class="collage-doubt--inner">
                <div class="page-title page-collage-title doubt-title">
                    {!! trans('collage_new.z4_doubt_screen_title') !!}
                </div>
                <div class="collage-doubt--block">
                    <div class="collage-doubt--content">
                        <div class="c-db-questions">
                            <div class="c-db-question">
                                <img loading="lazy" width="112" height="157" src="{{ asset('images/collage/tel.svg') }}"  @frontendAlt('partials/collage_new/doubt_screen.blade.php', (asset('images/collage/tel.svg')), 'Viar Image', '')>
                                <span>{!! trans('collage_new.z4_doubt_screen_question') !!}</span>
                            </div>
                            <div class="c-db-question">
								{!! trans('collage_new.z4_doubt_screen_question_text') !!}
                            </div>
                            <div class="c-db-question c-dbp-question">
                                <p>
                                    {!! trans('collage_new.z4_doubt_screen_question_price') !!}
                                </p>
                            </div>
                            <div class="c-db-question">
                                <p>{!! trans('collage_new.z4_doubt_screen_question_text2') !!}</p>
                            </div>
                        </div>
                        <a href="#collage-generator" class="c-db-btn">
                            {!! trans('collage_new.z4_doubt_screen_btn') !!}
                        </a>
                    </div>
                    <div class="collage-doubt-bg">

                    </div>
                </div>
                <div class="absolute-elements">
                    <div class="collage-d-express">
                        <img loading="lazy" width="87" height="73" src="{{ asset('images/collage/express.svg') }}"  @frontendAlt('partials/collage_new/doubt_screen.blade.php', (asset('images/collage/express.svg')), 'Viar Image', '')>
                        <p>{!! trans('collage_new.z4_doubt_screen_express_text1') !!}</p>
                    </div>
                    <div class="collage-d-gift">
                        <img loading="lazy" width="120" height="106" src="{{ asset('images/collage/gift.svg') }}"  @frontendAlt('partials/collage_new/doubt_screen.blade.php', (asset('images/collage/gift.svg')), 'Viar Image', '')>
                        <p>{!! trans('collage_new.z4_doubt_screen_express_text2') !!}</p>
                    </div>
                </div>
            </div>
        </div>

		<div class="ellipse el-desk ellipse-b-dbt">
			<img src="{{ asset('images/icon/ellipse-whete.svg') }}"  loading="eager"  @frontendAlt('partials/collage_new/doubt_screen.blade.php', (asset('images/icon/ellipse-whete.svg')), 'img', '')>
		</div>

		<div class="c-mob-d">
			<div class="section-frame">
				<div class="c-db-question c-db-qmob">
					<p>Вы сможете смотреть на него и <strong>вспоминать радостные моменты</strong> из жизни каждый день.</p>
				</div>
				<a href="#collage-generator" class="c-db-btn">
					Перейти в конструктор
				</a>
			</div>
		</div>

    </div>

