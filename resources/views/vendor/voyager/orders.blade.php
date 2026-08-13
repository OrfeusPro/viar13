@extends('voyager::master') @section('content')

	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

	<div class="container-fluid">
		<h1 class="page-title"><i class="voyager-basket"></i> Заказы</h1>
		@if($role != "printing")
			<a class="btn btn-success btn-add-new btn__new__order" href="{{ route('create_admin_order') }}"><i
					class="voyager-plus"></i><span>Новый заказ</span></a>
			<div style="display: table;">
				<a href="#" class="curier-call-btn"><img src="{{ asset('images/venipak.png') }}" alt="" /><span
						class="curier">ВЫЗОВ
						КУРЬЕРА</span></a>
			</div>
		@endif
		@include('vendor.voyager.partials.orders.styles')
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
				@if (isset($_GET['user_id']))
					<form action="" id="zak_filter_select" style="float: right;clear: both;">
						<input style="display:none;" type="text" name="user_id" value="{{ $_GET['user_id'] }}">
						<label>
							<span style="margin-right:5px;">Статусы оплаты</span>
							<select name="filter_by_zak_type" id="zak_fitler" style="float:right">
								<option @if (isset($_GET['filter_by_zak_type']) && $_GET['filter_by_zak_type'] == 'all') selected @endif value="all">Все
								</option>
								<option @if (isset($_GET['filter_by_zak_type']) && $_GET['filter_by_zak_type'] == 'payed') selected @endif value="payed">Оплачен
								</option>
								<option @if (isset($_GET['filter_by_zak_type']) && $_GET['filter_by_zak_type'] == 'not_payed') selected @endif value="not_payed">Не оплачен
								</option>
								<option @if (isset($_GET['filter_by_zak_type']) && $_GET['filter_by_zak_type'] == 'prepayment') selected @endif value="not_payed">Предоплата
								</option>
							</select>
						</label>
					</form>
				@else
					<div class="panel panel-default">
						<div class="panel-body">
							<p>Список заказов:</p>
							<div class="order__list__routes">
								@if($role != "printing")
								<a href="/admin/orders" class="btn btn-sm btn-warning">Текущие заказы</a>
								<a href="/admin/orders?completed=1" class="btn btn-sm btn-primary">Готовые заказы</a>
								<a href="/admin/orders?express=1" class="btn btn-sm btn-danger">Надо отправить сегодня</a>
								@endif
								<a href="/admin/orders?in_production=1" class="btn btn-sm btn-warning">В производстве</a>
								<a href="/admin/orders?printing=1" class="btn btn-sm btn-success" style="background: #b1db5b; color: black;">Менеджер печати</a>
								<a href="/admin/orders?sent_today=1" class="btn btn-sm btn-success" style="color: black; background: gainsboro;">Отправили сегодня</a>
								<a href="/admin/orders?status=sended&amp;order_id_sort=desc" class="btn btn-sm btn-success" style="color: black; background: #d9edf7;">Отправлены</a>
								<a href="/admin/orders?new_orders=1&amp;order_id_sort=desc" class="btn btn-sm btn-success">Новые заказы (сегодня/вчера)</a>
								@if($role != "printing")
									<a href="/admin/orders?not_payed=1" class="btn btn-sm btn-danger">Не оплачено: {{ $unpaidOrdersCount ?? 0 }}</a>
								@endif
								@if($role == "printing")
									<a href="/admin/orders?express=1" class="btn btn-sm btn-success" style="color: white; background: cornflowerblue;">Все текущие заказы</a>
								@endif
							</div>
						</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-filter"></i> Фильтр</h3>
						</div>

						<div class="panel-body">
							<form class="w-100">
								<div class="row w-100" style="padding: 15px;">
									<div class="col-6 col-md-2" style="margin-bottom:0px;">
										@if($role != "printing" && $role != "lite_manager") Заказов: <b>{{ $orders->total() }}</b> @endif
									</div>
									@if($role != "printing" && $role != "lite_manager")
									<div class="col-6 col-md-2" style="margin-bottom:0px;">
										@if($role != "printing") Сумма: <b>{{ $summ }} €</b> @endif
									</div>
									@endif
								</div>
								<input type="hidden" name="order_filter" value="1">
								<div class="form-group col-md-1">
									<label class="control-label" for="input-order-id">№ Заказа</label>
									<input type="text" placeholder="№ Заказа" name="order_id"
										@if (isset($_GET['order_id'])) value="{{ intval($_GET['order_id']) }}" @endif class="form-control">
								</div>
								<div class="form-group col-md-2">
									<label class="control-label" for="input-payment-request-number">Заявка на оплату</label>
									<input
										type="text"
										name="filter_payment_request_number"
										id="input-payment-request-number"
										placeholder="OPR-000001"
										@if (isset($_GET['filter_payment_request_number'])) value="{{ $_GET['filter_payment_request_number'] }}" @endif
										class="form-control">
								</div>
								<div class="form-group col-md-2">
									<label class="control-label" for="input-customer">Клиент</label>
									<input type="text" name="filter_customer"
										@if (isset($_GET['filter_customer'])) value="{{ $_GET['filter_customer'] }}" @endif
										placeholder="id, email, имя, фамилия, тел. пользователя, номер накладной, страна, почтовый индекс"
										id="input-customer" class="form-control" autocomplete="off">
								</div>
								<div class="form-group col-md-1">
									<label class="control-label" for="input-phone">Телефон</label>
									<input class="form-control" type="text" name="filter_phone" id="input-phone" placeholder="Телефон"
										value="@if (isset($_GET['filter_phone'])) {{ $_GET['filter_phone'] }} @endif">
								</div>
								<div class="form-group col-md-1">
									<label class="control-label" for="input-phone">Цена</label>
									<input class="form-control" type="text" name="filter_price" placeholder="Цена"
										value="@if (isset($_GET['filter_price'])) {{ $_GET['filter_price'] }} @endif">
								</div>
								<div class="form-group col-md-1">
									<label class="control-label" for="input-order-status">Статус заказа</label>
									<select name="filter_order_status_id" id="input-order-status" class="form-control">
										<option value=""></option>
										<option value="payed" @if (isset($_GET['filter_order_status_id']) and $_GET['filter_order_status_id'] == 'payed') selected @endif>
											Оплачен
										</option>
										<option value="not_payed" @if (isset($_GET['filter_order_status_id']) and $_GET['filter_order_status_id'] == 'not_payed') selected @endif>
											Не оплачен
										</option>
										<option value="prepayment" @if (isset($_GET['filter_order_status_id']) and $_GET['filter_order_status_id'] == 'prepayment') selected @endif>
											Предоплата
										</option>
									</select>
								</div>
								<div class="form-group col-md-1">
									<label class="control-label" for="input-order-status">Канал продаж</label>
									<select name="filter_a_order_from" id="a_order_from" class="form-control">
										<option value=""></option>
										@foreach ($order_from as $from)
											<option value="{{ $from['id'] }}" @if (isset($_GET['filter_a_order_from']) and $_GET['filter_a_order_from'] == $from['id']) selected @endif>
												{{ $from['title'] }}
											</option>
										@endforeach
									</select>
								</div>
								<div class="form-group col-md-1">
									<label class="control-label" for="input-order-status">Категория</label>
									<select name="filter_catid" id="catid" class="form-control">
										<option value=""></option>
										@foreach ($style as $catid)
											<option value="{{ $catid['id'] }}" @if (isset($_GET['filter_catid']) and $_GET['filter_catid'] == $catid['id']) selected @endif>
												{{ str_replace('</span>', '', str_replace('<span>', '', $catid->getTranslatedAttribute('name'))) }}
											</option>
										@endforeach
									</select>
								</div>
								<div class="form-group col-md-1">
									<label class="control-label" for="input-order-status">Страна:</label>
									<select name="filter_country" id="country" class="form-control">
										<option value=""></option>
										@foreach ($c_tels as $count)
											<option value="{{ $count['country_code'] }}" @if (isset($_GET['filter_country']) and $_GET['filter_country'] == $count['country_code']) selected @endif>
												{{ $count['country_name'] }}
											</option>
										@endforeach
									</select>
								</div>
								<div class="form-group col-md-1">
									<label class="control-label" for="input-order-status">Художник:</label>
									<select name="filter_painter_choose" id="filter_painter_choose" class="form-control">
										<option value=""></option>
										@if ($painters)
											@foreach ($painters as $painter)
												<option value="{{ $painter->id }}" @if (isset($_GET['filter_painter_choose']) and $_GET['filter_painter_choose'] == $painter->id) selected @endif>
													{{ $painter->nick }}
													- {{ $painter->email }}</option>
											@endforeach
										@endif
									</select>
								</div>
								<div class="form-group col-md-2">
									<label class="control-label" for="input-order-status">Заказы менеджеров:</label>
									<select name="filter_managers" id="filter_managers" class="form-control">
										<option value=""></option>
										@if ($managers)
											@foreach ($managers as $manager)
												<option value="{{ $manager->id }}" @if (isset($_GET['filter_managers']) and $_GET['filter_managers'] == $manager->id) selected @endif>
													@if ($manager->nick)
														{{ $manager->nick }}
														-
													@endif{{ $manager->email }}
												</option>
											@endforeach
										@endif
										<option value="all" @if (isset($_GET['filter_managers']) and $_GET['filter_managers'] == 'all') selected @endif>Админ</option>
									</select>
								</div>
								<div class="form-group col-md-1">
									<label class="control-label" for="input-phone">Размер</label>
									<input class="form-control" type="text" name="sizeId" id="input-phone" placeholder="30x40"
										value="@if (isset($_GET['sizeId'])){{ $_GET['sizeId'] }}@endif">
								</div>
								<div class="form-group col-md-2">
									<label class="control-label" for="input-order-status">Период:</label>
									<div style="display: flex;">
										<input type="datetime" class="form-control datepicker" name="filter_start_date"
											value="@if (isset($_GET['filter_start_date']) and $_GET['filter_start_date']) {{ $filter_start_date }} @endif"
											style="max-width:100px; margin-right: 5px;">
										<span style="padding-top: 4px;"> - </span>
										<input type="datetime" class="form-control datepicker" name="filter_end_date"
											value="@if (isset($_GET['filter_end_date']) and $_GET['filter_end_date']) {{ $filter_end_date }} @endif"
											style="max-width:100px; margin-left: 5px;" style="max-width:100px; margin-left: 5px;">
									</div>
								</div>
								<div class="form-group col-md-2">
									<button style="margin-top:25px;" type="submit" id="button-filter" class="btn btn-default"><i
											class="fa fa-filter"></i> Фильтр
									</button>
								</div>
							</form>

						</div>
					</div>
				@endif
			</div>
			<div class="col-md-12">
				<div class="panel panel-bordered">
					<div class="panel-body">
						<div class="table-responsive">
							<div id="dataTable_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
								<div class="row">
									<div class="col-sm-12">
										<table id="dataTable" class="table table-hover dataTable no-footer sortable" role="grid"
											aria-describedby="dataTable_info">
											<thead>
												<tr role="row">
													@foreach ($order_columns as $column)
														<td data-index="{{ $loop->index }}" class="sorting_disabled" tabindex="0" rowspan="1"
															colspan="1">
															@if ($loop->index === 0)
																@php
																	$currentOrderIdSort = $orderIdSort ?? request()->get('order_id_sort');
																	$orderIdSortQuery = request()->query();
																	unset($orderIdSortQuery['page']);
																	unset($orderIdSortQuery['_url']);
																	if ($currentOrderIdSort === 'desc') {
																	    $orderIdSortQuery['order_id_sort'] = 'asc';
																	    $orderIdSortIcon = '↓';
																	    $orderIdSortTitle = 'Сортировка по номеру: новые сверху';
																	} elseif ($currentOrderIdSort === 'asc') {
																	    unset($orderIdSortQuery['order_id_sort']);
																	    $orderIdSortIcon = '↑';
																	    $orderIdSortTitle = 'Сортировка по номеру: старые сверху';
																	} else {
																	    $orderIdSortQuery['order_id_sort'] = 'desc';
																	    $orderIdSortIcon = '⇅';
																	    $orderIdSortTitle = 'Сортировать по номеру заказа';
																	}
																	$orderIdSortUrl = url()->current() . (count($orderIdSortQuery) ? '?' . http_build_query($orderIdSortQuery) : '');
																@endphp
																<div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
																	<span>Номер</span>
																	<a href="{{ $orderIdSortUrl }}" title="{{ $orderIdSortTitle }}" style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; color: #8795a1; text-decoration: none;">
																		<span aria-hidden="true" style="display: inline-block; font-family: Arial, sans-serif; font-size: 18px; font-weight: 700; line-height: 1;">{{ $orderIdSortIcon }}</span>
																	</a>
																</div>
															@elseif($loop->index === 1)
																<span class="tac">Оплата</span>
															@elseif($loop->index === 2)
																<span class="tac">Пользователь</span>
															@elseif($loop->index === 3)
																<span class="tac">Получатель</span>
															@elseif($loop->index === 4)
																<span class="tac">Товар</span>
															@elseif($loop->index === 5)
																<span class="tac">Комментарии</span>
															@else
																{{ $column }}
															@endif
														</td>
													@endforeach
													<td class="sorting_disabled" rowspan="1" colspan="1" aria-label="Доступные действия"><span
															class="tac">Художник</span>
													</td>
													<td class="sorting_disabled" rowspan="1" colspan="1" aria-label="Доступные действия"><span
															class="tac">Заказ</span></td>
												</tr>
											</thead>
											<div>
												@foreach ($orders as $order)
													@php
														if (!isset($_GET['order_filter'])) {
														    $order = (array) $order;
														}

														if ($order['vrv_1'] != null) {
														    $order_vr_id = 'VR00' . $order['vrv_1'];
														} elseif ($order['vrv_2'] != null) {
														    $order_vr_id = 'BAW' . $order['vrv_2'];
														} elseif ($order['vrv_3'] != null) {
														    $vrv_3 = $order['vrv_3'];
														    if ($vrv_3 < 10) {
														        $vrv_3_mod = '00' . $vrv_3;
														    } elseif ($vrv_3 < 100 && $vrv_3 > 9) {
														        $vrv_3_mod = '0' . $vrv_3;
														    } else {
														        $vrv_3_mod = $vrv_3;
														    }
														    $order_vr_id = 'VRR445' . $vrv_3_mod;
														} elseif ($order['vrv_4'] != null) {
														    $order_vr_id = 'DS020' . $order['vrv_4'];
														} else {
														    $order_vr_id = null;
														}

														$order['items'] = json_decode($order['items'], true);
														$order['delivery'] = json_decode($order['delivery'], true);
														// $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';
														$statusName = ['payed' => 'оплачено', 'not_payed' => 'не оплачено', 'prepayment' => 'предоплата'];
													@endphp

													@php
														$user_status_id = App\Models\User::getUserStatusById($order['user_id']);
													@endphp

													@php
														$end_date = $order['painter_endtime'];
														$d_diff = null;
														$is_important_painter = 0;
														if ($end_date != null) {
														    $now = time();
														    $end_date_time = strtotime($end_date);
														    $d_diff = ceil(($now - $end_date_time) / 86400);
														    if ($d_diff == -0 || $d_diff == 1) {
														        $is_important_painter = 1;
														    } else {
														        $is_important_painter = 0;
														    }
														}
													@endphp
													<tr style="text-align: center;" role="row" class="odd odd-id-{{ $order['id'] }}"
														id="order__{{ $order['id'] }}">

														@include('voyager::orders.id')
                                                        
                                                        @if(isset($order['sa_escalations']) && count($order['sa_escalations']) > 0)
                                                            @php
                                                                $activeEscalations = $order['sa_escalations']->filter(function($e) {
                                                                    return $e->status !== 'resolved';
                                                                });
                                                            @endphp
                                                            @if($activeEscalations->count() > 0)
                                                            <div style="background: #ffebe9; border: 1px solid #ff8182; border-radius: 4px; padding: 5px; margin-top: 5px; font-size: 11px; text-align: left;">
                                                                <strong style="color: red;"><i class="voyager-warning"></i> Эскалация SA:</strong><br>
                                                                @foreach($activeEscalations as $esc)
                                                                    <span style="color: #666;">- {{ $esc->reason }}</span><br>
                                                                @endforeach
                                                            </div>
                                                            @endif
                                                        @endif


														{{--@if ($order['is_admin_order'] == 2)
															@include('voyager::orders.is_admin_order')
															@continue
														@endif--}}

														@foreach ($order_columns as $key => $column)
															<td style="text-align: center;" class="td-{{ $loop->index }} td__id">
																@if ($key == 'firts')
																	@include('voyager::orders.payment')
																@elseif($key == 'second')
																	@include('voyager::orders.user')

																	@if($role != "printing")

																		<br>
																		<div>Канал продаж:</div>
																		@foreach ($order_from as $from)
																			@if (isset($order['a_order_from']) and $order['a_order_from'] == $from['id'])
																				<b>{{ $from['title'] }}</b>
																			@endif
																		@endforeach
																		<br><br>
																		<div>Категория:</div>
																		@foreach ($style as $catid)
																			@if ($order['catid'] == $catid['id'])
																				<b>{{ str_replace('</span>', '', str_replace('<span>', '', $catid->getTranslatedAttribute('name'))) }}</b>
																			@endif
																		@endforeach
																		@if ($order['catid'] == 0)
																			<span style="color:red;"><b>нет</b></span>
																		@endif
																		<br><br>
																		<div>Менеджер:</div>
																		@php $m_id = 0; @endphp
																		@if ($managers)
																			@foreach ($managers as $manager)
																				<b>
																					@if ($order['manager_id'] == $manager->id)
																						@php $m_id = $manager->id @endphp @if ($manager->nick)
																							{{ $manager->nick }}
																							-
																						@endif{{ $manager->email }}
																					@endif
																				</b>
																			@endforeach
																			@if ($m_id == 0)
																				<b>Админ</b>
																			@endif
																		@endif
																	@endif


                                                                    <div class="form-group">

                                                                        <?php if($user_status_id ?? 1) ?>
                                                                        <label for="name">Статус клиента</label>
                                                                        <select name="status" id=""
                                                                                onchange="changeClientStatus(this.options[this.selectedIndex].value, {{ $order['user_id'] }})">
                                                                            @foreach ($statuses as $status)
                                                                                <option @if ($user_status_id == $status->id) selected @endif value="{{ $status->id }}">
                                                                                    {{ $status->name }}</option>
                                                                            @endforeach
                                                                        </select>

                                                                    </div>




																@elseif($key == 'third')
																	@include('voyager::orders.user_data')
																@elseif($key == 'four')
																	@include('voyager::orders.items')
																@elseif($key == 'five')
																	@include('voyager::orders.comments')
																@elseif($key == 'six')
																	@php
																		$painter = App\Models\User::getPainterByOrderId($order['id']);
																	@endphp
																	@if ($painter != '')
																		<a target="_blank" href="/admin/users/{{ $painter->id }}/edit">{{ $painter->nick }}
																			- {{ $painter->first_name }} {{ $painter->last_name }}</a>
																		@if($role != "printing")
																			<a style="margin-left:10px"
																			href="/admin/users/{{ $painter->id }}/remove/{{ $order['id'] }}"><span
																				style="color:red;">✖</span></a>
																		@endif
																		<br>
																		<br>
																		@if($role != "printing")
																			@php
																				$order_painter_sketch_images = $order['order_painter_images']->where('is_img_sketch',1);
																				$legacy_painter_sketch_images = collect(array_values(array_filter(array_map('trim', explode(',', trim((string) ($order['painter_sketch_images'] ?? ''), ','))))));
																			@endphp

																			@if ($order_painter_sketch_images->isNotEmpty() || $legacy_painter_sketch_images->isNotEmpty())
																				<b>Набросок:</b><br>
																				{{-- <div class="">
																					<select id="status" name="status"
																						onchange="changePainterSketchImagesStatus(this.options[this.selectedIndex].value, {{ $order['id'] }})">
																						@if ($APainterImagesStatus)
																							@foreach ($APainterImagesStatus as $pis)
																								<option value="{{ $pis->id }}" @if (isset($order['painter_sketch_images_status']) and $order['painter_sketch_images_status'] == $pis->id) selected @endif>
																									{{ $pis->title }}</option>
																							@endforeach
																						@endif
																					</select>
																					<br><span>{{ $order['painter_sketch_images_status_date'] }}</span>
																				</div>
																				<br> --}}
																				<div class="painter__list">
																					@if ($order_painter_sketch_images->isNotEmpty())
																						@foreach ($order_painter_sketch_images as $item_imgs)
																							<span class="painter__img">
																								@php
																									$imgPath = $item_imgs->small_image ? $item_imgs->small_image : $item_imgs->image;
																									$imgSrc = order_image_url($imgPath);
																									$fullSrc = order_image_url($item_imgs->image);
																								@endphp
																								<a target="_blank" href="{{ $fullSrc }}">
																									<img src="{{ $imgSrc }}" alt="">
																								</a>
																							</span>

																							<div class="">
																								<select name="status" onchange="changeImageStatus(this.options[this.selectedIndex].value, {{ $order['id'] }},  {{ $item_imgs['id'] }}, '{{ route('changeOrderPainterImageStatus') }}')">
																									@if ($APainterImagesStatus)
																										@foreach ($APainterImagesStatus as $pis)
																											<option value="{{ $pis->id }}" @if (isset($order['painter_sketch_images_status']) and $item_imgs->status == $pis->id) selected @endif>
																												{{ $pis->title }}</option>
																										@endforeach
																									@endif
																								</select>
																								<br><span>{{ $item_imgs->updated_at }}</span>
																							</div>
																							<hr>
																						@endforeach
																					@else
																						@foreach ($legacy_painter_sketch_images as $painter_img)
																							<span class="painter__img">
																								@php
																									$imgSrc = order_image_url($painter_img);
																								@endphp
																								<a target="_blank" href="{{ $imgSrc }}">
																									<img src="{{ $imgSrc }}" alt="">
																								</a>
																							</span>
																						@endforeach
																					@endif

																				</div>
																			@endif
																		@endif
																		<br>
																		@php
																			$order_painter_picture_images = $order['order_painter_images']->where('is_img_painter',1);
																			$legacy_painter_images = collect(array_values(array_filter(array_map('trim', explode(',', trim((string) ($order['painter_images'] ?? ''), ','))))));
																		@endphp
																		@if ($order_painter_picture_images->isNotEmpty() || $legacy_painter_images->isNotEmpty())
																			<b>Картины:</b><br>
																			@if($role != "printing")
																				{{-- <div class="">
																					<select id="status" name="status"
																						onchange="changePainterImagesStatus(this.options[this.selectedIndex].value, {{ $order['id'] }})">
																						@if ($APainterImagesStatus)
																							@foreach ($APainterImagesStatus as $pis)
																								<option value="{{ $pis->id }}" @if (isset($order['painter_images_status']) and $order['painter_images_status'] == $pis->id) selected @endif>
																									{{ $pis->title }}</option>
																							@endforeach
																						@endif
																					</select>
																					<br><span>{{ $order['painter_images_status_date'] }}</span>
																				</div> --}}
																			@endif
																			<br>
																			<div class="painter__list">
																				@if ($order_painter_picture_images->isNotEmpty())
																				@foreach ($order_painter_picture_images as $item_imgs)
																					<span class="painter__img">
																						@php
																							$imgPath = $item_imgs->small_image ? $item_imgs->small_image : $item_imgs->image;
																							$imgSrc = order_image_url($imgPath);
																							$fullSrc = order_image_url($item_imgs->image);
																						@endphp
																						<a target="_blank" href="{{ $fullSrc }}">
																							<img src="{{ $imgSrc }}" alt="">
																						</a>
																					</span>

																					<div class="">
																						<select name="status" onchange="changeImageStatus(this.options[this.selectedIndex].value, {{ $order['id'] }},  {{ $item_imgs['id'] }}, '{{ route('changeOrderPainterImageStatus') }}')">
																							@if ($APainterImagesStatus)
																								@foreach ($APainterImagesStatus as $pis)
																									<option value="{{ $pis->id }}" @if (isset($order['painter_sketch_images_status']) and $item_imgs->status == $pis->id) selected @endif>
																										{{ $pis->title }}</option>
																								@endforeach
																							@endif
																						</select>
																						<br><span>{{ $item_imgs->updated_at }}</span>
																					</div>
																					<hr>
																				@endforeach
																				@else
																					@foreach ($legacy_painter_images as $painter_img)
																						<span class="painter__img">
																							@php
																								$imgSrc = order_image_url($painter_img);
																							@endphp
																							<a target="_blank" href="{{ $imgSrc }}">
																								<img src="{{ $imgSrc }}" alt="">
																							</a>
																						</span>
																					@endforeach
																				@endif
																			</div>
																		@endif
																		<br>
																		@php
																			$client_imgs = $order['client_images'];
																			$client_imgs = trim($client_imgs, ',');
																			$client_imgs_items = explode(',', $client_imgs);
																		@endphp
																		@if (is_array($client_imgs_items) && !empty($client_imgs_items) && $client_imgs_items[0] != '')
																			<b>Картины клиента:</b><br>
																			<div class="painter__list">
																				@foreach ($client_imgs_items as $img_item)
																					@php
																						$clientImgSrc = order_image_url($img_item);
																					@endphp
																					<div class="painter__img">
																						<a target="_blank" href="{{ $clientImgSrc }}">
																							<img src="{{ $clientImgSrc }}" alt="">
																						</a>
																					</div>
																				@endforeach
																			</div>
																		@endif

																		@if($role != "printing")

																			<br>
																			@if ($is_important_painter == 1)
																				<span style="color:red;font-weight: bold;">Время на заказ</span>
																			@else
																				Время на заказ
																			@endif
																			<input data-till="{{ $d_diff }}" value="{{ $order['painter_endtime'] }}"
																				class="when_compl_{{ $order['id'] }}" name="completed_at" type="date"
																				min="{{ $date_form_now }}">
																			<br>
																			<button class=" js_painter_time btn btn-sm btn-primary"
																				data-id="{{ $order['id'] }}
																					">Обновить</button>
																			<br>
																			<br>
																			Заказ оплачен/выполнен?<br>
																			<select data-id="{{ $order['id'] }}" name="painter_payed" id="painter_payed">
																				<option @if ($order['painter_payed'] == null) selected @endif value="null">нет
																				</option>
																				<option @if ($order['painter_payed'] != null) selected @endif value="1">да
																				</option>
																			</select>
																		@endif
																	@else
																		@if($role != "printing")

																			<select name="painter_choose" id="js_painter_assign" style="width:100%;"
																				data-id="{{ $order['id'] }}">
																				<option value="#">нет</option>
																				@if ($painters)
																					@foreach ($painters as $painter)
																						<option value="{{ $painter->id }}">{{ $painter->nick }}
																							- {{ $painter->email }}</option>
																					@endforeach
																				@endif
																			</select>
																		@endif
																	@endif
																@endif
																</li>
														@endforeach
														</ul>
														@if($role != "printing")
															@if ($painter != '')
																<div class="painter_show_images">
																	<div class="show_title">Показывать картины
																		клиенту?
																	</div>
																	<select name="show_painter_imaged" id="spi" data-id="{{ $order['id'] }}">
																		<option value="1" @if ($order['is_show_painter_images'] == 1) selected @endif>
																			да
																		</option>
																		<option value="0" @if ($order['is_show_painter_images'] == 0 || $order['is_show_painter_images'] == null) selected @endif>
																			нет
																		</option>
																	</select>
																</div>
															@endif
														@endif

															@php
																$printings = App\Models\User::getPrintingByOrderId($order['id']);
															@endphp

															<br><br>
															<div class="show_title">Менеджер печати</div>

															@if ($printings != '')
																<a target="_blank" href="/admin/users/{{ $printings->id }}/edit">{{ $printings->first_name }} {{ $printings->last_name }}</a>

																@if($role != "printing")

																	<a style="margin-left:10px"
																		href="/admin/printing/{{ $printings->id }}/remove/{{ $order['id'] }}"><span
																			style="color:red;">✖</span></a>
																@endif
																<br>
																<br>
															@else
																@if($role != "printing")
																	<select name="printing_choose" id="js_printing_assign" style="width:100%;"
																	data-id="{{ $order['id'] }}">
																	<option value="#">нет</option>
																	@if ($printing)
																		@foreach ($printing as $print)
																			<option value="{{ $print->id }}">{{ $print->nick }}
																				- {{ $print->email }}</option>
																		@endforeach
																	@endif
																	</select>
																@endif
															@endif
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
																@if(isset($order['status']) && $order['status'] && $order['status_date'])
																	<br>
																	Изменено: {{ $order['status_date'] }}
                                                                    <br>

                                                                    @if(isset($order['watching_date']))
                                                                        <br>
                                                                        На рассмотрении: {{ $order['watching_date'] }}
																    @endif

                                                                    @if(isset($order['pegging_date']))
                                                                        <br>
                                                                        В процессе: {{ $order['pegging_date'] }}
																    @endif

                                                                    @if(isset($order['in_production_date']))
                                                                        <br>
                                                                        В производстве: {{ $order['in_production_date'] }}
																    @endif

                                                                    @if(isset($order['send_date']))
                                                                        <br>
                                                                        Отправлен: {{ $order['send_date'] }}
																    @endif

                                                                    @if(isset($order['send_lubanas_date']))
                                                                        <br>
                                                                        Отправлен на Лубанас: {{ $order['send_lubanas_date'] }}
																    @endif

                                                                    @if(isset($order['completed_date']))
                                                                        <br>
                                                                        Завершен: {{ $order['completed_date'] }}
																    @endif
																@endif

																@if(isset($order['user_other_orders']) && count($order['user_other_orders']) > 1)
																	<br>
																	<br>
																	Другие активные заказы: <span style='color:red;'>
																		@foreach ($order['user_other_orders'] as $key => $user_other_orders)
																			@php
																				$linkedOrderUrl = "/admin/orders?order_filter=1&order_id={$user_other_orders}&filter_customer=&filter_phone=++&filter_price=++&filter_order_status_id=&filter_a_order_from=&filter_catid=&filter_country=&filter_painter_choose=&filter_managers=&sizeId=&filter_start_date=&filter_end_date=";
																			@endphp
																			<a href="{{ $linkedOrderUrl }}" style="color:red; text-decoration: underline;">#{{ $user_other_orders }}</a>@if (!$loop->last), @endif
																		@endforeach

																	</span>
																@endif

															</div>
															<br>
															<br>

															<div class="form-group">
																<br><br>
																@php
																	$order_date = date('d/m/Y H:i', strtotime($order['created_at']));
																@endphp
																<b>номер заказа:</b> <span>{{ $order['id'] }}</span><br>
																<b>дата заказа:</b> <span> {{ $order_date }}</span>
																@if (isset($order['delivery']['when_send']) && strtotime($order['delivery']['when_send']))
																	<br>
																	@php
																		$when_send = new DateTime($order['delivery']['when_send']);
																		// echo $order['delivery']['when_send'];
																	@endphp
																	@if($role != "printing")

																		<br>желаемая дата доставки:
																		<br> {{-- $when_send->format('d/m/Y') --}}
																		<input value="{{ $when_send->format('Y-m-d') }}" class="when_send_{{ $order['id'] }}"
																			name="completed_at" type="date" min="{{ $when_send->format('d.m.Y') }}">
																		<br>
																		<button class="js_when_send_time btn btn-sm btn-primary"
																			data-id="{{ $order['id'] }}">Обновить</button>
																	@endif
																	<br>
																	@php
																		$cur_date = new DateTime();
																		$end_date = strtotime($cur_date->format('Y-m-d'));
																		$datediff = strtotime($when_send->format('Y-m-d')) - $end_date;
																	@endphp
																	<b>доставить через:</b> <br>
																	@php
																		$df = floor($datediff / (60 * 60 * 24));
																		$day_diff = str_replace('', '', $df);
																		if ($day_diff < 3) {
																		    $color_z = 'red';
																		} elseif ($day_diff > 2 && $day_diff < 8) {
																		    $color_z = 'orange';
																		} elseif ($day_diff > 7) {
																		    $color_z = 'green';
																		}
																	@endphp
																	@if ($day_diff == 0)
																		сегодня
																	@else
																		<span style="font-weight: bold;font-size:16px;color:{{ $color_z }};">
																			{{ $day_diff }} дня(ей)
																		</span>
																	@endif
																	<br>
																	<b>тип заказа:
																		@if ($day_diff < 4)
																			<p style="font-weight: bold;font-size:16px;color:{{ $color_z }};">
																				Срочный</p>
																		@else
																			<p>Обычный</p>
																		@endif
																	</b>
																@else

																		<br><br>
																		<span style="font-weight: bold;">желаемая дата доставки:</span>
																		<input data-till="{{ $d_diff }}" value="" class="when_send_{{ $order['id'] }}"
																			name="completed_at" type="date" min="{{ $date_form_now }}">
																		<br>
																		<button class="js_when_send_time btn btn-sm btn-primary"
																			data-id="{{ $order['id'] }}">Обновить</button>

																@endif
															</div>



																<br>
																<br>
																<a class="btn" style="text-align: right;float: right;background: rgb(0, 89, 255);color: #fff;"
																	href="{{ route('create_admin_order', ['from_order_id' => $order['id']]) }}">Создать заказ</a>
																<br>
																<a class="btn" style="text-align: right;float: right;background: orange;color: #fff;"
																	href="{{ route('edit_admin_order', $order['id']) }}">Редактировать заказ</a>
																<br>
																<a class="btn btn-sm btn-warning pull-right view btn_orders" style="clear:both"
																	href="/admin/users/{{ $order['user_id'] }}">
																	<span class="hidden-xs hidden-sm text__btn__span">Просмотр
																		клиента</span>
																</a>
																<br>
																<a href="/admin/orders?order_filter=1&user_id={{ $order['user_id'] }}"

																	class="btn btn-sm btn-warning pull-right view btn_orders">
																	<span class="hidden-xs hidden-sm text__btn__span">Все закаы ранее</span>
																</a>
															@if($role != "printing")
																<br>
																<a href="javascript:;" onclick="deleteOrder( {{ $order['id'] }} )" title="Удалить"
																	style="clear:both;" class="btn btn-sm btn-danger pull-right delete">
																	<i class="voyager-trash"></i>
																	<span class="hidden-xs hidden-sm text__btn__span">Удалить</span>
																</a>
															@endif
															@if (!empty($order['photo']))
																<div style="background-color: gainsboro; margin: 10px 0px 0px 0px;">
																	Исходящая накладная
																</div>
																<p>
																	<a class="in_btn" href="{{ $order['photo'] }}">Открыть</a><a class="in_btn" download
																		href="{{ $order['photo'] }}">Скачать</a>
																</p>
															@endif
														</td>
													</tr>
                                                    <tr class="eticet__tr">
                                                        <td class="eticet__form js__closing__form" style="grid-column: span {{ count($order_columns) + 1 }}; display: none;" id="order-{{ $order['id'] }}" colspan="{{ count($order_columns) + 1 }}">
                                                            @if (isset($order['user']))
                                                                @include('voyager::form__label')
                                                            @endif
                                                        </td>
                                                    </tr>
												@endforeach
											</div>
										</table>
										{{-- new --}}
										{{-- @foreach ($orders as $order_bot)
											@php
												if (!isset($_GET['order_filter'])) {
												    $order = (array) $order_bot;
												    $order['delivery'] = json_decode($order['delivery'], true);
												} else {
												    $order = $order_bot;
												}
											@endphp
											<div class="eticet__form js__closing__form" style="display: none;" id="order-{{ $order['id'] }}">

												@if (isset($order['user']))
													@include('voyager::form__label')
												@endif
											</div>
										@endforeach --}}
										{{-- endndew --}}
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		@if (!is_array($orders))
			@if ($no_pagin != 1)
				{{ $orders->links() }}
			@endif
		@endif
	</div>
    <div class="modal fade payment-request-modal" id="paymentRequestQuickModal" tabindex="-1" role="dialog" aria-labelledby="paymentRequestQuickModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="paymentRequestQuickForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                    <input type="hidden" name="context_order_id" id="payment_request_context_order_id" value="{{ old('context_order_id') }}">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="paymentRequestQuickModalLabel">Создать платеж</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="payment_request_quick_amount">Сумма, EUR</label>
                            <input
                                type="number"
                                min="0.01"
                                max="999999.99"
                                step="0.01"
                                class="form-control"
                                id="payment_request_quick_amount"
                                name="amount"
                                value="{{ old('amount') }}"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="payment_request_quick_purpose">Назначение платежа</label>
                            <input
                                type="text"
                                class="form-control"
                                id="payment_request_quick_purpose"
                                name="purpose"
                                maxlength="255"
                                value="{{ old('purpose') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Создать ссылку</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
	<style>
		.list__adm__items {
			list-style: none;
		}

		.orda__col ul {
			padding: 0;
			margin: 0;
		}

		.comments__main__list .active{
			font-weight: 600;
			color: rgb(80, 9, 9);
		}

		.orda__col {
			word-break: break-all;
			margin: 0 auto;
		}
	</style>
	@stop @section('javascript')

	@include('vendor.voyager.partials.orders.bot_scripts')
@stop







