@extends('voyager::master') @section('content')
	{{-- new --}}
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	{{-- endnew --}}
	<div class="container-fluid">
		<h1 class="page-title"><i class="voyager-basket"></i> Заказы</h1>
		<div style="display: table;">
			<a href="#" class="curier-call-btn"><img src="{{ asset('images/venipak.png') }}" alt="" /><span class="curier">ВЫЗОВ
					КУРЬЕРА</span></a>
		</div>
		{{-- new --}}
		<div class="order__courier js__closing__form" style="display: none; margin-top: 30px;">
			<div class="close__btn js__close">×</div>
			@include('voyager::form_courier')
		</div>
		@if (session()->has('error_vin'))
			<br />
			<div class="alert alert-warning">
				@if (is_array(session('error_vin')))
					@foreach (session('success_vin')['text'] as $err_item)
						{{ $err_item }}
					@endforeach
				@else
					{{ session('error_vin') }}
				@endif
			</div>
			@endif @if (session()->has('success_vin'))
				<br />
				<div class="alert alert-success">
					@if (is_array(session('success_vin')['text']))
						@foreach (session('success_vin')['text'] as $s_item)
							<p>№: {{ $s_item }}</p>
						@endforeach
					@else
						<p>№: {{ session('success_vin')['text'] }}<br /></p>
					@endif
				</div>
			@endif
			{{-- endnew --}}
	</div>



	<div class="page-content">
		@include('voyager::alerts') @include('voyager::dimmers')
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-bordered">
					<div class="panel-body">
						<div class="table-responsive">
							<div id="dataTable_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
								<div class="row">
									<div class="col-sm-12">
										<table id="dataTable" class="table table-hover dataTable no-footer" role="grid"
											aria-describedby="dataTable_info">
											<thead>
												<tr role="row">
													@foreach ($order_columns as $column)
														<th data-index="{{ $loop->index }}" class="sorting_disabled" tabindex="0" rowspan="1" colspan="1">
															@if ($loop->index === 4)
															@elseif($loop->index === 5)
																Комментарий
															@else
																{{ $column }}
															@endif
														</th>
													@endforeach
													<th class="sorting_disabled" rowspan="1" colspan="1" aria-label="Доступные действия">Комментарий админа</th>
												</tr>
											</thead>
											<div>
												@foreach ($orders as $order)
													@php
														$order = (array) $order;
														$order['items'] = json_decode($order['items'], true);
														$order['delivery'] = json_decode($order['delivery'], true);
														$order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';
														$statusName = ['payed' => 'оплачено', 'not_payed' => 'не оплачено', 'prepayment' => 'предоплата'];
													@endphp

													<tr style="text-align: center;" role="row" class="odd odd-id-{{ $order['id'] }}">
														<td style="text-align: center;">
															№: {{ $order['id'] }}
														</td>
														@foreach ($order_columns as $key => $column)
															<td style="text-align: center;" class="td-{{ $loop->index }}">
																@if ($key == 'firts')
																	<p>
																		Дата заказа: {{ date('d.m.Y', strtotime($order['created_at'])) }}
																	</p>
																	<div class="status"
																		style="padding: 5px 10px; background-color: <?= $order['payment_status'] == 'not_payed' ? 'red' : ($order['payment_status'] == 'prepayment' ? '#c19300' : 'green') ?>;color: white;margin: 5px 0px; border-radius: 7px;">
																		{{ $statusName[$order['payment_status']] }}
																	</div>
																	<div>
																		<a href="#" class="eticet-create-btn" data-id="#order-{{ $order['id'] }}">
																			<img src="{{ asset('images/venipak.png') }}" alt="" />
																			<span>СОЗДАНИЕ ЭТИКЕТКИ</span>
																		</a>
																	</div>
																	<div>
																		<div class="nums-list">
																			Номера этикеток:<br />
																			<?php $e_list = explode(',', rtrim($order["labels"], ","));
                                                foreach ($e_list as $lbl) { ?>
																			<form class="f_print" action="{{ route('print_label') }}" method="POST">
																				<br />
																				{{ csrf_field() }}
																				<span>{{ $lbl }}</span>
																				<input name="label_code" type="hidden" value="{{ $lbl }}" required />
																				<button type="submit">На печать</button>
																			</form>
																			<?php } ?>
																		</div>
																	</div>
																@elseif($key == 'second')
																	{{-- <p class="order__id">{{ $order["id"] }}</p> --}}
																	@php
																		$orderUser = $order['user'] ?? null;
																		$orderDelivery = (isset($order['delivery']) && is_array($order['delivery'])) ? $order['delivery'] : [];
																	@endphp
																	<p>Данные пользователя:</p>
																	<div>
																		<p>
																			@if ($orderUser)
																				{{ $orderUser->first_name }}
																				{{ $orderUser->last_name }}
																			@else
																				{{ trim(((string) ($orderDelivery['first_name'] ?? '')) . ' ' . ((string) ($orderDelivery['last_name'] ?? ''))) }}
																			@endif
																		</p>
																		<p>{{ $orderUser ? $orderUser->phone : ($orderDelivery['payer_phone'] ?? ($orderDelivery['phone'] ?? '')) }}</p>
																		<p>{{ $orderUser ? $orderUser->email : ($orderDelivery['email'] ?? '') }}</p>
																		@if ($orderUser)
																			<p>{{ $orderUser->status }}</p>
																			<p>Язык пользователя: {{ $orderUser->preferredLocale() }}</p>
																		@else
																			<p style="color:#b94a48;">Пользователь не найден</p>
																		@endif
																	</div>
																@elseif($key == 'third')
																	<div style="background: #f8fafc;
																																								padding: 5px;
																																								color: #313942;">
																		<p>Даные получателя:</p>
																		<p>Email: {{ $order['delivery']['email'] }}</p>
																		<p>Имя: {{ $order['delivery']['first_name'] }}</p>
																		<p>Фамилия: {{ $order['delivery']['last_name'] }}</p>
																		@php
																			$recipientPhone = $order['delivery']['phone'] ?? '-';
																			$payerPhone = $order['delivery']['payer_phone'] ?? (isset($order['user']) ? $order['user']->phone : '-');
																			$hasRecipientPhone = array_key_exists('payer_phone', $order['delivery'] ?? [])
																				|| !empty($order['delivery']['is_no_payer']);
																			$normalizePhone = function ($phone) {
																				return preg_replace('/\\D+/', '', (string) $phone);
																			};
																			$isSamePhone = $payerPhone && $recipientPhone && $normalizePhone($payerPhone) === $normalizePhone($recipientPhone);
																			$displayPhone = ($payerPhone && $payerPhone !== '-') ? $payerPhone : $recipientPhone;
																		@endphp
																		@if($hasRecipientPhone && !$isSamePhone)
																			<p>Телефон заказчика: {{ $payerPhone }}</p>
																			<p>Телефон получателя: {{ $recipientPhone }}</p>
																		@else
																			<p>Телефон: {{ $displayPhone }}</p>
																		@endif
																		<p>Страна: {{ $order['delivery']['country'] }}</p>
																		<p>Адресс: {{ $order['delivery']['address'] }}</p>
																		<p>Индекс: {{ $order['delivery']['postal_index'] }}</p>

																		@if (isset($order['delivery']['when_send']))
																			<p>Дата доставки: {{ $order['delivery']['when_send'] }}</p>
																		@endif

																		@if (isset($order['delivery']['lang']))
																			<p>Язык пользователя: {{ $order['delivery']['lang'] }}</p>
																		@endif

																		@if ($order['order_image'])
																			<p><a target="_blank" href="{{ $order['order_image'] }}">Картинка в заказе</a></p>
																		@endif

																		@if ($order['delivery']['sposob'] == 'to_the_door')
																			<p>Доставка до дверей дома или работы</p>
																		@elseif($order['delivery']['sposob'] == 'pickup_Riga')
																			<p>Самовывоз Рига</p>
																		@elseif($order['delivery']['sposob'] == 'pickup_Daugavplis')
																			<p>Самовывоз Даугавпилс</p>
																		@elseif($order['delivery']['sposob'] == 'venipak')
																			<p>Доставка Venipak</p>
																		@elseif($order['delivery']['sposob'] == 'pickup_at_viar_workshop')
																			<p>Забрать в мастерской VIAR</p>
                                                                        @elseif($order['delivery']['sposob'] == 'city_delivery')
                                                                            <p>Доставка по {{$order['delivery']['city']}}</p>
																		@endif





																		@if ($order['payment'] == 'cash_in_office')
																			<p>Наличными при получении в офисе</p>
																		@elseif($order['payment'] == 'on_delivery')
																			<p>Оплата во время доставки</p>
																		@elseif($order['payment'] == 'transfer')
																			<p>Оплата перечислением</p>
																		@elseif($order['payment'] == 'online_paysera')
																			<p>Онлайн банкинг</p>
																		@elseif($order['payment'] == 'google_pay')
																			<p>Google Pay</p>
																		@elseif($order['payment'] == 'apple_pay')
																			<p>Apple Pay</p>
																		@elseif($order['payment'] == 'paypalOnetimePayment')
																			<p>PayPal</p>
                                                                        @elseif($order['payment'] == 'prepayment')
                                                                            <p>Предоплата</p>
																		@elseif($order['payment'] == 'creditcart')
																			<p>Картой онлайн</p>
																		@endif
																		@if (isset($order['delivery']['comment']))
																			<p>Комментарий: {{ $order['delivery']['comment'] }}</p>
																		@endif
																	</div>
																	<p>
																		Страна:{{ $order['country'] }}. <br>
																		Адресс: {{ $order['delivery']['address'] }}. <br>
																		Индекс: {{ $order['delivery']['postal_index'] }}
																	</p>
																	<p>
																		@if ($order['payment'] == 'cash_in_office')
																			<img style="max-width: 60px;" src="/storage/images/cash.png" title="Наличными при получении в офисе"
																				alt="Наличными при получении в офисе" />
																		@elseif($order['payment'] == 'on_delivery')
																			<img style="max-width: 60px;" src="/storage/images/delivery-payment.png"
																				title="Оплата во время доставки" alt="Оплата во время доставки" />
																		@elseif($order['payment'] == 'transfer')
																			<img style="max-width: 60px;" src="/storage/images/visa.png" title="Оплата перечислением"
																				alt="Оплата перечислением" />
																		@elseif($order['payment'] == 'online_paysera')
																			<img style="max-width: 60px;" src="/storage/images/paysera.svg" style="max-width:100px;" title="Онлайн банкинг"
																				alt="Онлайн банкинг" />
																		@elseif($order['payment'] == 'google_pay')
																			<img style="max-width: 60px;" src="/theme/viar/img/cart/pbl_ap.png" style="max-width:100px;" title="Google Pay"
																				alt="Google Pay" />
																		@elseif($order['payment'] == 'apple_pay')
																			<img style="max-width: 60px;" src="/theme/viar/img/cart/pbl_jp.png" style="max-width:100px;" title="Онлайн банкинг"
																				alt="Онлайн банкинг" />
																		@elseif($order['payment'] == 'paypalOnetimePayment')
																			<img style="max-width: 60px;" src="/theme/viar/img/icons/PayPal.svg" style="max-width:100px;" title="Онлайн банкинг"
																				alt="Онлайн банкинг" />
																		@elseif($order['payment'] == 'creditcart')
																			<img style="max-width: 60px;" src="/theme/viar/img/cart/payments.png" title="Картой онлайн"
																				alt="Картой онлайн" />
                                                                        @elseif($order['payment'] == 'prepayment')
                                                                            <img style="max-width: 60px;" src="/theme/viar/img/cart/prepaid2.png" title="Предоплата"
                                                                                 alt="Предоплата" />
																		@endif
																	</p>
																	<p>
																		@if ($order['delivery']['sposob'] == 'to_the_door')
																			<img style="max-width: 60px;" src="/storage/images/truck_icon.png"
																				title="Доставка до дверей дома или работы" alt="Доставка до дверей дома или работы" />
																		@elseif ($order['delivery']['sposob'] == 'venipak')
																			<img style="max-width: 60px;" data-delivery="1" src="/images/venipak_new.png" style="max-width:100px;" title="Доставка Venipak" alt="Доставка Venipak" />
																		@elseif ($order['delivery']['sposob'] == 'pickup_at_viar_workshop')
																			<img style="max-width: 60px;" data-delivery="1" src="/images/pickup_at_viar_workshop.png" style="max-width:100px;" title="Доставка Venipak" alt="Доставка Venipak" />
																		@elseif($order['delivery']['sposob'] == 'pickup_Riga')
																			<img style="max-width: 60px;" src="/storage/images/office.png" title="Самовывоз Рига"
																				alt="Самовывоз Рига" />
																		@elseif($order['delivery']['sposob'] == 'pickup_Daugavplis')
																			<img style="max-width: 60px;" src="/storage/images/office.png" title="Самовывоз Даугавпилс"
																				alt="Самовывоз Даугавпилс" />
                                                                        @if ($order['delivery']['sposob'] == 'city_delivery')
                                                                                <img style="max-width: 60px;" src="/storage/images/truck_icon.png"
                                                                                     title="Доставка по {{$order['delivery']['city']}}" alt="Доставка по {{$order['delivery']['city']}}" />
                                                                        @endif
																	</p>
																@elseif($key == 'four')
																	<p>Стоимость:
																		@php
																			$pr = str_replace(' €', '', $order['price']);
																		@endphp
																		@if ($pr != $order['sale_price'])
																			<strike>{{ $order['price'] }}</strike> <b>{{ $order['sale_price'] }} €</b><br>
																		@else
																			<b>{{ $order['sale_price'] }} €</b>
																		@endif
																		<br>
																		@if (isset($order['delivery']['deliv_price']))
																			Доставка: <b>{{ $order['delivery']['deliv_price'] }} €</b>
																		@endisset
																</p>
																<hr>

																@php
																	$user_link = \URL::to('/') . '/admin/user/' . $order['user_id'];
																@endphp

																@if (isset($order['delivery']['is_coupon_30_40']))
																	<p style="color:orange;font-weight: bold;">Использован купон:30x40</p>
																@endif

																@if (isset($order['delivery']['is_coupon_dates']))
																	<p style="color:orange;font-weight: bold;">Использован купон:2 даты</p>
																@endif
                                                                @if (isset($order['delivery']['has_invited_sale']))
																	<p style="color:orange;font-weight: bold;">Использована скидка по приглашению 5 евро</p>
																@endif
																<ul class="list__adm__items">
																	@foreach ($order['items'] as $product)
																		@if (!isset($product['sumPrice']))
																			@continue
																		@endif


																		<li>
																			<a target="_blank" href='{{ $order['pdf_link'] }}'>Счет</a>
																		</li>

																		@if ($order['pdf_approved'] != 1)
																			<br>
																			<li>
																				@php
																					$ac_link = str_replace('admin/', '', $user_link);
																				@endphp
																				<a href='{{ $ac_link }}/approve_checkout/{{ $order['id'] }}'>Подтвердить счет</a>
																			</li>
																			<br>
																		@endif

																		<li>
																			<a href='{{ $user_link }}/leave_rev/{{ $order['id'] }}'>Запрос отзыва</a>
																		</li>
																		<br>

																		@isset($product['name'])
																			<li>
																				Товар:{{ $product['name'] }}
																			</li>
																		@endif

																		@if (isset($product['activeImage']))
																			@if (isset($product['is_construct']) || isset($product['is_canvas_inter']) || isset($product['is_modular_inter']) || isset($product['is_oil_portrait']) || (isset($product['is_gall_with_img']) && $product['is_gall_with_img'] == 1))
																				@php
																					$svg1 = str_replace('.jpeg', '.svg', $product['activeImage']);
																					$file = str_replace('.png', '.svg', $svg1);
																					$file = str_replace('.jpg', '.svg', $file);
																					$file = str_replace('.heic', '.svg', $file);
																					$file = str_replace('.heif', '.svg', $file);
																					$file = str_replace('/storage/', '/uploads/', $file);
																					$file = strstr($file, '/uploads/');
																					$cur_file = $file ?: null;
																					$file = $cur_file ? ($_SERVER['DOCUMENT_ROOT'] . '/public/' . ltrim($cur_file, '/')) : null;
																					$file = $file ? str_replace('/public/public', '/public', $file) : null;
																				@endphp

																				@if (!$file || !file_exists($file))
																					@isset($product['orig_images'])
																						@php
																							$origSrc = order_image_url($product['orig_images'][0]);
																						@endphp
																						<img class="def_img_some" style="max-width:223px;" src="{{ $origSrc }}"
																							alt="">
																					@else
																						@php
																							$activeSrc = order_image_url($product['activeImage']);
																						@endphp
																						<img class="def_img_some" style="max-width:223px;" src="{{ $activeSrc }}" alt="">
																						<br>
																						<a href="{{ $activeSrc }}">Картинка</a>
																					@endisset
																				@else
																					@php
																						$activeSrc = order_image_url($product['activeImage']);
																					@endphp
																					<a style="position:relative;display:block;" href="{{ $cur_file }}">
																						<img class="def_img_some" style="max-width:223px;opacity:0;"
																							src="{{ $activeSrc }}" alt="">
																						@php
																							$svg_file = file_get_contents($file);
																							$orgin_file_name = explode('__orig__', $product['activeImage']);
																							$file = str_replace($orgin_file_name[1], $product['activeImage'], $svg_file);

																							$file = str_replace('id="image"', 'id="image' . $loop->index . '"', $file);

																							$file = str_replace('<rect', '<rect height="100%"', $file);

																							$file = str_replace('fill="url(#image)"', 'fill="url(#image' . $loop->index . ')"', $file);

																							echo "<div style='width:100%; height:100%;position:absolute;left:0;top:0;right:0;bottom:0;' >" . $file . '</div>';
																						@endphp
																					</a>
																					<a href="{{ $activeSrc }}">Оригинал</a>
																				@endif
																			@else
																				@php
																					$activeSrc = order_image_url($product['activeImage']);
																				@endphp
																				<img target="_blank" class="def_img" src="{{ $activeSrc }}"
																					alt="">
																				<a href="{{ $activeSrc }}">Оригинал</a>
																			@endif
																		@endif


																		@isset($product['fon_images'])
																			@foreach ($product['fon_images'] as $fon_img)
																				<a href="{{ $fon_img }}">Фон-{{ $loop->index }}</a>
																			@endforeach
																		@endisset


																		@include('partials.all_basket_order_items')

																		@isset($product['sumFormatedPrice'])
																			<p>Цена: {{ $product['sumFormatedPrice'] }}</>
																			@endisset
																			@if (!$loop->last)
																				<hr>
																			@endif
																@endforeach
															@elseif($key == 'five')
																<p>{{ $order['comment'] }}</p>
															@elseif($key == 'six')
																<p>{{ $order['admin_comment'] }}</p>
														@endif
														</li>
												@endforeach
												</ul>
												<td>
													<div class="form-group">
														<label for="status">Статус заказа: </label>
														<select id="status" name="status"
															onchange="changeStatus(this.options[this.selectedIndex].value, {{ $order['id'] }})">
															<option value="watching" <?= $order['status'] == 'watching' ? 'selected' : '' ?>>На рассмотрении</option>
															<option value="pegging" <?= $order['status'] == 'pegging' ? 'selected' : '' ?>>В процессе</option>
															<option value="in_production" <?= $order['status'] == 'in_production' ? 'selected' : '' ?>>В производстве</option>
															<option value="sended" <?= $order['status'] == 'sended' ? 'selected' : '' ?>>Отправлен</option>
															<option value="send_lubanas" <?= $order['status'] == 'send_lubanas' ? 'selected' : '' ?>>Отправлен на Лубанас 65</option>
															<option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Завершен</option>
														</select>
													</div>
													<br>
													<br>
													<div class="form-group">
														@php
															$user_status_id = App\Models\User::getUserStatusById($order['user_id']);
														@endphp
														<label for="name">Статус клиента</label>
														<select name="status" id=""
															onchange="
																																														changeClientStatus(this.options[this.selectedIndex].value, {{ $order['user_id'] }})">
															@foreach ($statuses as $status)
																<option @if ($user_status_id == $status->id) selected @endif value="{{ $status->id }}">
																	{{ $status->name }}</option>
															@endforeach
														</select>
													</div>
													<div class="form-group">
														<br><br>
														@php
															$order_date = date('d.m.Y H:i', strtotime($order['created_at']));
														@endphp
														<b>номер заказа:</b> <span>{{ $order['id'] }}</span><br>
														<b>дата заказа:</b> <span> {{ $order_date }}</span>
														@if (isset($order['delivery']['when_send']))
															<br>
															@php
																$when_send = new DateTime($order['delivery']['when_send']);
															@endphp
															<b>желаемая дата доставки: {{ $when_send->format('d.m.Y') }}</b>

															@php
																$end_date = strtotime($when_send->format('d.m.Y'));
																$datediff = strtotime($order['created_at']) - $end_date;
															@endphp
															<b>доставить через:</b>
															@php
																$df = floor($datediff / (60 * 60 * 24));
																$day_diff = str_replace('-', '', $df);
															@endphp
															@if ($day_diff == 0)
																сегодня
															@else
																{{ $day_diff }} дня(ей)
															@endif
															<br>
															<b>тип заказа:
																@if ($day_diff < 4)
																	<p style="font-weight: bold;font-size:16px;color:red;">Срочный</p>
																@else
																	<p>Обычный</p>
																@endif
															</b>
														@endif
													</div>
													<br>
													<br>
													<div style="display: inline-block;">
														<p>
															<a class="btn btn-sm btn-warning pull-right view btn_orders"
																href="/admin/users/{{ $order['user_id'] }}" title="Подтвердить">
																<span class="hidden-xs hidden-sm">Просмотр
																	клиента</span>
															</a>
														</p>
														{{-- <p>
                                             <a class="btn btn-sm btn-warning pull-right view btn_orders"
                                                href="/admin/orders/{{ $order['id'] }}"
                                                title="Подтвердить">
                                             <span class="hidden-xs hidden-sm">Действующий
                                             заказ</span>
                                             </a>
                                          </p> --}}
														<p>
															<a href="/admin/orders?user_id={{ $order['user_id'] }}" title="Подтвердить"
																class="btn btn-sm btn-warning pull-right view btn_orders">
																<span class="hidden-xs hidden-sm">Все закаы ранее</span>
															</a>
														</p>
														@if ($order['status'] != 'completed')
															{{-- <p>
                                             <a href="javascript:;"
                                                onclick="aproveOrder( {{ $order['id'] }}, this)"
                                                title="Подтвердить"
                                                class="btn btn-sm btn-warning pull-right view"
                                                data-id="1" id="delete-1">
                                             <i class="voyager-check"></i>
                                             <span class="hidden-xs hidden-sm">Подтвердить</span>
                                             </a>
                                          </p> --}}
														@endif
														<p>
															<a href="javascript:;" onclick="deleteOrder( {{ $order['id'] }} )" title="Удалить"
																class="btn btn-sm btn-danger pull-right delete">
																<i class="voyager-trash"></i>
																<span class="hidden-xs hidden-sm">Удалить</span>
															</a>
														</p>
													</div>
													@if (!empty($order['photo']))
														<div style="
																																											background-color: gainsboro;
																																											margin: 10px 0px 0px 0px;
																																											">
															Исходящая накладная
														</div>
														<p>
															<a class="in_btn" href="{{ $order['photo'] }}">Открыть</a><a class="in_btn" download
																href="{{ $order['photo'] }}">Скачать</a>
														</p>
													@endif
												</td>
												</tr>
												@endforeach
											</div>
										</table>
										{{-- new --}}
										@foreach ($orders as $order)
											@php $order = (array)$order; @endphp
											<div class="eticet__form js__closing__form" style="display: none;" id="order-{{ $order['id'] }}">
												@include('voyager::form__label')
											</div>
										@endforeach
										{{-- endndew --}}
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<style>
		.list__adm__items {
			list-style: none;
		}

	</style>

	<script>
	 function changeStatus(val, id) {
	  $.ajax({
	   type: 'post',
	   headers: {
	    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	   },
	   url: '{{ route('change_order') }}',
	   data: {
	    status_name: val,
	    order_id: id
	   },
	   success: function(response) {

	    if (response) {
	     var data = jQuery.parseJSON(response);
	     if (data['info'] != 1) {
	      alert('Ошибка обновления статуса');
	     } else {
	      alert(data['success']);
	     }

	    }
	   }
	  });
	 }

	 function changeClientStatus(status_id, user_id) {
	  $.ajax({
	   type: 'post',
	   headers: {
	    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	   },
	   url: '{{ route('change_client_status') }}',
	   data: {
	    status_id: status_id,
	    user_id: user_id
	   },
	   success: function(response) {

	    if (response) {
	     var data = jQuery.parseJSON(response);
	     if (data['info'] != 1) {
	      alert('Ошибка обновления статуса');
	     } else {
	      alert(data['success']);
	     }

	    }
	   }
	  });
	 }

	 function deleteOrder(id) {
	  $.ajax({
	   type: 'post',
	   dataType: 'html',
	   headers: {
	    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	   },
	   url: '{{ route('delete_order') }}',
	   data: {
	    id: id
	   },
	   success: function(response) {

	    if (response) {
	     var data = jQuery.parseJSON(response);

	     if (data['Error']) {
	      alert(data['Error']);
	     } else if (data['success']) {
	      $('.odd-id-' + id).remove();
	     }
	    }
	   }
	  });
	 }

	 function aproveOrder(id, btn) {
	  $.ajax({
	   type: 'post',
	   dataType: 'html',
	   headers: {
	    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	   },
	   url: '{{ route('approve_order') }}',
	   data: {
	    id: id
	   },
	   success: function(response) {

	    if (response) {
	     var data = jQuery.parseJSON(response);

	     if (data['Error']) {
	      alert(data['Error']);
	     } else if (data['success']) {
	      var parentBtn = $(btn).parent().parent();
	      $('.status', parentBtn).text('completed');
	      $(btn).remove();
	     }
	    }
	   }
	  });
	 }
	</script>
	<link rel="stylesheet" href="/voyager/orders.css?v=1.2">
	@stop @section('javascript')
	<script>
	 // new
	 $(document).on("click", ".curier-call-btn", function(e) {
	  e.preventDefault();
	  $(".order__courier").stop().slideToggle("fast");
	 });

	 $(document).on("click", ".eticet-create-btn", function(e) {
	  e.preventDefault();
	  let id = $(this).data("id");
	  $(".eticet__form").hide(0);
	  $(id).stop().slideToggle("fast");
	  $("html, body").animate({
	   scrollTop: $(id).offset().top - 70
	  }, "fast");
	 });

	 var insurance_dif_coeff = 0.3;
	 var taxFree_insurance = 0;

	 $(document).on("change", "#insur", function(e) {
	  var th = $(this);
	  var suma = th.val();
	  if (suma > 10) {
	   var kaina = ((suma - taxFree_insurance) * insurance_dif_coeff) / 100;
	   if (kaina < 0) {
	    th.closest("table").find("#calc-re").html("0");
	   } else {
	    th.closest("table")
	     .find("#calc-res")
	     .html(Math.round(kaina * 100) / 100);
	   }
	  } else {
	   th.closest("table").find("#calc-res").html("0");
	  }
	 });

	 $(document).on("click", ".js__close", function(e) {
	  $(this).closest(".js__closing__form").stop().slideToggle("fast");
	 });

	 $(document).on("click", ".js__add__pack", function(e) {
	  e.preventDefault();
	  var th = $(this);
	  var prev = th.parent().prev("p");
	  prev.clone().insertAfter(prev);
	 });

	 // endnde
	</script>

	<style>
		b,
		optgroup,
		strong {
			font-weight: bold;
		}

	</style>
@stop
