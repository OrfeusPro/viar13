<?php

namespace App\Http\Controllers\Admin;

use App\Models\Orders;
use Illuminate\Http\Request;
use App\Models\OrderPainterImages;
use Intervention\Image\Facades\Image;
use Intervention\Image\Exception\NotReadableException;

class ImageGenController extends Controller
{

    public function __construct() {

    }

    public function image_gen_by_order_id($id, Request $request)
    {
        $overwrite = $request->overwrite ?? false; // Если параметр overwrite передан и он true, перезаписываем файлы

        if($id)
        {
            $order = Orders::where('id', $id)->first();
            // dd($order);
            if(!$order)
            {
                echo $id." - нет такого в базе<br>";
                return false;
            }

            $imageFields = ['painter_images', 'painter_sketch_images', 'client_images'];

            foreach ($imageFields as $field) {
                // Обработка каждого изображения
                $images = explode(",",$order->$field);

                foreach ($images as $image) {
                    if($image == "")
                    {
                        continue;
                    }

                    $originalImage = public_path($image); // Путь к оригинальному изображению
                    $originalImage = str_replace("https://viarcanvas.com/","",$originalImage);
                    $originalImage = str_replace("http://viarcanvas.com/","",$originalImage);
                    $originalImage = str_replace("\\","/",$originalImage);

                    // Проверяем, существует ли файл и доступен ли он для записи
                    if (!file_exists($originalImage) || !is_writable($originalImage)) {
                        // Можно добавить здесь логирование ошибок или пропустить файл
                        echo $originalImage. " - не могу конвертировать<br>";
                        continue;
                    }

                    // Получаем расширение и имя файла
                    $info = pathinfo($originalImage);
                    $fileName =  basename($originalImage,'.'.$info['extension']);

                    // Создаем новое имя для меньшего изображения
                    $smallImage = $info['dirname']."/".$fileName . '-small.' . $info['extension'];

                    // Проверяем, существует ли уже файл с меньшим изображением
                    if (file_exists($smallImage) && !$overwrite) {
                        // Можно добавить здесь логирование ошибок или пропустить файл
                        echo $smallImage. " - уже существует<br>";
                        continue;
                    }

                    // Попытка ресайза и сохранения изображения с новым именем
                    try {
                        $img = Image::make($originalImage);
                        $img->resize(null, 320, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($smallImage);
                    } catch (NotReadableException $e) {
                        // Обработка ошибки чтения изображения. Здесь можно добавить логирование ошибок или другие действия.
                        echo $originalImage. " - ошибка чтения файла<br>";
                        continue;
                    }
                }
            }

            return true;
        }
        else
        {
            return false;
        }

    }


    public function genSmallImages($id, Request $request)
    {
        $overwrite = $request->overwrite ?? false; // Если параметр overwrite передан и он true, перезаписываем файлы

        if($id)
        {
            $images = OrderPainterImages::where('small_image', null)->where('img_error', 0)->get();

            foreach ($images as $item) {

                $originalImage = public_path($item->image); // Путь к оригинальному изображению

                // Проверяем, существует ли файл и доступен ли он для записи
                if (!file_exists($originalImage) || !is_writable($originalImage)) {
                    // Можно добавить здесь логирование ошибок или пропустить файл
                    echo $originalImage. " - не могу конвертировать<br>";
                    $item->img_error = 1;
                    $item->save();
                    continue;
                }

                // Получаем расширение и имя файла
                $info = pathinfo($originalImage);
                $fileName =  basename($originalImage,'.'.$info['extension']);

                // Создаем новое имя для меньшего изображения
                $smallImage = $info['dirname']."/".$fileName . '-small.' . $info['extension'];
                $smallImage = str_replace("\\","/", $smallImage);

                $originaldirname = pathinfo($item->image);
                $originaldirname = $originaldirname['dirname'];
                $saveImageName = $originaldirname."/".$fileName . '-small.' . $info['extension'];

                // Проверяем, существует ли уже файл с меньшим изображением
                if (file_exists($smallImage) && !$overwrite) {
                    // Можно добавить здесь логирование ошибок или пропустить файл
                    echo $smallImage. " - уже существует<br>";
                    continue;
                }

                // Попытка ресайза и сохранения изображения с новым именем
                try {
                    $img = Image::make($originalImage);
                    $img->resize(null, 320, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($smallImage);

                    $item->small_image = $saveImageName;
                    $item->save();
                } catch (NotReadableException $e) {
                    // Обработка ошибки чтения изображения. Здесь можно добавить логирование ошибок или другие действия.
                    echo $originalImage. " - ошибка чтения файла<br>";
                    $item->img_error = 1;
                    $item->save();
                    continue;
                }
            }

            return true;
        }
        else
        {
            return false;
        }

    }

    //Cгенерировать все картигнки
    public function image_gen_all(Request $request)
    {
        // $this->image_gen_by_order_id("2999", $request);
        $this->genSmallImages("2999", $request);
    }
}