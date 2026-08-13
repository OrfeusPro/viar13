<?php

namespace App\Helpers;

use App;
use App\Models\Orders;
use App\Models\GlobConfig;

class UserFormHelper
{
    public static function send_photo_form_helper($request)
    {
        $data['content'] = '';
        $admin_data = GlobConfig::first()->get()->translate(App::getLocale(), 'ru')[0];
        $admin_data_mail = $admin_data['admin_email'];

        $data['to'] = $admin_data_mail;
        $data['subject'] = 'Расчет портрета';

        $path = '';

        if ($request->images) {
            $imageName =  'N-' . Orders::getNextId() . '.' . $request->images->extension();
            $request->images->move(public_path('uploads'), $imageName);
            $path = 'https://viarcanvas.com/uploads/' . $imageName;
        }

        if ($request->email) {
            $data['content'] = "Email: {$request->email} <br>";
        }

        if ($request->phone) {
            $data['content'] .= "Phone: {$request->phone} <br>";
        }

        if ($request->step0 && $request->step0 != 'null') {
            $data['content'] .= "Событие: {$request->step0} <br>";
        }

        if ($request->step1) {
            $data['content'] .= "Для кого: {$request->step1} <br>";
        }

        if ($request->step2) {
            $data['content'] .= "Сумма: {$request->step2} <br>";
        }

        if ($request->step3) {
            $data['content'] .= "Стиль: {$request->step3} <br>";
        }

        if ($request->dest && $request->dest != 'null') {
            $data['content'] .= "Расчет: {$request->dest} <br>";
        }

        if ($path && $path != '') {
            $data['content'] .= "Image: {$path}";
        }

        return $data;
    }

    public static function send_all_styles_form_helper($request)
    {
        $path = '';

        if (!$request->form_name && $request->images) {
            $imageName = time() . '.' . $request->images->extension();
            $request->images->move(public_path('uploads'), $imageName);
            $path = 'https://viarcanvas.com/uploads/' . $imageName;
        }

        $i=1;
        $files_names = [];

        if ($request->form_name) {
            $files = $request->file('images');

			if($files)
			{
				foreach ($files as $file) {
					$imageName = time()."_".$i . '.' .$file->extension();
					$file->move(public_path('uploads'), $imageName);
					$files_names[] = 'https://viarcanvas.com/uploads/' . $imageName;
					$i++;
				}
			}
        }

        $files_2 = $request->file('file');

        if($files_2)
        {
            foreach ($files_2 as $file) {
                $imageName = time()."_".$i . '.' .$file->extension();
                $file->move(public_path('uploads'), $imageName);
                $files_names[] = 'https://viarcanvas.com/uploads/' . $imageName;
                $i++;
            }
        }



        // данные
        $admin_data = GlobConfig::first()->get()->translate(App::getLocale(), 'ru')[0];
        $admin_data_mail = $admin_data['admin_email'];

        // письмо
        $data['to'] = $admin_data_mail;

        if ($request->form_name) {
            $data['subject'] = $request->form_name;
        } else {
            $data['subject'] = 'Форма фото - 15 минут';
        }

        $data['content'] = '';

        if ($request->email) {
            $data['content'] .= "Email: {$request->email} <br>";
        }

        if ($request->phone) {
            $data['content'] .= "Phone: {$request->phone} <br>";
        }

        if ($request->comments) {
            $data['content'] .= "Comment: {$request->comments} <br>";
        }

        $data['orig_images'] = '';
		if($files_names){
			foreach ($files_names as $file)
			{
				$data['content'] .= $file."<br>";
			}
            $data['orig_images'] = $files_names;
		}

        if (!$request->form_name)
        {
            if ($path)
            {
                $data['content'] .= "Image: {$path} <br>";
            }
        }else{
            if($files_names){
                foreach ($files_names as $file)
                {
                    $data['content'] .= $file."<br>";
                }
            }
        }

        return $data;
    }

