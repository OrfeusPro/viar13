							@php
								$showed_box = 0;
							@endphp

							@if(isset($product['name']))
								<tr>
									<td align="left" valign="middle">
										<span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;line-height: 17px;color: #e76b3c;">{!!$product['name']!!} @if(isset($product['sizeId'])){{$product['sizeId']}}@endif @if(isset($product['size_name'])){{$product['size_name']}}@endif - @if(isset($product['sumPrice'])){{$product['sumPrice']}}€@endif</span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
							@endif
								
{{--
							@isset($product['savedImage'])
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{ trans('gl.orig_images') }}: <strong>
											<a href="{{ $product['savedImage'] }}" target="_blank" target="_blank">#0</a>
											</strong>
									</span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
							@endif
	
							@isset($product['orig_images'])
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{ trans('gl.orig_images') }}: <strong>
									@foreach($product['orig_images'] as $img)
										<span style="margin-right:5px;">
											<a href="{{ $img }}" target="_blank">#{{ $loop->index }}</a>
										</span>
									@endforeach</strong>
									</span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
							@endif
--}}

							

							@isset($product['compl_id'])
								@if($product['compl_id'] != 'undefined')
									<tr>
										<td align="left" valign="middle">
											<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
											<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
									  line-height: 17px;color: #1E2533;">{{ trans('gl.pack') }}: <strong>{{ App\Models\GalleryBox::getNameById($product['compl_id'])['name'] }}</strong></span>
										</td>
									</tr>
									<tr>
										<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
									</tr>
								@endif
							@endisset

{{--
							@isset($product['formIndex'])
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{ trans('gl.form_num_text') }}: <strong>{{ $product['formIndex'] }}</strong></span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
							@endisset

							@isset($product['is_gift_card'])
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{ trans('gl.sender') }}: <strong>{{ data_get($product, 'sender', '') }}</strong></span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
							
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{ trans('gl.reseiver') }}: <strong>{{ data_get($product, 'reseiver', '') }}</strong></span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
							
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{ trans('gl.date') }}: <strong>{{ data_get($product, 'date', '') }}</strong></span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
								
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{ trans('gl.torjname') }}: <strong>{{ data_get($product, 'torjname', '') }}</strong></span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
							
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{ trans('gl.torjtext') }}: <strong>{{ data_get($product, 'torjtext', '') }}</strong></span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
							
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{ trans('gl.card_type') }}: <strong>{{ $product['card_type'] }}</strong></span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
							
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{ trans('mail.price') }}: <strong>@if((string) data_get($product, 'hide_nom', 'false') == 'false') {{ trans('gl.nom_show') }} @else {{ trans('gl.nom_hide') }} @endif</strong></span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
								
								@if(isset($is_admin_data_orders))
									@isset($product['gift_code'])
										<li>{{ $product['gift_code'] }}</li>
									@endisset
								@endif
							@endisset

							@isset($product['formId'])
								@if($product['formId'] != 'undefined')
									<tr>
										<td align="left" valign="middle">
											<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
											<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
									  line-height: 17px;color: #1E2533;">{{ trans('gl.form_id') }}: <strong>{{ $product['formId'] }}</strong></span>
										</td>
									</tr>
									<tr>
										<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
									</tr>
								@endif
							@endisset
--}}
							@if(isset($product['execution']))
								@if($product['execution'] != 'undefined')
									<tr>
										<td align="left" valign="middle">
											<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
											<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
									  line-height: 17px;color: #1E2533;">{{ trans('cart.execution_type') }}: <strong>{{$product['execution']}}</strong></span>
										</td>
									</tr>
									<tr>
										<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
									</tr>
								@endif
							@endif

