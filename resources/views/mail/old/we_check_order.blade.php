<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru" xml:lang="ru">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width">
	<title></title>
</head>



<body style="margin: 0 auto;padding-bottom: 0px;">
	<link rel="stylesheet" href="https://use.typekit.net/aif1ths.css">
	<table width="100%" cellpadding="0" cellspacing="0"
		style="display: block;max-width: 600px;margin: 0 auto;font-family: 'Lato', sans-serif; max-width: 600px;position: relative;padding-left: 35px;padding-bottom:50px;padding-top: 30px; border: 0;box-sizing: border-box;background: url('<?php echo $base_url; ?>/letters/bg.png');background-repeat: no-repeat;background-size: 100% 100%;margin-bottom:100px; border-radius: 19px;padding-right: 20px;">
		<tr>
			<td>
				<img src="<?php echo $base_url; ?>/letters/logo.png" alt="logo" style="margin-bottom: 15px;">
			</td>
		</tr>
		<tr>
			<td>
				<h1 style="font-family:'Lato', sans-serif;font-size:30px;margin: 0;font-weight: 700;">
					<?php echo $data['we_check_title']; ?>
					(#<?php echo $order_id; ?>)</h1>
			</td>
		</tr>
		<tr>
			<td style="font-size: 18px; font-weight: 900; color: #a0565f;">
				<?php echo $data['we_check_sub']; ?>
			</td>
		</tr>
		<tr>
			<td>
				<span
					style="font-size: 16px;line-height: 24px; font-weight: 400;margin-top: 25px;display: flex;margin-bottom: 15px;">
					<?php echo $data['your_order_z_data']; ?></span>
				<ul
					style="font-size: 16px;line-height: 24px;margin-bottom: 0px;list-style-position: outside;padding-left: 20px;">
					<?php foreach ($order['items'] as $product) { ?>
					<?php if(!isset($product['sumPrice'])) continue; ?>

					<?php if(isset($product['name'])) { ?>
					<li style="display: list-item;list-style-position: outside;">
						<?php echo $product['name']; ?>
					</li>
					<?php } ?>

					<li style="display: list-item;list-style-position: outside;">
						<?php if(isset($product['sizeId'])) { ?>
						<?php echo $ts1['size_text']; ?>: <b><?php echo $product['sizeId']; ?></b>
						<?php } ?>

						<?php if(isset($product['size_name'])) { ?>
						<?php echo $ts1['size_text']; ?>: <b><?php echo $product['size_name']; ?></b>
						<?php } ?>
					</li>

					<?php if(isset($product['execution'])) { ?>
					<?php if($product['execution'] != 'undefined') { ?>
					<li style="display: list-item;list-style-position: outside;">
						<?php echo $ts1['bask_isp']; ?>: <?php echo $product['execution'] ?>
					</li>
					<?php } ?>
					<?php } ?>

					<?php if(isset($product['hud_of'])) { ?>
					<?php if($product['hud_of'] != 'undefined') { ?>
					<li style="display: list-item;list-style-position: outside;">
						<?php echo $ts1['bask_of']; ?>: <?php echo $product['hud_of'] ?>
					</li>
					<?php } ?>
					<?php } ?>

					<?php if(isset($product['show'])) { ?>
					<?php if(isset($product['show']['decoration'])) { ?>
					<li style="display: list-item;list-style-position: outside;">
						<?php echo $ts1['bask_of']; ?>: <?php echo $product['show']['decoration']; ?>
					</li>
					<?php } ?>
					<?php if(isset($product['show']['execution'])) { ?>
					<li style="display: list-item;list-style-position: outside;">
						<?php echo $ts1['bask_isp']; ?> <?php echo $product['show']['execution']; ?>
					</li>
					<?php } ?>
					<?php } ?>

					<?php if(isset($product['terms'])) { ?>
					<li style="display: list-item;list-style-position: outside;">
						<?php echo $ts1['bask_izg']; ?>:
						{{ \App\Models\GalleryItem::getTermsByPriceLocaled($product['terms'], $locale) }}
					</li>
					<?php } ?>


					<?php if(isset($product['pack'])) { ?>
					<li style="display: list-item;list-style-position: outside;">
						<?php echo $ts2['pack']; ?>: <?php echo $product['pack']; ?>
					</li>
					<?php } ?>

					<?php if(isset($product['show'])) { ?>
					<?php if(isset($product['show']['box'])) { ?>
					<li style="display: list-item;list-style-position: outside;">
						<?php echo $ts2['pack']; ?>: <?php echo $product['show']['box'][0]; ?>
					</li>
					<?php } ?>
					<?php } ?>

					<?php if(isset($product['userComment'])) { ?>
					<li style="display: list-item;list-style-position: outside;">
						<?php echo $ts1['comments']; ?>: <?php echo $product['userComment']; ?>
					</li>
					<?php } ?>



					<br>
					<?php } ?>

					<?php  if (isset($order['delivery']['comment'])) { ?>
					<li style="display: list-item;list-style-position: outside;">
						<?php echo $ts1['comments']; ?>: <?php echo $order['delivery']['comment']; ?>
					</li>
					<?php } ?>

					<li style="display: list-item;list-style-position: outside;">
						<b><?php echo $ts3['data_zakaza']; ?>: <?php echo $now; ?></b>
					</li>

					<?php if(isset($order['delivery'])) : ?>
					<?php if($order['delivery']['when_send']) { ?>
					<?php if(strtotime($order['delivery']['when_send'])) { ?>
					<?php $when_send = new DateTime($order['delivery']['when_send']); ?>
					<li style="display: list-item;list-style-position: outside;">
						<b><?php echo $ts3['jel_dat_dost']; ?>: <?php echo $when_send->format('d.m.Y'); ?></b>
					</li>

					<?php
										 $end_date = strtotime($when_send->format('d.m.Y'));
										 $datediff = strtotime($order['created_at']) - $end_date;
										 $df = floor($datediff / (60 * 60 * 24));
										 $day_diff = str_replace('-', '', $df); 
								?>
					<?php if($day_diff<4) : ?>
					<li style="display: list-item;list-style-position: outside;">
						<b><?php echo $ts3['tip_zakaz_sr']; ?></b>
					</li>
					<?php else: ?>
					<li style="display: list-item;list-style-position: outside;">
						<b><?php echo $ts3['tip_zakaz_ob']; ?></b>
					</li>
					<?php endif; ?>
					<?php } ?>
					<?php } ?>

					<?php endif; ?>

				</ul>
			</td>
		</tr>
	</table>
</body>

</html>