@include(env('THEME_RESOURCES') . 'account.breads', ['page' => $page])

<div class="main-cabinet">
	<div class="section-frame">
		<div class="main-cabinet__inner">
			<div class="cabinet-top__block">
				<div class="page-title cabinet-title">
					<span>@lang('account_new.unpaid.title')</span>
					<a class="activeSpan" href="{{ route('logout') }}"
						onclick="event.preventDefault(); document.getElementById('logout-form').submit();">@lang('account_new.btn.logout')</a>
				</div>
				<p>Оплата заказов поделена на <b class="activeSpan">2 переода</b> ! Количество сделанных заказов <b class="activeSpan">( 25 )</b> !</p>
			</div>
			<div class="cabinet-container">

				@include(env('THEME_RESOURCES') . 'account.menu')

				<div class="cabinet-grid">
					<div class="cabinet-content">
						<div class="cabinet-payState">
							<div class="cabinet-payState__body scroll-box">
								<div class="cabinet-scroll__nav">
									<div class="scroll-up">
										<svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path
												d="M17.8577 12.7766C17.7308 12.9255 17.5848 13 17.4198 13C17.2548 13 17.1089 12.9255 16.982 12.7766L9.5 3.99828L2.01804 12.7766C1.89112 12.9255 1.74516 13 1.58016 13C1.41516 13 1.2692 12.9255 1.14228 12.7766L0.190381 11.6598C0.0634605 11.5109 -7.7486e-07 11.3396 -7.7486e-07 11.146C-7.7486e-07 10.9525 0.0634605 10.7812 0.190381 10.6323L9.06212 0.223368C9.18904 0.0744559 9.335 0 9.5 0C9.665 0 9.81096 0.0744559 9.93788 0.223368L18.8096 10.6323C18.9365 10.7812 19 10.9525 19 11.146C19 11.3396 18.9365 11.5109 18.8096 11.6598L17.8577 12.7766Z"
												fill="#FA7846" />
										</svg>
									</div>
									<div class="scroll-down">
										<svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path
												d="M1.14229 0.223367C1.26921 0.0744551 1.41516 -1.13249e-06 1.58016 -1.13249e-06C1.74516 -1.13249e-06 1.89112 0.0744551 2.01804 0.223367L9.5 9.00172L16.982 0.223367C17.1089 0.0744551 17.2548 -1.13249e-06 17.4198 -1.13249e-06C17.5848 -1.13249e-06 17.7308 0.0744551 17.8577 0.223367L18.8096 1.34021C18.9365 1.48912 19 1.66037 19 1.85395C19 2.04754 18.9365 2.21879 18.8096 2.3677L9.93788 12.7766C9.81096 12.9255 9.665 13 9.5 13C9.335 13 9.18904 12.9255 9.06212 12.7766L0.19038 2.3677C0.0634597 2.21879 0 2.04754 0 1.85395C0 1.66037 0.0634597 1.48912 0.19038 1.34021L1.14229 0.223367Z"
												fill="#FA7846" />
										</svg>
									</div>
								</div>
								<div class="cabinet-table scroll-block">
									<div class="table-top table-grid">
										<div class="table-item table-head">
											Номер заказа
										</div>
										<div class="table-item table-head">
											Количество человек
										</div>
										<div class="table-item table-head">
											Тип портрета
										</div>
										<div class="table-item table-head">
											Дата заказа
										</div>
										<div class="table-item table-head">
											Дата выполненния
										</div>
										<div class="table-item table-head">
											Стоимость заказа
										</div>
									</div>
									<div class="table-body">
										<div class="table-row table-grid">
											<div class="table-box">
												<p class="p-mobile">Номер
													заказа</p>
												<p class="t-text"><span>№3489</span></p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Количество человек</p>
												<p class="t-text">5</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Тип портрета</p>
												<p class="t-text">Clasic Portrait</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Дата заказа</p>
												<p class="t-text">04.04.2023</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Дата выполненния</p>
												<p class="t-text">05.05.2023</p>
											</div>
											<div class="table-box tablePrice-box">
												<p class="p-mobile">Стоимость заказа</p>
												<div class="table-innerBox inputContainer active">
													<p>Администратор:</p>
													<div class="row">
														<p>20$</p>
														<p class="pblack">14.05.2023</p>
													</div>
													<form class="inputTable">
														<p>Вы:</p>
														<input type="tablePrice" value="30$">
														<button>Добавить</button>
													</form>
												</div>
											</div>
											<div class="table-box tableComments-box">
												<form class="tableTextarea inputContainer">
													<textarea name="tableComments" placeholder="Напишите  есть ли у Вас  дополнительные комментарии"></textarea>
													<button>@lang("account_new.orders.chat.add_comment")</button>
												</form>
											</div>
										</div>
									</div>
									<div class="table-body">
										<div class="table-row table-grid">
											<div class="table-box">
												<p class="p-mobile">Номер
													заказа</p>
												<p class="t-text"><span>№3489</span></p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Количество человек</p>
												<p class="t-text">5</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Тип портрета</p>
												<p class="t-text">Clasic Portrait</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Дата заказа</p>
												<p class="t-text">04.04.2023</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Дата выполненния</p>
												<p class="t-text">05.05.2023</p>
											</div>
											<div class="table-box tablePrice-box">
												<p class="p-mobile">Стоимость заказа</p>
												<div class="table-innerBox inputContainer active">
													<p>Администратор:</p>
													<div class="row">
														<p>20$</p>
														<p class="pblack">14.05.2023</p>
													</div>
													<form class="inputTable">
														<p>Вы:</p>
														<input type="tablePrice" value="30$">
														<button>Добавить</button>
													</form>
												</div>
											</div>
											<div class="table-box tableComments-box">
												<form class="tableTextarea inputContainer">
													<textarea name="tableComments" placeholder="Напишите  есть ли у Вас  дополнительные комментарии"></textarea>
													<button>@lang("account_new.orders.chat.add_comment")</button>
												</form>
											</div>
										</div>
									</div>
									<div class="table-body">
										<div class="table-row table-grid">
											<div class="table-box">
												<p class="p-mobile">Номер
													заказа</p>
												<p class="t-text"><span>№3489</span></p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Количество человек</p>
												<p class="t-text">5</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Тип портрета</p>
												<p class="t-text">Clasic Portrait</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Дата заказа</p>
												<p class="t-text">04.04.2023</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Дата выполненния</p>
												<p class="t-text">05.05.2023</p>
											</div>
											<div class="table-box tablePrice-box">
												<p class="p-mobile">Стоимость заказа</p>
												<div class="table-innerBox inputContainer active">
													<p>Администратор:</p>
													<div class="row">
														<p>20$</p>
														<p class="pblack">14.05.2023</p>
													</div>
													<form class="inputTable">
														<p>Вы:</p>
														<input type="tablePrice" value="30$">
														<button>Добавить</button>
													</form>
												</div>
											</div>
											<div class="table-box tableComments-box">
												<form class="tableTextarea inputContainer">
													<textarea name="tableComments" placeholder="Напишите  есть ли у Вас  дополнительные комментарии"></textarea>
													<button>@lang("account_new.orders.chat.add_comment")</button>
												</form>
											</div>
										</div>
									</div>
									<div class="table-body">
										<div class="table-row table-grid">
											<div class="table-box">
												<p class="p-mobile">Номер
													заказа</p>
												<p class="t-text"><span>№3489</span></p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Количество человек</p>
												<p class="t-text">5</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Тип портрета</p>
												<p class="t-text">Clasic Portrait</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Дата заказа</p>
												<p class="t-text">04.04.2023</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Дата выполненния</p>
												<p class="t-text">05.05.2023</p>
											</div>
											<div class="table-box tablePrice-box">
												<p class="p-mobile">Стоимость заказа</p>
												<div class="table-innerBox inputContainer active">
													<p>Администратор:</p>
													<div class="row">
														<p>20$</p>
														<p class="pblack">14.05.2023</p>
													</div>
													<form class="inputTable">
														<p>Вы:</p>
														<input type="tablePrice" value="30$">
														<button>Добавить</button>
													</form>
												</div>
											</div>
											<div class="table-box tableComments-box">
												<form class="tableTextarea inputContainer">
													<textarea name="tableComments" placeholder="Напишите  есть ли у Вас  дополнительные комментарии"></textarea>
													<button>@lang("account_new.orders.chat.add_comment")</button>
												</form>
											</div>
										</div>
									</div>
									<div class="table-body">
										<div class="table-row table-grid">
											<div class="table-box">
												<p class="p-mobile">Номер
													заказа</p>
												<p class="t-text"><span>№3489</span></p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Количество человек</p>
												<p class="t-text">5</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Тип портрета</p>
												<p class="t-text">Clasic Portrait</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Дата заказа</p>
												<p class="t-text">04.04.2023</p>
											</div>
											<div class="table-box">
												<p class="p-mobile">Дата выполненния</p>
												<p class="t-text">05.05.2023</p>
											</div>
											<div class="table-box tablePrice-box">
												<p class="p-mobile">Стоимость заказа</p>
												<div class="table-innerBox inputContainer active">
													<p>Администратор:</p>
													<div class="row">
														<p>20$</p>
														<p class="pblack">14.05.2023</p>
													</div>
													<form class="inputTable">
														<p>Вы:</p>
														<input type="tablePrice" value="30$">
														<button>Добавить</button>
													</form>
												</div>
											</div>
											<div class="table-box tableComments-box">
												<form class="tableTextarea inputContainer">
													<textarea name="tableComments" placeholder="Напишите  есть ли у Вас  дополнительные комментарии"></textarea>
													<button>@lang("account_new.orders.chat.add_comment")</button>
												</form>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="cabinet-content__icon">
							<img src="{{ asset(config('theme.current') . '/images')}}/cabinet/credit-card.svg" width="82" height="82" alt="Viar Cabinet Peding Orders">
						</div>
					</div>

					
				</div>
			</div>
		</div>
	</div>
</div>

@include(config('theme.resource') . 'pages.gallery.zpart_viarcanvas_is')
