<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru" xml:lang="ru">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width">
	<title></title>
</head>

<body style="position:relative;margin: 0 auto;">
	<link rel="stylesheet" href="https://use.typekit.net/aif1ths.css">
	<table width="100%" cellpadding="0" cellspacing="0"
		style="display: block;max-width: 600px;margin: 0 auto;font-family: 'Lato', sans-serif; max-width: 600px;position: relative;padding-left: 35px;padding-bottom: 50px;margin-bottom: 100px;padding-top: 30px; border: 0;box-sizing: border-box;background: url('http://viaranvas.com.com/letters/bg.png');background-repeat: no-repeat;background-size: 100% 100%;padding-right: 20px;border-radius: 19px;">
		<tr>
			<td>
				<img src="http://viaranvas.com/letters/logo.png" alt="logo" style="margin-bottom: 15px;">
			</td>
		</tr>
		<tr>
			<td>
				<h1 style="font-family:'Lato', sans-serif;font-size:30px;margin: 0;font-weight: 700;">
					<?php echo $data['sended_sub']; ?></h1>
			</td>
		</tr>
		<tr>
			<td style="font-size: 18px; font-weight: 900; color: #a0565f;">
				<?php echo $data['sended_noty']; ?>
			</td>
		</tr>
		<tr>
			<td>
				<span style="font-size: 16px;line-height: 24px; font-weight: 400;margin-top: 25px;margin-bottom: 15px;">
					<?php echo $data['your_order_z_data']; ?></span>
				<ul
					style="font-size: 16px;line-height: 24px;margin-bottom: 0px;list-style-position: outside;padding-left: 20px;">
					<li style="font-weight: 900;display: list-item;list-style-position: outside;">
						<?php echo $data['sended_date_zak']; ?> <?php echo $created_at; ?>
					</li>
					<li style="font-weight: 900;display: list-item;list-style-position: outside;">
						<?php echo $data['sended_date_otp']; ?> <?php echo $now; ?>
					</li>
					<?php if(isset($order['delivery']['when_send'])){ ?>
					<li style="font-weight: 900;display: list-item;list-style-position: outside;">
						<?php if(strtotime($order['delivery']['when_send'])) { ?>
						<?php echo $data['sended_date_jel']; ?>:
						<?php $when_send = new DateTime($order['delivery']['when_send']); ?>
						<?php echo $when_send; ?>
						<?php } ?>

						<br>

					</li>
					<?php } ?>
				</ul>
			</td>
		</tr>
	</table>
</body>

</html>