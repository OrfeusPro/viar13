<?php

namespace App\Services;

class ImageSaverService
{
    public function store($file, $path)
    {
        $filePath = $file->storeAs(
            $path,
            time() . '-' . $file->getClientOriginalName()
        );

        $filePath = str_replace('public/', '', $filePath);

        return $filePath;
    }

    public function delete($path)
    {
        $full_path = storage_path('app/public/' . str_replace('/storage/', '', $path));

        if (file_exists($full_path)) {
            unlink($full_path);
        }
    }

    public function base64Decode($image, $path)
    {
        $base64_str = substr($image, strpos($image, ',') + 1);

        $image = base64_decode($base64_str);

        $fileName = rand(1, 500000) . time() . '.png';

        \Storage::disk('local')->put($path . $fileName, $image);

        $filePath = str_replace('public/', '', $path);

        return $filePath . $fileName;
    }

    public function saveSvgImage($image, $path)
    {
        $fileName = rand(1, 500000) . time() . '.svg';

        \Storage::disk('local')->put($path . $fileName, $image);

        $filePath = str_replace('public/', '', $path);

        return $filePath . $fileName;
    }
}
