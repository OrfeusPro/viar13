@extends('voyager::master')

@section('css')
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="stylesheet" href="/css/admin_order.css?v=4">
	<style>
		.form-bonuses {
			display: none;
		}

		.page-content .form-group p {
			margin: 0;
		}

		.page-content .form-group {
			margin-bottom: 10px;
		}

		.admin-section-block {
			border: 1px solid #d9d9d9;
			border-radius: 8px;
			padding: 14px;
			margin: 12px 0;
			background: #fafafa;
		}

		.sales-admin-block,
		.payment-admin-block,
		.delivery-admin-block {
			border: 1px solid #d9d9d9;
			border-radius: 8px;
			padding: 14px;
			margin: 12px 0;
			background: #fafafa;
		}

		.payment-status-select.status-not-payed {
			background: #ff9d9d;
			color: #1e2533;
		}

		.payment-status-select.status-payed {
			background: #2ba92b;
			color: #fff;
		}

		.payment-status-select.status-prepayment {
			background: #c19300;
			color: #fff;
		}

		.admin-section-title {
			text-align: center;
			margin: 0 0 12px;
			font-size: 18px;
		}

		.js_form_tab {
			border: 1px solid #d9d9d9;
			border-radius: 8px;
			padding: 12px;
			margin: 12px 0;
			background: #fafafa;
			position: relative;
		}

		.btn-rem-new {
			width: 44px;
			height: 34px;
			position: absolute;
			right: 12px;
			top: 12px;
			color: #fff;
		}

		.js_form_tab h2 {
			margin: 0 0 12px;
			font-size: 18px;
		}

		.js_form_tab textarea {
			width: 100%;
			min-height: 120px;
			resize: vertical;
		}

		.btn-add-new {
			margin-top: 8px;
		}
	</style>
@stop



@section('page_header')
	<h1 class="page-title">
		Создание заказа
	</h1>
@stop

@php
	// if(isset($order) && $order)
	// {
	//     $order['items'] = json_decode($order['items'], true);
	//     $order['delivery'] = json_decode($order['delivery'], true);
	// }
@endphp

