<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru" xml:lang="ru">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width">
	<title></title>
</head>

<body style="margin: 0 auto;">
	<link rel="stylesheet" href="https://use.typekit.net/aif1ths.css">
	<table width="100%" cellpadding="0" cellspacing="0"
		style="display: block;max-width: 600px;margin: 0 auto;font-family: 'Lato', sans-serif;max-width: 600px;position: relative;padding-left: 35px;margin-bottom: 100px;;padding-bottom: 65px;padding-top: 30px; border: 0;box-sizing: border-box;background: url('<?php echo $base_url; ?>/letters/bg2.png');background-repeat: no-repeat;background-size: 100% 100%;border-radius: 19px;padding-left: 20px;">
		<tr style="width: 100%;" class="fix-tr">
			<td class="logo-right" style="max-width:85px;">
				<img src="<?php echo $base_url; ?>/letters/logo.png" alt="logo" style="margin-bottom: 15px;">
			</td>
			<td style="margin-top: 20px;text-align: right;white-space: nowrap;">
				<a href="<?php echo $data['rev_fb']; ?>">
					<img src="<?php echo $base_url; ?>/letters/facebook.png" alt="instagram" height="31px"
						style="margin-right: 14px;">
				</a>
				<a href="<?php echo $data['rev_vk']; ?>">
					<img src="<?php echo $base_url; ?>/letters/vk.png" alt="vk" height="22px"
						style="margin-bottom:5px;margin-right: 14px;">
				</a>
				<a href="<?php echo $data['rev_ig']; ?>">
					<img src="<?php echo $base_url; ?>/letters/instagram.png" alt="instagram" height="31px"
						style="margin-right: 14px;">
				</a>
				<a href="<?php echo $data['rev_yb']; ?>">
					<img src="<?php echo $base_url; ?>/letters/youtube.png" alt="instagram" height="21px"
						style="margin-bottom:5px;margin-right: 30px;">
				</a>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<h1
					style="font-family:'Lato', sans-serif;font-size:30px;margin-top: 10px;font-weight: 900;margin-bottom: 0px;">
					<?php echo $data['rev_title']; ?></h1>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="font-size: 18px; font-weight: 900; color: #a0565f;">
				<?php echo $data['rev_sub_title']; ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<p
					style="font-size: 16px; font-weight: 400;line-height: 30px;margin-top: 20px;margin-bottom: 40px;padding-right: 30px;width: 90%;">
					<?php echo $data['rev_mail']; ?>
				</p>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<a href="<?php echo $data['rev_lang_url']; ?>" style="margin-bottom:5px;position:relative;display:block;position: relative;height: 70px;width: 240px;text-decoration: none;text-align: center;
					background:url(<?php echo $base_url; ?>/img/collages-link.png) 100% 100% no-repeat;background-size:contain;">
					<span style="font-size:18px;color: #a0565f;position:relative;z-index:2;
					color: #fff;display:block;padding-top: 21px;text-align: center;display: block;">
						<?php echo $data['rev_btn_text']; ?>
					</span>
				</a>

			</td>
		</tr>
		<tr style="align-items: center; padding-top: 50px;">
			<td style="max-width: 110px;">
				<img src="<?php echo $base_url; ?>/letters/avatar.png" alt="avatar"
					style="margin-right:15px;width: 100%;">
			</td>
			<td>
				<table class="small-table">
					<tr>
						<td style="font-size: 16px;font-weight: 400;line-height: 30px;margin-top: 10px;">
							<?php echo $data['rev_thx_text']; ?>
						</td>
					</tr>
					<tr>
						<td style="font-weight: 900;">
							<?php echo $data['rev_after_txt_user']; ?>
						</td>
					</tr>
					<tr>
						<td style="margin-top: 10px;align-items: center;">
							<img src="<?php echo $base_url; ?>/letters/mail.png" alt="" style="margin-top: 3px;">
							<?php echo $data['rev_mai']; ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>

</html>