    public static function send_photo_portrait_form_helper($request)
    {
        // данные
        $admin_data = GlobConfig::first()->get()->translate(App::getLocale(), 'ru')[0];
        $admin_data_mail = $admin_data['admin_email'];

        $type = 'Портрет';

        if ($request->price) {
            $type = 'Канвас';
        }

        // письмо
        $data['to'] = $admin_data_mail;
        $data['subject'] = $type . " по фото";
        $data['content'] = '';

        if ($request->email) {
            $data['content'] .= "Email: {$request->email} <br>";
        }

        if ($request->phone) {
            $data['content'] .= "Phone: {$request->phone} <br>";
        }

        if ($request->new_size && $request->new_price) {
            $data['content'] .= "Size: {$request->new_size} ";
            $data['content'] .= "- {$request->new_price} <br>";
        }
        else if ($request->size) {
            $data['content'] .= "Size: {$request->size} <br>";
        }

        if ($request->new_people_count && $request->new_people_count_price) {
            $data['content'] .= "Number of persons: {$request->new_people_count} ";
            $data['content'] .= "+ {$request->new_people_count_price} <br>";
        }

        if ($request->styles) {
            $data['content'] .= "Style: {$request->styles} <br>";
        }

        if ($request->box) {
            $data['content'] .= "Box: {$request->box} <br>";
        }

		//dd( $request->file, $request->file->extension());
		/*
        if ($request->file) {
            $imageName = time() . '.' . $request->file->extension();
            $request->file->move(public_path('uploads'), $imageName);
            $path = 'https://viarcanvas.com/uploads/' . $imageName;
        }*/

        $files = $request->file('file');
        $files_names = [];
        $i = 1;

        if ($files) {
            foreach ($files as $file) {
                $imageName = time() . "_" . $i . '.' . $file->extension();
                $file->move(public_path('uploads'), $imageName);
                $files_names[] = 'https://viarcanvas.com/uploads/' . $imageName;
                $i++;
            }
        }

		$data['orig_images'] = '';
		if($files_names){
			foreach ($files_names as $file)
			{
				$data['content'] .= $file."<br>";
			}
            $data['orig_images'] = $files_names;
		}

        /* if ($request->file) {
            $imageName1 = time() . uniqid() . '.' . $request->file->extension();
            $request->file->move(public_path('uploads'), $imageName1);
            $path = 'https://viarcanvas.com/uploads/' . $imageName1;
            $data['content'] .= "Image: {$path} <br>";
        }

        if ($request->file2) {
            $imageName2 = time() . uniqid() . '.' . $request->file2->extension();
            $request->file2->move(public_path('uploads'), $imageName2);
            $path = 'https://viarcanvas.com/uploads/' . $imageName2;
            $data['content'] .= "Image: {$path} <br>";
        }

        if ($request->file3) {
            $imageName3 = time() . uniqid() . '.' . $request->file3->extension();
            $request->file3->move(public_path('uploads'), $imageName3);
            $path = 'https://viarcanvas.com/uploads/' . $imageName3;
            $data['content'] .= "Image: {$path} <br>";
        }

        if ($request->file4) {
            $imageName4 = time() . uniqid() . '.' . $request->file4->extension();
            $request->file4->move(public_path('uploads'), $imageName4);
            $path = 'https://viarcanvas.com/uploads/' . $imageName4;
            $data['content'] .= "Image: {$path} <br>";
        }

        if ($request->file5) {
            $imageName5 = time() . uniqid() . '.' . $request->file5->extension();
            $request->file5->move(public_path('uploads'), $imageName5);
            $path = 'https://viarcanvas.com/uploads/' . $imageName5;
            $data['content'] .= "Image: {$path} <br>";
        }

        if ($request->file6) {
            $imageName6 = time() . uniqid() . '.' . $request->file6->extension();
            $request->file6->move(public_path('uploads'), $imageName6);
            $path = 'https://viarcanvas.com/uploads/' . $imageName6;
            $data['content'] .= "Image: {$path} <br>";
        }

        if ($request->file7) {
            $imageName7 = time() . uniqid() . '.' . $request->file7->extension();
            $request->file7->move(public_path('uploads'), $imageName7);
            $path = 'https://viarcanvas.com/uploads/' . $imageName7;
            $data['content'] .= "Image: {$path} <br>";
        }

        if ($request->file8) {
            $imageName8 = time() . uniqid() . '.' . $request->file8->extension();
            $request->file8->move(public_path('uploads'), $imageName8);
            $path = 'https://viarcanvas.com/uploads/' . $imageName8;
            $data['content'] .= "Image: {$path} <br>";
        }

        if ($request->file9) {
            $imageName9 = time() . uniqid() . '.' . $request->file9->extension();
            $request->file9->move(public_path('uploads'), $imageName9);
            $path = 'https://viarcanvas.com/uploads/' . $imageName9;
            $data['content'] .= "Image: {$path} <br>";
        }

        if ($request->file10) {
            $imageName10 = time() . uniqid() . '.' . $request->file10->extension();
            $request->file10->move(public_path('uploads'), $imageName10);
            $path = 'https://viarcanvas.com/uploads/' . $imageName10;
            $data['content'] .= "Image: {$path} <br>";
        }*/

        return $data;
    }
}