@section('content')
	<div class="page-content container-fluid">
		<div class="form-edit-add">
			<form action="{{ route('create_admin_order_form') }}" method="POST" enctype="multipart/form-data">
				{{ csrf_field() }}
				<div class="row">
					@if (count($errors) > 0)
						<div class="alert alert-danger">
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					@if (session()->has('success'))
						<div class="alert alert-success">
							№{{ session()->get('order_id') }} - {{ session()->get('message') }}
						</div>
					@endif
					<div class="col-md-6">
						<div class="panel panel-bordered">

							{{-- order data --}}
							<div class="panel-body">
								<div class="form-group">
									<label class="control-label" for="manager">Менеджер:</label>
									<select name="manager" id="manager" class="form-control">
										<option value=""></option>
										@if (!empty($managers))
											@foreach ($managers as $manager)
												<option value="{{ $manager->id }}" @if (old('manager') == $manager->id) selected @endif>
													@if ($manager->nick){{ $manager->nick }} - @endif{{ $manager->email }}
												</option>
											@endforeach
										@endif
										<option value="all" @if (!old('manager') || old('manager') == 'all') selected @endif>Админ</option>
									</select>
								</div>

								<div class="form-group">
									<label for="admin_comment">Комментарий админа</label>
									<input type="text" class="form-control" id="admin_comment" name="admin_comment"
										placeholder="" value="{{ old('admin_comment') }}">
								</div>

								<div class="admin-section-block">
									<h3 class="admin-section-title">Данные пользователя</h3>

									<div class="form-group">
										<p>Email:</p>
										<input class="form-control" type="email" id="emailInput" name="email"
											value="@if (isset($user['email'])) {{ $user['email'] }} @endif">
										<p id="finduser"> </p>
									</div>

									<div class="form-group">
										<p>Имя:</p>
										<input class="form-control" type="text" name="first_name"
											value="@if (isset($user['first_name'])) {{ $user['first_name'] }} @endif">
									</div>

									<div class="form-group">
										<p>Фамилия:</p>
										<input class="form-control" type="text" name="last_name"
											value="@if (isset($user['last_name'])) {{ $user['last_name'] }} @endif">
									</div>

									<div class="form-group">
										<p>Телефон заказчика:</p>
										<input class="form-control" type="text" name="phone"
											value="@if (isset($user['phone']) && $user['phone']) {{ $user['phone'] }} @endif">
									</div>

									<div class="form-group">
										<p>Телефон получателя:</p>
										<input class="form-control" type="text" name="phone_rec"
											value="@if (isset($user['phone']) && $user['phone']) {{ $user['phone'] }} @endif">
									</div>

									<div class="form-group">
										<p>Комментарий к заказу (общий):</p>
										<input class="form-control" type="text" name="comment" value="">
									</div>
								</div>

								<div class="sales-admin-block">
									<div class="form-group">
										<p>Канал продаж:</p>
										<select class="form-control" name="a_order_from" placeholder="Канал продаж:">
											@foreach ($order_from as $from)
												<option value="{{ $from['id'] }}">{{ $from['title'] }}</option>
											@endforeach
										</select>
									</div>

									<div class="form-group">
										<p>Категория:</p>
										<select class="form-control" name="new_catid" placeholder="Категория">
											<option value="0" selected>Нет категории</option>
											@foreach ($style as $catid)
												<option value="{{ $catid['id'] }}">
													{{ str_replace('</span>', '', str_replace('<span>', '', $catid->getTranslatedAttribute('name'))) }}
												</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="payment-admin-block">
									<div class="form-group">
										<p>Оплата:</p>
										<select class="form-control" name="payment" placeholder="Оплата">
											<option value="cash_in_office">Наличными при получении в офисе</option>
											<option value="on_delivery">Оплата во время доставки</option>
											<option value="transfer">Оплата перечислением</option>
											<option value="online_paysera">Онлайн банкинг</option>
											<option value="google_pay">Google Pay</option>
											<option value="apple_pay">Apple Pay</option>
											<option value="paypalOnetimePayment">PayPal</option>
											<option value="creditcart">Картой онлайн</option>
											<option value="prepayment">Предоплата</option>
										</select>
									</div>

									<div class="form-group">
										<p>Статус оплаты:</p>
										<select class="form-control payment-status-select" name="payment_status" placeholder="Статус оплаты">
											<option value="not_payed">Не оплачено</option>
											<option value="prepayment">Предоплата</option>
											<option value="payed">Оплачено</option>
										</select>
									</div>
								</div>

								<div class="delivery-admin-block">
									<div class="form-group">
										<p>Доставка:</p>
										<select class="form-control" name="sposob" id="sposob" placeholder="Способ доставки">
											<option value="to_the_door">Доставка до дверей дома или работы</option>
											<option value="pickup_Riga">Самовывоз Рига</option>
											<option value="pickup_Daugavplis">Самовывоз Даугавпилс</option>
											<option value="venipak">Доставка Venipak</option>
											<option value="pickup_at_viar_workshop">Забрать в мастерской VIAR</option>
											<option value="city_delivery">Доставка по городу</option>
										</select>
									</div>

									<div class="form-group">
										<p>Страна:</p>
										<select class="form-control" name="country" placeholder="Страна">
											@foreach ($c_tels as $count)
												<option value="{{ $count['country_code'] }}" @if (isset($user['country']) && $user['country'] == $count['country_code']) selected @endif>
													{{ $count['country_code'] }}
												</option>
											@endforeach
										</select>
									</div>

									@php
										$createPickupPoints = $deliveryPickupAtViarWorkshop ?? [];
										$createDeliveryTowns = $deliveryTowns ?? [];
									@endphp
									<div class="form-group" id="pickup_workshop_container" style="display: none;">
										<p>Самовывоз (мастерская VIAR):</p>
										<select class="form-control" name="pickup_workshop_id" id="pickup_workshop_id">
											<option value="">-- выберите пункт --</option>
											@foreach ($createPickupPoints as $pickupPoint)
												@php
													$pickupTitle = trim((string)$pickupPoint->title);
													$countryCodes = collect(explode(',', (string)($pickupPoint->country_code ?? 'ALL')))
														->map(function ($code) { return strtoupper(trim($code)); })
														->filter(function ($code) { return $code !== ''; })
														->values();
													if ($countryCodes->isEmpty()) {
														$countryCodes = collect(['ALL']);
													}
												@endphp
												<option value="{{ $pickupPoint->id }}" data-country="{{ $countryCodes->implode(',') }}">
													{{ $pickupTitle }}
												</option>
											@endforeach
										</select>
									</div>

									<div class="form-group" id="delivery_town_container" style="display: none;">
										<p>Город (доставка по городу):</p>
										<select class="form-control" name="delivery_town_id" id="delivery_town_id">
											<option value="">-- выберите город --</option>
											@foreach ($createDeliveryTowns as $town)
												@php
													$townName = trim((string)$town->city);
													$townCountry = strtoupper(trim((string)($town->country ?? '')));
												@endphp
												<option value="{{ $town->id }}" data-country="{{ $townCountry }}">{{ $townName }}</option>
											@endforeach
										</select>
									</div>

									<div class="form-group" id="city_container">
										<p>Город:</p>
										<input class="form-control" type="text" name="city"
											value="@if (isset($user['city'])) {{ $user['city'] }} @endif">
									</div>

									<div class="form-group">
										<p>Адресс:</p>
										<input class="form-control" type="text" name="address"
											value="@if (isset($user['address']) && $user['address']) {{ $user['address'] }} @endif">
									</div>

									<div class="form-group">
										<p>Индекс:</p>
										<input class="form-control" type="text" name="postal_index"
											value="@if (isset($user['postal_index']) && $user['postal_index']) {{ $user['postal_index'] }} @endif">
									</div>

									<div class="form-group">
										<p>Когда получить:</p>
										<input class="form-control" type="date" name="when_send" value="{{ old('when_send') }}">
									</div>

									<div class="form-group">
										<p>Цена за доставку:</p>
										<input class="form-control" type="text" name="deliv_price" value="{{ old('deliv_price', '0') }}">
									</div>
								</div>

								<div class="admin-section-block">
									<h3 class="admin-section-title">Данные заказа</h3>

									<div class="form-bonuses">
										<p>Доступно бонусов</p>
										<input class="form-control" id="bonus_value" type="text" disabled value="0">

										<p>Списать бонусов в этом заказе</p>
										<input class="form-control" type="number" name="bonus" value="{{ old('bonus', 0) }}" min="0" max="0">
									</div>

									<div class="form-group">
										<p>Скидка в евро:</p>
										<input type="number" class="form-control" name="sale_eur" value="{{ old('sale_eur', 0) }}">
									</div>

									<div class="form-group">
										<p>Скидка в %:</p>
										<input type="number" class="form-control" name="sale_percent" value="{{ old('sale_percent', 0) }}">
									</div>

									<div class="form-group">
										<p>Итого по товарам:</p>
										<input class="form-control" type="text" id="order_total_items_display" value="0.00" readonly>
									</div>

									<div class="form-group">
										<p>Итого к оплате (с учетом скидки и доставки):</p>
										<input class="form-control" type="text" id="order_total_final_display" value="0.00" readonly>
									</div>

									<div class="form-group" style="display:none;">
										<p>Оригинальная цена:</p>
										<input class="form-control" type="text" name="price" value="{{ old('price') }}">
									</div>

									<div class="form-group" style="display:none;">
										<p>Цена со скидкой:</p>
										<input class="form-control" type="text" name="sale_price" value="{{ old('sale_price') }}">
									</div>
								</div>

							</div>
						</div>
						<!-- Новый блок: Юридическая информация -->
						<div class="panel panel-bordered">
							<div class="panel-heading">
								<h3 class="panel-title">Юридическая информация</h3>
							</div>
							<div class="panel-body" style="padding-top: 10px;">
								<!-- Чекбокс "Я юр. лицо" -->
								<div class="form-group">
									<div class="checkbox">
										<label>
											<input type="checkbox" name="ur_name" class="js_ur" {{ old('ur_name') == 'on' ? 'checked' : '' }}>
											Юр. лицо (Включать для вывода в счете)
										</label>
									</div>
								</div>
								<!-- Имя юридического лица -->
								<div class="form-group">
									<p>Имя Юр. лица:</p>
									<input type="text" class="form-control" name="ur_name_l" placeholder="Имя Юр. лица"
										value="{{ old('ur_name_l') }}">
								</div>
								<!-- Регистрационный номер -->
								<div class="form-group">
									<p>Рег. номер:</p>
									<input type="text" class="form-control" name="ur_reg_num" placeholder="Рег. номер"
										value="{{ old('ur_reg_num') }}">
								</div>
								<!-- Адрес (2 строки) -->
								<div class="form-group">
									<p>Адрес:</p>
									<textarea class="form-control" name="ur_legal_addr" placeholder="Адрес" rows="2">{{ old('ur_legal_addr') }}</textarea>
								</div>
								<!-- VAT NUMBER -->
								<div class="form-group">
									<p>VAT NUMBER:</p>
									<input type="text" class="form-control" name="ur_pnr_nr" placeholder="VAT NUMBER"
										value="{{ old('ur_pnr_nr') }}">
								</div>
								<!-- Имя банка -->
								<div class="form-group">
									<p>Имя банка:</p>
									<input type="text" class="form-control" name="ur_bank_name" placeholder="Имя банка"
										value="{{ old('ur_bank_name') }}">
								</div>
								<!-- Код банка -->
								<div class="form-group">
									<p>Код банка:</p>
									<input type="text" class="form-control" name="ur_bank_code" placeholder="Код банка"
										value="{{ old('ur_bank_code') }}">
								</div>
								<!-- Номер счета банка -->
								<div class="form-group">
									<p>Номер счета банка:</p>
									<input type="text" class="form-control" name="ur_bank_acc_code" placeholder="Номер счета банка"
										value="{{ old('ur_bank_acc_code') }}">
								</div>
							</div>
						</div>

						<button type="submit" class="btn-primary btn primary">Добавить</button>


					</div>

					<div class="col-md-6">
						<div class="panel panel-bordered">
							<div class="panel-body">
								<div class="form-group">
									<h3 class="admin-section-title">Товары в заказе</h3>

									<div class="js__tabs__items">
										<div class="js_form_tab">
											<input class="form-control" type="hidden" name="basket_item[]" value="">
											<h2>Товар</h2>
											<div class="form-group">
												<p>Название:</p>
												<input class="form-control" type="text" name="basket_name[]" value="">
											</div>

											<div class="form-group">
												<p>Цена:</p>
												<input class="form-control" type="number" name="basket_price[]" value="">
											</div>

											<div class="form-group">
												<p>Размер:</p>
												<input class="form-control" type="text" name="basket_size[]" value="">
											</div>

											<div class="form-group">
												<p>Добавить фото к этой позиции:</p>
												<input class="form-control" type="file" multiple name="basket_images_0[]" value=""
													accept="image/*,image/heif,image/heic">
											</div>

											<div class="form-group">
												<p>Изготовление (к-во дней):</p>
												<input class="form-control" type="text" name="basket_terms[]" value="">
											</div>

											<div class="form-group">
												<p>Опции для названия файла:</p><br>
												<label>Вид холста:</label>
												<select class="form-control" name="basket_manual_canvas_id[0]">
													<option value="1">Эконом (S)</option>
													<option value="2" selected>Интерьерный (S)</option>
													<option value="3">Синтетический (S)</option>
													<option value="4">Хлопковый (C)</option>
													<option value="5">Глянцевый (G)</option>
												</select><br>
												<label>Подарочная упаковка:</label>
												<select class="form-control" name="basket_manual_gift_code[0]">
													<option value="G0" selected>Обычная упаковка (G0)</option>
													<option value="G1">Подарочная бумага (G1)</option>
													<option value="G2">Эксклюзивная упаковка (G2)</option>
												</select><br>
												<label>Лак / мазки:</label>
												<select class="form-control" name="basket_manual_decoration_id[0]">
													<option value="5" selected>Стандарт. Без дополнений (L0, P0)</option>
													<option value="1">Арт-гель (L2, P0)</option>
													<option value="2">Художественные мазки (L0, P1)</option>
													<option value="3">Даммарный лак (L1, P0)</option>
												</select><br>
												<label>Ориентация:</label>
												<select class="form-control" name="basket_manual_orientation_code[0]">
													<option value="V0" selected>Авто по размеру (V0)</option>
													<option value="V1">Вертикальная (V1)</option>
													<option value="V2">Горизонтальная (V2)</option>
													<option value="V3">Квадрат (V3)</option>
													<option value="V4">Панорама (V4)</option>
												</select><br>
											<label>Оформление:</label>
											<select class="form-control" name="basket_manual_baget_code[0]">
												<option value="B0" selected>Без рамки (B0)</option>
												<option value="B1">Рамка (B1)</option>
												<option value="B2">На бумаге, в рамке (B2)</option>
											</select><br>
												<label><input type="checkbox" name="basket_manual_express[0]" value="1"> Экспресс</label>
											</div>

											<div class="form-group">
												<p>Комментарий пользователя:</p>
												<textarea class="form-control" name="basket_comment[]"></textarea>
											</div>

										</div>
									</div>
									<button class="js_add_item btn btn-success btn-add-new"><i class="voyager-plus"></i></button>



								</div>

							</div>
						</div>
					</div>

				</div>
			</form>
		</div>
	</div>

	<iframe id="form_target" name="form_target" style="display:none"></iframe>
	<form id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="post"
		enctype="multipart/form-data" style="width:0px;height:0;overflow:hidden">
		{{ csrf_field() }}
		<input name="image" id="upload_file" type="file" onchange="$('#my_form').submit();this.value='';">
	</form>
	</div>
