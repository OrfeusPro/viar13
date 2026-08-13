<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderPainterImages;
use Intervention\Image\Facades\Image;
use Intervention\Image\Exception\NotReadableException;


class GenSmallImages extends Command
{
    /**

     * The name and signature of the console command.

     *

     * @var string

     */
    protected $signature = 'GenSmallImages:run';

    /**

     * The console command description.

     *

     * @var string

     */
    protected $description = 'Generate small images cron task';

    /**

     * Create a new command instance.

     *

     * @return void

     */
    public function __construct()
    {
        parent::__construct();
    }

    /**

     * Execute the console command.

     *

     * @return mixed

     */

    public function handle()
    {
        $images = OrderPainterImages::where('small_image', null)->where('img_error', 0)->get();
        $overwrite = 1;
        $make = 0;
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

                $make++;
            } catch (NotReadableException $e) {
                // Обработка ошибки чтения изображения. Здесь можно добавить логирование ошибок или другие действия.
                echo $originalImage. " - ошибка чтения файла<br>";
                $item->img_error = 1;
                $item->save();
                continue;
            }
        }
        echo "Created images: ".$make;
        echo "\r\nDone";

    }
}