{{--
							@isset($product['is_port_product'])

								@isset($product['forma_id'])
									@if($product['forma_id'] != 'undefined')
										<tr>
											<td align="left" valign="middle">
												<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
												<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										  line-height: 17px;color: #1E2533;">{{ trans('gl.bask_form') }}: <strong>{{ $product['forma_id'] }}</strong></span>
											</td>
										</tr>
										<tr>
											<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
										</tr>
									@endif
								@endisset

								@isset($product['users_count'])
									@if($product['users_count'] != 'undefined')
										<tr>
											<td align="left" valign="middle">
												<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
												<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										  line-height: 17px;color: #1E2533;">{{ trans('gl.bask_persons') }}: <strong>{{ $product['users_count'] }}</strong></span>
											</td>
										</tr>
										<tr>
											<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
										</tr>
									@endif
								@endisset

								@isset($product['type'])
									@if($product['type'] != 'undefined')
										<tr>
											<td align="left" valign="middle">
												<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
												<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										  line-height: 17px;color: #1E2533;">{{ trans('gl.bask_isp') }}: <strong>{{ $product['type'] }}</strong></span>
											</td>
										</tr>
										<tr>
											<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
										</tr>
									@endif
								@endisset

								@isset($product['holst_id'])
									@if($product['holst_id'] != 'undefined')
										<tr>
											<td align="left" valign="middle">
												<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
												<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										  line-height: 17px;color: #1E2533;">{{ trans('gl.bask_holst') }}: <strong>{{ App\Models\GalleryHolst::getHolstNameById($product['holst_id']) }}</strong></span>
											</td>
										</tr>
										<tr>
											<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
										</tr>
									@endif
								@endisset

								
								@isset($product['hud_of'])
									@if($product['hud_of'] != 'undefined')
										<tr>
											<td align="left" valign="middle">
												<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
												<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										  line-height: 17px;color: #1E2533;">{{ trans('gl.bask_of') }}: <strong>{{ $product['hud_of'] }}</strong></span>
											</td>
										</tr>
										<tr>
											<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
										</tr>
								
									@endif
								@endisset
							@endisset


							@isset($product['ram_id'])
								@if($product['ram_id'] != 'undefined' && \App\Models\CanvasRam::isFramedOption($product['ram_id']))
									<tr>
										<td align="left" valign="middle">
											<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
											<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
									  line-height: 17px;color: #1E2533;">{{ trans('gl.bask_ram') }}: <strong>{{ App\Models\CanvasRam::getRamNameById($product['ram_id']) }}</strong></span>
										</td>
									</tr>
									<tr>
										<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
									</tr>
								@endif
							@endisset


							@isset($product['wall_size_mod'])
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{ trans('gl.wall_size') }}: <strong>{{ $product['wall_size_mod'] }}</strong></span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
							@endisset
--}}
							@php
								$is_mod = 0;
							@endphp

							@if(isset($product['terms']) && $product['terms'])
								@if($is_mod == 0)
									<tr>
										<td align="left" valign="middle">
											<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
											<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
									  line-height: 17px;color: #1E2533;">{{ trans('gl.bask_izg') }}: <strong>{{ $product['terms'] }}</strong></span>
										</td>
									</tr>
									<tr>
										<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
									</tr>
								@endif
							@endif
{{--
							@isset($product['is_canvas_inter'])
								@isset($product['wall_size'])
									@if($product['wall_size'] != 'x')
										<tr>
											<td align="left" valign="middle">
												<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
												<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										  line-height: 17px;color: #1E2533;">{{ trans('gl.wall_size') }}: <strong>{{ $product['wall_size'] }}</strong></span>
											</td>
										</tr>
										<tr>
											<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
										</tr>
									@endif
								@endisset

								@isset($product['pic_size'])
									@if($product['pic_size'] != 'x')
										<tr>
											<td align="left" valign="middle">
												<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
												<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										  line-height: 17px;color: #1E2533;">{{ trans('gl.pic_size') }}: <strong>{{ $product['pic_size'] }}</strong></span>
											</td>
										</tr>
										<tr>
											<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
										</tr>
									@endif
								@endisset

								@isset($product['ram_id'])
									@if($product['ram_id'] != 'undefined')
										<tr>
											<td align="left" valign="middle">
												<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
												<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										  line-height: 17px;color: #1E2533;">{{ trans('gl.rama') }}: <strong>{{ App\Models\GalleryItem::getRamNameById( $product['ram_id']) }}</strong></span>
											</td>
										</tr>
										<tr>
											<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
										</tr>
									@endif
								@endisset
							@endisset
							


							@isset($product['is_modular_inter'])
								@if($product['wall_size'] != 'x')
									<tr>
										<td align="left" valign="middle">
											<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
											<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
									  line-height: 17px;color: #1E2533;">{{ trans('gl.wall_size') }}: <strong>{{ $product['wall_size'] }}</strong></span>
										</td>
									</tr>
									<tr>
										<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
									</tr>
								@endif

								@isset($product['pic_size'])
									@if($product['pic_size'] != 'x')
										<tr>
											<td align="left" valign="middle">
												<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
												<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										  line-height: 17px;color: #1E2533;">{{ trans('gl.pic_size') }}: <strong>{{ $product['pic_size'] }}</strong></span>
											</td>
										</tr>
										<tr>
											<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
										</tr>
									@endif
								@endisset

								@isset($product['ram_id'])
									@if($product['ram_id'] != 'undefined')
										<tr>
											<td align="left" valign="middle">
												<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
												<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										  line-height: 17px;color: #1E2533;">{{ trans('gl.rama') }}: <strong>{{ App\Models\GalleryItem::getRamNameById( $product['ram_id']) }}</strong></span>
											</td>
										</tr>
										<tr>
											<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
										</tr>
									@endif
								@endisset
							@endisset



							@isset($product['show'])
								@isset($product['show']['basketType'])
									<tr>
										<td align="left" valign="middle">
											<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
											<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
									  line-height: 17px;color: #1E2533;">@lang('basket.picture_type'): <strong>{{ $product['show']['basketType'] }}</strong></span>
										</td>
									</tr>
									<tr>
										<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
									</tr>
								@endisset
								
								@isset($product['show']['effect'])
									<tr>
										<td align="left" valign="middle">
											<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
											<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
									  line-height: 17px;color: #1E2533;">{{ $product['show']['effect'] }}</span>
										</td>
									</tr>
									<tr>
										<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
									</tr>
								@endisset
								
								
								@isset($product['show']['decoration'])
									<tr>
										<td align="left" valign="middle">
											<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
											<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
									  line-height: 17px;color: #1E2533;">{{ trans('gl.hud_of_text') }}: <strong>{{ $product['show']['decoration'] }}</strong></span>
										</td>
									</tr>
									<tr>
										<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
									</tr>
								@endisset

								@if(!isset($product['sizeId']))
									@isset( $product['show']['size'])
										<tr>
											<td align="left" valign="middle">
												<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
												<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										  line-height: 17px;color: #1E2533;">{{ trans('cart.size') }}: <strong>{{ $product['show']['size'] }}</strong></span>
											</td>
										</tr>
										<tr>
											<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
										</tr>
									@endisset
								@endif
								

								@isset( $product['show']['execution'])
									<tr>
										<td align="left" valign="middle">
											<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
											<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
									  line-height: 17px;color: #1E2533;">{{ trans('cart.execution_type') }}: <strong>{{ $product['show']['execution'] }}</strong></span>
										</td>
									</tr>
									<tr>
										<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
									</tr>
								@endisset
								
								@isset( $product['show']['canvas'])
									<tr>
										<td align="left" valign="middle">
											<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
											<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
									  line-height: 17px;color: #1E2533;">{{ trans('cart.canvas') }}: <strong>{{ $product['show']['canvas'] }}</strong></span>
										</td>
									</tr>
									<tr>
										<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
									</tr>
								@endisset
								
								@isset($product['show']['box'])
									@php
										$showed_box = 1;
									@endphp
									
									<tr>
										<td align="left" valign="middle">
											<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
											<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
									  line-height: 17px;color: #1E2533;">{{ trans('cart.packaging') }}: <strong>{{ implode(', ', $product['show']['box']) }}</strong></span>
										</td>
									</tr>
									<tr>
										<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
									</tr>
								@endisset
								
							@endisset

--}}

							@isset($product['canvas'])
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{ trans('cart.canvas') }}: <strong>{{ $product['canvas'] }}</strong></span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
							@endif

							@if(isset($product['pack']) && $product['pack'])
								@if($product['pack'] != 'undefined')
									@if($showed_box != 1)
										<tr>
											<td align="left" valign="middle">
												<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
												<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
										  line-height: 17px;color: #1E2533;">{{ trans('cart.packaging') }}: <strong>{{ $product['pack'] }}</strong></span>
											</td>
										</tr>
										<tr>
											<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
										</tr>
									@endisset
								@endif
							@endif

							{{--
							@if(isset($product['userComment']))
								<tr>
									<td align="left" valign="middle">
										<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
										<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
								  line-height: 17px;color: #1E2533;">{{$ts1['comments']}}: <strong>{{$product['userComment']}}</strong></span>
									</td>
								</tr>
								<tr>
									<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
								</tr>
							@endif
							--}}
