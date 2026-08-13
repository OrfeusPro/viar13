<div class="vz-art cart-page-global">
	<div class="section-frame">
		@if (isset($response))
			<b>Ваш заказ оплачен</b>
			<br>
			<br>
			<br>
			<br>
			@isset($response['orderid'])
				orderid: {{ $response['orderid'] }}<br>
			@endisset

			@isset($response['amount'])
				amount: {{ $response['amount'] }} <br>
			@endisset

			@isset($response['currency'])
				currency: {{ $response['currency'] }} <br>
			@endisset

			@isset($response['country'])
				country: {{ $response['country'] }} <br>
			@endisset

			@isset($response['p_firstname'])
				p_firstname: {{ $response['p_firstname'] }} <br>
			@endisset

			@isset($response['p_lastname'])
				p_lastname: {{ $response['p_lastname'] }} <br>
			@endisset

			@isset($response['test'])
				test: {{ $response['test'] }} <br>
			@endisset

			@isset($response['version'])
				version: {{ $response['version'] }} <br>
			@endisset

			@isset($response['projectid'])
				projectid: {{ $response['projectid'] }} <br>
			@endisset

			@isset($response['original_paytext'])
				original_paytext: {{ $response['original_paytext'] }} <br>
			@endisset

			@isset($response['paytext'])
				paytext: {{ $response['paytext'] }} <br>
			@endisset

			@isset($response['payment'])
				payment: {{ $response['payment'] }} <br>
			@endisset

			@isset($response['p_email'])
				p_email: {{ $response['p_email'] }} <br>
			@endisset

			@isset($response['m_pay_restored'])
				m_pay_restored: {{ $response['m_pay_restored'] }} <br>
			@endisset

			@isset($response['tried_changing_email'])
				tried_changing_email: {{ $response['tried_changing_email'] }} <br>
			@endisset

			@isset($response['frame'])
				frame: {{ $response['frame'] }} <br>
			@endisset

			@isset($response['status'])
				status: {{ $response['status'] }} <br>
			@endisset

			@isset($response['requestid'])
				requestid: {{ $response['requestid'] }} <br>
			@endisset

			@isset($response['name'])
				name: {{ $response['name'] }} <br>
			@endisset

			@isset($response['surename'])
				surename: {{ $response['surename'] }} <br>
			@endisset

			@isset($response['payamount'])
				payamount: {{ $response['payamount'] }} <br>
			@endisset

			@isset($response['paycurrency'])
				paycurrency: {{ $response['paycurrency'] }} <br>
			@endisset

			@isset($response['account'])
				account: {{ $response['account'] }} <br>
			@endisset

			@isset($response['type'])
				type: {{ $response['type'] }} <br>
			@endisset
		@endif
		@if (isset($exception))
			Ошибка оплаты
			@php var_dump($exception) @endphp
		@endif
	</div>
</div>
