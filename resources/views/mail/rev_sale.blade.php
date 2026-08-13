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
		style="display:block;max-width: 600px;margin: 0 auto;font-family: 'Lato', sans-serif; display: flex;max-width: 600px;position: relative;padding-left: 35px;padding-bottom: 95px;padding-top: 30px; border: 0;box-sizing: border-box;background: url('<?php echo $base_url; ?>/letters/bg3.png');background-size: 100% 100%;background-repeat: no-repeat;border-radius:19px; margin-bottom: 100px;padding-right:20px;">
		<tr>
			<td style="width: 100px;">
				<img src="<?php echo $base_url; ?>/letters/logo.png" alt="logo" style="margin-bottom: 15px;">
			</td>
		</tr>
		<tr>
			<td>
				<h1
					style="font-family:'Lato', sans-serif;font-size:30px;margin-top: 10px;font-weight: 900;margin-bottom: 20px;color: #a0565f;">
					<?php echo $data['sale_30_40_title']; ?></h1>
			</td>
		</tr>
		<tr>
			<td style="font-size: 18px; font-weight: 900; color: #000;">
				<?php echo $data['sale_30_40_text']; ?> <?php echo $coupon_code; ?>
			</td>
		</tr>
		<tr>
			<td>
				<a href="<?php echo $base_url; ?>" style="margin-bottom:5px;position:relative;display:block;position: relative;height: 70px;width: 240px;text-decoration: none;text-align: center;
					background:url(<?php echo $base_url; ?>/img/collages-link.png) 100% 100% no-repeat;background-size:contain;">
					<span style="font-size:18px;color: #a0565f;position:relative;z-index:2;
					color: #fff;display:block;padding-top: 21px;text-align: center;display: block;">
						<?php echo $data['sale_30_40_btn']; ?>
					</span>
				</a>
			</td>
		</tr>
	</table>
</body>

</html>