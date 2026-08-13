
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru" xml:lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width">
<title></title>
</head>
<body style="position:relative;margin: 0 auto;">
	<link rel="stylesheet" href="https://use.typekit.net/aif1ths.css">
	<table width="100%" cellpadding="0" cellspacing="0" style="display: block; margin: 0 auto;font-family: 'Lato', sans-serif; max-width: 600px;position: relative;padding-left: 35px;padding-bottom: 95px;padding-top: 30px; border: 0;box-sizing: border-box;background: url('<?php echo $base_url; ?>/letters/bg.png');background-size: 100% 100%;background-repeat: no-repeat;margin-bottom: 100px;padding-right: 20px;border-radius: 19px;">
		<tr>
			<td>
				<p style="font-family:'Lato', sans-serif;"><?php echo $data['text']; ?></p>
			</td>
		</tr>
		<tr>
			<td>
				<?php if ($data['comment_images'] != null) { ?>

					<?php
                        $comm_image = trim($data['comment_images']);
                        $comm_image = explode(',', $comm_image);
                        foreach ($comm_image as $image) {
                            ?>
						<?php if ($image && $image != '') { ?>
							<a target="_blank" href="<?php echo $image; ?>" style="display:inline-block;margin-righ:20px;">
								<img src="<?php echo $image; ?>" alt="" style="width:100px;height:auto;max-height:100px;">
							</a>
						<?php } ?>
					<?php
                        } ?>
				<?php } ?>
			</td>
		</tr>
	</table>
</body>
</html>