@stop

@section('javascript')
	<script>
		function choose_user(email) {
			$("#emailInput").val(email);
			$("#finduser").html("");
			show_bonuses(function(result) {
				console.log(result);
			});




		}

		function show_bonuses(callback) {
			emailValue = $("#emailInput").val();
			$.ajax({
				url: '/admin/check-user',
				type: 'POST',
				data: {
					email: emailValue,
					form: 2
				},
				dataType: 'json',
				success: function(data) {
					console.log(data);
					if (data.length === 1) {
						if (data[0].bonuses > 0) {
							$("#bonus_value").val(data[0].bonuses);
							$('[name="bonus"]').attr('max', data[0].bonuses);
							$(".form-bonuses").css('display', 'block');
						} else {
							$("#bonus_value").val(0);
							$('[name="bonus"]').attr('max', 0);
						}
						$("#finduser").html("");

						$('[name="first_name"]').val(data[0].first_name);
						$('[name="last_name"]').val(data[0].last_name);
						$('[name="phone"]').val(data[0].phone);
						$('[name="phone_rec"]').val(data[0].phone);





					} else {
						$(".form-bonuses").css('display', 'none');
					}

					// Pass the result to the callback function
					if (typeof callback === 'function') {
						callback(data[0]);
					}
				},
				error: function(error) {
					console.error('Error:', error);
				}
			});
		}



		$('document').ready(function() {
			$(document).on('click', '.js_rem_item', function(e) {
				$(this).closest('.js_form_tab').remove();
				syncOrderTotalsFromItems();
			});

			function toNumber(value) {
				if (value === null || value === undefined) {
					return 0;
				}
				var normalized = value.toString().replace(',', '.').trim();
				var n = parseFloat(normalized);
				return isNaN(n) ? 0 : n;
			}

			function syncOrderTotalsFromItems() {
				var sum = 0;
				$('input[name="basket_price[]"]').each(function() {
					sum += toNumber($(this).val());
				});
				sum = Math.round(sum * 100) / 100;

				var bonus = toNumber($('input[name="bonus"]').val());
				var subtotal = Math.max(0, Math.round((sum - bonus) * 100) / 100);

				var sumText = sum.toFixed(2);
				$('#order_total_items_display').val(sumText);

				// Base totals stored in DB; discounts/delivery are stored separately.
				$('input[name="price"]').val(subtotal);
				$('input[name="sale_price"]').val(subtotal);

				syncFinalTotalDisplay(sum, bonus, subtotal);
			}

			$(document).on('input', 'input[name="basket_price[]"]', syncOrderTotalsFromItems);

			function syncFinalTotalDisplay(itemsSum, bonus, subtotal) {
				var saleEur = toNumber($('input[name="sale_eur"]').val());
				var salePercent = toNumber($('input[name="sale_percent"]').val());
				var delivPrice = toNumber($('input[name="deliv_price"]').val());

				// Same order as PDF: subtract EUR first, then percent from current sale_price.
				var discounted = subtotal;
				if (saleEur > 0) {
					discounted = discounted - saleEur;
				}
				if (salePercent > 0) {
					discounted = discounted - (discounted * (salePercent / 100));
				}
				discounted = Math.max(0, Math.round(discounted * 100) / 100);

				var finalTotal = discounted + Math.max(0, delivPrice);
				finalTotal = Math.round(finalTotal * 100) / 100;

				$('#order_total_final_display').val(finalTotal.toFixed(2));
			}

			$(document).on('input', 'input[name="sale_eur"]', syncOrderTotalsFromItems);
			$(document).on('input', 'input[name="sale_percent"]', syncOrderTotalsFromItems);
			$(document).on('input', 'input[name="deliv_price"]', syncOrderTotalsFromItems);
			$(document).on('input', 'input[name="bonus"]', syncOrderTotalsFromItems);

			// Discounts are mutually exclusive: when EUR discount is set, clear % and vice versa.
			var isDiscountSyncing = false;
			$(document).on('input', 'input[name="sale_eur"]', function() {
				if (isDiscountSyncing) {
					return;
				}
				var eur = toNumber($(this).val());
				if (eur > 0) {
					isDiscountSyncing = true;
					$('input[name="sale_percent"]').val(0);
					isDiscountSyncing = false;
				}
				syncOrderTotalsFromItems();
			});
			$(document).on('input', 'input[name="sale_percent"]', function() {
				if (isDiscountSyncing) {
					return;
				}
				var percent = toNumber($(this).val());
				if (percent > 0) {
					isDiscountSyncing = true;
					$('input[name="sale_eur"]').val(0);
					isDiscountSyncing = false;
				}
				syncOrderTotalsFromItems();
			});






			var cur_item = 0;
			$(document).on('click', '.js_add_item', function(e) {
				e.preventDefault();
				cur_item++;

				$('.js__tabs__items').append(`
                        <div class="js_form_tab">
                            <button class="js_rem_item btn btn-danger btn-rem-new">—</button>
                            <input class="form-control" type="hidden" name="basket_item[]" value="">
                            <h2>Товар</h2>
                            <div class="form-group">
                                <p>Название:</p>
                                <input class="form-control" type="text" name="basket_name[]" value="">
                            </div>

                            <div class="form-group">
                                <p>Цена:</p>
                                <input class="form-control" type="number" name="basket_price[]" value="">
                            </div>

                            <div class="form-group">
                                <p>Размер:</p>
                                <input class="form-control" type="text" name="basket_size[]" value="">
                            </div>


                            <div class="form-group">
                                <p>Добавить фото к этой позиции:</p>
                                <input class="form-control" type="file" multiple name="basket_images_` + cur_item + `[]" value="" accept="image/*,image/heif,image/heic">
                            </div>

                            <div class="form-group">
                                <p>Изготовление (к-во дней):</p>
                                <input class="form-control" type="text" name="basket_terms[]" value="">
                            </div>

                            <div class="form-group">
                                <p>Опции для названия файла:</p><br>
                                <label>Вид холста:</label>
                                <select class="form-control" name="basket_manual_canvas_id[` + cur_item + `]">
                                    <option value="1">Эконом (S)</option>
                                    <option value="2" selected>Интерьерный (S)</option>
                                    <option value="3">Синтетический (S)</option>
                                    <option value="4">Хлопковый (C)</option>
                                    <option value="5">Глянцевый (G)</option>
                                </select><br>
                                <label>Подарочная упаковка:</label>
                                <select class="form-control" name="basket_manual_gift_code[` + cur_item + `]">
                                    <option value="G0" selected>Обычная упаковка (G0)</option>
                                    <option value="G1">Подарочная бумага (G1)</option>
                                    <option value="G2">Эксклюзивная упаковка (G2)</option>
                                </select><br>
                                <label>Лак / мазки:</label>
                                <select class="form-control" name="basket_manual_decoration_id[` + cur_item + `]">
                                    <option value="5" selected>Стандарт. Без дополнений (L0, P0)</option>
                                    <option value="1">Арт-гель (L2, P0)</option>
                                    <option value="2">Художественные мазки (L0, P1)</option>
                                    <option value="3">Даммарный лак (L1, P0)</option>
                                </select><br>
                                <label>Ориентация:</label>
                                <select class="form-control" name="basket_manual_orientation_code[` + cur_item + `]">
                                    <option value="V0" selected>Авто по размеру (V0)</option>
                                    <option value="V1">Вертикальная (V1)</option>
                                    <option value="V2">Горизонтальная (V2)</option>
                                    <option value="V3">Квадрат (V3)</option>
                                    <option value="V4">Панорама (V4)</option>
                                </select><br>
                                <label>Оформление:</label>
                                <select class="form-control" name="basket_manual_baget_code[` + cur_item + `]">
                                    <option value="B0" selected>Без рамки (B0)</option>
                                    <option value="B1">Рамка (B1)</option>
                                    <option value="B2">На бумаге, в рамке (B2)</option>
                                </select><br>
                                <label><input type="checkbox" name="basket_manual_express[` + cur_item + `]" value="1"> Экспресс</label>
                            </div>

                            <div class="form-group">
                                <p>Комментарий к позиции:</p>
                                <textarea class="form-control" name="basket_comment[]"></textarea>
                            </div>

                            </div>
                        `);

				syncOrderTotalsFromItems();
			});

			function filterPickupOptionsByCountry() {
				var selectedCountry = ($('select[name="country"]').val() || '').toUpperCase();
				var $options = $('#pickup_workshop_id option');
				$options.each(function() {
					if (!this.value) {
						this.hidden = false;
						return;
					}
					var raw = ($(this).data('country') || 'ALL').toString().toUpperCase();
					var allowed = raw.split(',').map(function(code) {
						return code.trim();
					}).filter(function(code) {
						return code.length > 0;
					});
					if (!allowed.length) {
						allowed.push('ALL');
					}
					this.hidden = !!selectedCountry && allowed.indexOf('ALL') === -1 && allowed.indexOf(selectedCountry) === -1;
				});

				var $selected = $('#pickup_workshop_id option:selected');
				if ($selected.length && $selected[0].hidden) {
					$('#pickup_workshop_id').val('');
				}
				if (!$('#pickup_workshop_id').val()) {
					var firstVisible = $('#pickup_workshop_id option').filter(function() {
						return this.value && !this.hidden;
					}).first().val();
					if (firstVisible) {
						$('#pickup_workshop_id').val(firstVisible);
					}
				}
			}

			function filterTownOptionsByCountry() {
				var selectedCountry = ($('select[name="country"]').val() || '').toUpperCase();
				var $options = $('#delivery_town_id option');
				$options.each(function() {
					if (!this.value) {
						this.hidden = false;
						return;
					}
					var townCountry = ($(this).data('country') || '').toString().toUpperCase();
					this.hidden = !!selectedCountry && !!townCountry && townCountry !== selectedCountry;
				});

				var $selected = $('#delivery_town_id option:selected');
				if ($selected.length && $selected[0].hidden) {
					$('#delivery_town_id').val('');
				}
				if (!$('#delivery_town_id').val()) {
					var firstVisible = $('#delivery_town_id option').filter(function() {
						return this.value && !this.hidden;
					}).first().val();
					if (firstVisible) {
						$('#delivery_town_id').val(firstVisible);
					}
				}
			}

			function syncDeliveryCreateFields() {
				var method = $('#sposob').val() || '';
				var showPickup = method === 'pickup_at_viar_workshop' || method === 'pickup_Riga' || method === 'pickup_Daugavplis' || method === 'pickup_Daugavpils';
				var showTown = method === 'city_delivery';
				var hideCityForWorkshop = method === 'pickup_at_viar_workshop' || method === 'pickup_Riga' || method === 'pickup_Daugavplis' || method === 'pickup_Daugavpils';

				$('#pickup_workshop_container').toggle(showPickup);
				$('#delivery_town_container').toggle(showTown);
				$('#city_container').toggle(!hideCityForWorkshop);

				if (showPickup) {
					filterPickupOptionsByCountry();
					if (method === 'pickup_Riga') {
						$('#pickup_workshop_id').val('1');
					} else if (method === 'pickup_Daugavplis' || method === 'pickup_Daugavpils') {
						$('#pickup_workshop_id').val('2');
					}

					var pickupText = $('#pickup_workshop_id option:selected').text().trim();
					if (pickupText) {
						$('input[name="address"]').val(pickupText);
						if (hideCityForWorkshop) {
							$('input[name="city"]').val('');
						} else {
							$('input[name="city"]').val(pickupText);
						}
					}
				} else {
					$('#pickup_workshop_id').val('');
				}

				if (showTown) {
					filterTownOptionsByCountry();
					var townText = $('#delivery_town_id option:selected').text().trim();
					if (townText) {
						$('input[name="city"]').val(townText);
					}
				} else {
					$('#delivery_town_id').val('');
				}
			}

			$('#sposob').on('change', syncDeliveryCreateFields);
			$('select[name="country"]').on('change', syncDeliveryCreateFields);
			$('#pickup_workshop_id').on('change', function() {
				var method = $('#sposob').val() || '';
				var pickupText = $('#pickup_workshop_id option:selected').text().trim();
				if (pickupText) {
					$('input[name="address"]').val(pickupText);
					if (method === 'pickup_at_viar_workshop') {
						$('input[name="city"]').val('');
					} else {
						$('input[name="city"]').val(pickupText);
					}
				}
			});
			$('#delivery_town_id').on('change', function() {
				var townText = $('#delivery_town_id option:selected').text().trim();
				if (townText) {
					$('input[name="city"]').val(townText);
				}
			});

			function applyPaymentStatusColor() {
				var $statusSelect = $('select[name="payment_status"]');
				if (!$statusSelect.length) {
					return;
				}

				$statusSelect.removeClass('status-not-payed status-payed status-prepayment');
				var statusValue = $statusSelect.val();

				if (statusValue === 'not_payed') {
					$statusSelect.addClass('status-not-payed');
				} else if (statusValue === 'payed') {
					$statusSelect.addClass('status-payed');
				} else if (statusValue === 'prepayment') {
					$statusSelect.addClass('status-prepayment');
				}
			}

			$('select[name="payment_status"]').on('change', applyPaymentStatusColor);

			applyPaymentStatusColor();
			syncDeliveryCreateFields();

			// Enforce exclusivity on initial load too (in case of old() values).
			if (toNumber($('input[name="sale_eur"]').val()) > 0) {
				$('input[name="sale_percent"]').val(0);
			} else if (toNumber($('input[name="sale_percent"]').val()) > 0) {
				$('input[name="sale_eur"]').val(0);
			}

			syncOrderTotalsFromItems();


			$('#emailInput').on('input', function() {

				// Get the current value of the email input
				var emailValue = $(this).val();

				// Check if there are at least 3 characters entered in the email input
				if (emailValue.length >= 3) {

					$.ajax({
						url: '/admin/check-user',
						type: 'POST',
						data: {
							email: emailValue,
							form: 1
						},
						dataType: 'json',
						success: function(data) {
							userlinks = '';
								data.forEach(function(item) {

								/*
								                                userlinks=userlinks+'<a style="cursor: pointer" onclick="choose_user("'+item.email+'") >'+item.email+' '+item.first_name+' '+item.last_name+'</a><br>';
								*/
								userlinks = userlinks +
									'<a style="cursor: pointer" onclick="choose_user(\'' +
									item.email + '\')">' + item.email + ' ' + item
									.first_name + ' ' + item.last_name + '</a><br>';

							});
							$("#finduser").html(userlinks);

									$('[name="first_name"]').val("");
									$('[name="last_name"]').val("");
									$('[name="phone"]').val("");
									$('[name="phone_rec"]').val("");

								},
						error: function(error) {
							console.error('Error:', error);
						}
					});
				}
			});




			$('#emailInput').blur(function() {
				show_bonuses(function(result) {
					console.log(result);
				});
			});


		});
	</script>
@stop
