<table cellpadding="0" cellspacing="0" width="100%" style="width: 100%;max-width: 332px;background: #ffffff;display: inline-block;vertical-align: top;">
                        <tr>
                            <td align="center" valign="top" style="padding: 9px;">
                                <table cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #fa7846;">

									<tr>
										<td align="center" valign="top" style="padding: 9px;">
											<table cellpadding="0" cellspacing="0" width="100%">


												<tr>
													<td height="10" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>
												<tr>
													<td align="left" valign="top">
														<span style="font-family: Arial;font-style: normal;font-weight: bold;font-size: 15px;
													line-height: 17px;color: #1E2533;">@lang('mail.order_detail_title')</span>
													</td>
												</tr>
												<tr>
													<td height="20" style="font-size:0; line-height:0;">&nbsp;</td>
												</tr>

												@foreach ($basket as $product)
													@include('.mail.item', ['product'=> $product])
												@endforeach


												@if(isset($order['delivery']['sposob']) && $order['delivery']['sposob'])
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
															<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: #1E2533;">@lang('mail.delivery_sposob'):
																<strong>
																	@if ($order['delivery']['sposob'] == 'to_the_door')
																		@lang('mail.delivery_sposob_to_the_door')
																	@elseif($order['delivery']['sposob'] == 'pickup_Riga')
																		@lang('mail.delivery_sposob_pickup_riga')
																	@elseif($order['delivery']['sposob'] == 'pickup_Daugavplis')
																		@lang('mail.delivery_sposob_pickup_daugavplis')
																	@elseif($order['delivery']['sposob'] == 'venipak')
                                                                        @lang('cart_new.step_3_delivery_to_pick-up_point')
																	@elseif($order['delivery']['sposob'] == 'pickup_at_viar_workshop')
                                                                        @lang('cart_new.step_3_pick_up_at_viar_workshop')
                                                                    @elseif($order['delivery']['sposob'] == 'city_delivery')
                                                                        <p>Доставка по {{$order['delivery']['city']}}</p>
                                                                    @endif


                                                                    @if(isset($order['delivery']['deliv_price']) && $order['delivery']['deliv_price'] && $order['delivery']['deliv_price'] > 0)
                                                                        - {{ $order['delivery']['deliv_price'] }}€
                                                                    @endif
																</strong>
															</span>
														</td>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endisset

												@if(isset($order['payment']) && $order['payment'])
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
															<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: #1E2533;">@lang('mail.delivery_payment'):
																<strong>
																	@if ($order['payment'] == 'cash_in_office')
																		@lang('mail.delivery_payment_cash_in_office')
																	@elseif($order['payment'] == 'on_delivery')
																		@lang('mail.delivery_payment_on_delivery')
																	@elseif($order['payment'] == 'transfer')
																		@lang('mail.delivery_payment_transfer')
                                                                    @elseif($order['payment'] == 'prepayment')
                                                                        @lang('mail.prepayment')
                                                                    @elseif($order['payment'] == 'online_paysera')
                                                                        @lang('cart_new.step_4_payment_by_card')
                                                                    @elseif($order['payment'] == 'google_pay')
                                                                        Google Pay
                                                                    @elseif($order['payment'] == 'apple_pay')
                                                                        Apple Pay
                                                                    @elseif($order['payment'] == 'paypalOnetimePayment')
                                                                        PayPal
																	@elseif($order['payment'] == 'creditcart')
																		@lang('cart_new.step_4_by_card_online')
																	@endif
																</strong>
															</span>
														</td>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endisset

												@if (isset($order['delivery']['is_coupon_30_40']))
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																border="0" alt="check-circle" />
															<span
																style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.coupon_used'):
																30x40
															</span>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endif

												@if (isset($order['delivery']['bonus']))
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																border="0" alt="check-circle" />
															<span
																style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.bonuses_used'):
																<?= $order['delivery']['bonus'] ?>
															</span>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endif

												@if (isset($order['delivery']['is_coupon_40_60']))
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																border="0" alt="check-circle" />
															<span
																style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.coupon_used'):
																40x60
															</span>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endif

												@if (isset($order['delivery']['is_coupon_dates']))
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																border="0" alt="check-circle" />
															<span
																style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.coupon_used'):
																@lang('coupon.2_dates')
															</span>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endif

												@if (isset($order['delivery']['has_invited_sale']))
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																border="0" alt="check-circle" />
															<span
																style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.invitation_discount_used')
															</span>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endif

												@if (isset($order['delivery']['is_1free']))
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																border="0" alt="check-circle" />
															<span
																style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.promotion_used_3_1')
															</span>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endif

												@if (isset($order['delivery']['is_universal']))
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																border="0" alt="check-circle" />
															<span
																style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.universal')
															</span>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endif

												@if (isset($order['delivery']['is_abandoned_basket']))
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																border="0" alt="check-circle" />
															<span
																style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.abandoned_basket')
															</span>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endif

												@if (isset($order['delivery']['is_giftcard']))
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																border="0" alt="check-circle" />
															<span
																style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.giftcard')
															</span>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endif

												@if (isset($order['delivery']['coupon_type']))
													@if ($order['delivery']['coupon_type'] == '30_40')
														<tr>
															<td align="left" valign="middle">
																<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																	style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																	border="0" alt="check-circle" />
																<span
																	style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.coupon_used'):
																	30x40
																</span>
														</tr>
														<tr>
															<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
														</tr>
													@endif

													@if ($order['delivery']['coupon_type'] == 'bonus')
														<tr>
															<td align="left" valign="middle">
																<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																	style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																	border="0" alt="check-circle" />
																<span
																	style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.bonuses_used')
																</span>
														</tr>
														<tr>
															<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
														</tr>
													@endif

													@if ($order['delivery']['coupon_type'] == 'facebook')
														<tr>
															<td align="left" valign="middle">
																<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																	style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																	border="0" alt="check-circle" />
																<span
																	style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.coupon_used_facebook_screenshot')
																</span>
														</tr>
														<tr>
															<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
														</tr>
													@endif

													@if ($order['delivery']['coupon_type'] == '40_60')
														<tr>
															<td align="left" valign="middle">
																<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																	style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																	border="0" alt="check-circle" />
																<span
																	style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.coupon_used'):
																	40x60
																</span>
														</tr>
														<tr>
															<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
														</tr>
													@endif

													@if ($order['delivery']['coupon_type'] == 'date')
														<tr>
															<td align="left" valign="middle">
																<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																	style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																	border="0" alt="check-circle" />
																<span
																	style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.coupon_used'):
																	@lang('coupon.2_dates')
																</span>
														</tr>
														<tr>
															<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
														</tr>
													@endif

													@if ($order['delivery']['coupon_type'] == 'friend')
														<tr>
															<td align="left" valign="middle">
																<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																	style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																	border="0" alt="check-circle" />
																<span
																	style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.invitation_discount_used')
																</span>
														</tr>
														<tr>
															<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
														</tr>
													@endif

													@if ($order['delivery']['coupon_type'] == '1free')
														<tr>
															<td align="left" valign="middle">
																<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																	style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																	border="0" alt="check-circle" />
																<span
																	style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">@lang('coupon.promotion_used_3_1')

																</span>
														</tr>
														<tr>
															<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
														</tr>
													@endif

													@if ($order['delivery']['coupon_type'] == 'universal')
														<tr>
															<td align="left" valign="middle">
																<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20"
																	style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;"
																	border="0" alt="check-circle" />
																<span
																	style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px; line-height: 17px;color: orange;font-weight: bold;">
																	@lang('coupon.universal')
																</span>
														</tr>
														<tr>
															<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
														</tr>
													@endif
												@endif

												@if(isset($basket['totalPrice']))
													<tr>
														<td align="left" valign="middle">
															<img src="https://viarcanvas.com/letters/new/check-circle-1.png" width="26" height="20" style="margin:0; padding:0; border:none; display:inline-block;vertical-align: bottom;" border="0" alt="check-circle" />
															<span style="font-family: Arial;font-style: normal;font-weight: normal;font-size: 15px;
													  line-height: 17px;color: #1E2533;">@lang('mail.totalprice'): <strong>


                                                        @if(isset($basket['sale_price']) && $basket['sale_price'] && isset($order['delivery']['deliv_price']) && isset($basket['total_terms_price']))
                                                            {{ (float)$basket["sale_price"] + (float)$order['delivery']['deliv_price'] + (float)$basket["total_terms_price"] }}
                                                        @elseif(isset($basket['sale_price']) && $basket['sale_price'] && isset($order['delivery']['deliv_price']) && $order['delivery']['deliv_price'] && isset($basket['total_terms_price']) && $basket['total_terms_price'])
                                                            {{ $basket['sale_price'] + $order['delivery']['deliv_price'] + $basket['total_terms_price'] }}
                                                        @elseif(isset($basket['sale_price']) && $basket['sale_price'] && isset($order['delivery']['deliv_price']) && $order['delivery']['deliv_price'])
                                                            {{ $basket['sale_price'] + $order['delivery']['deliv_price'] }}
                                                        @elseif(isset($basket['sale_price']) && $basket['sale_price'] && isset($basket['total_terms_price']) && $basket['total_terms_price'])
                                                            {{ $basket['sale_price'] + $basket['total_terms_price'] }}
                                                        @elseif(isset($order['delivery']['deliv_price']) && $order['delivery']['deliv_price'] && isset($basket['total_terms_price']) && $basket['total_terms_price'])
                                                            {{ $basket['totalPrice'] + $order['delivery']['deliv_price'] + $basket['total_terms_price'] }}
                                                        @elseif(isset($order['delivery']['deliv_price']) && $order['delivery']['deliv_price'])
                                                            {{ $basket['totalPrice'] + $order['delivery']['deliv_price'] }}
                                                        @elseif(isset($basket['total_terms_price']) && $basket['total_terms_price'])
                                                            {{ $basket['totalPrice'] + $basket['total_terms_price'] }}
                                                        @else
                                                            {{ $basket['totalPrice'] }}
                                                        @endif

                                                        €</strong></span>
														</td>
													</tr>
													<tr>
														<td height="17" style="font-size:0; line-height:0;">&nbsp;</td>
													</tr>
												@endif
											</table>
										</td>
									</tr>

                                </table>
                            </td>
                        </tr>
                    </table>
