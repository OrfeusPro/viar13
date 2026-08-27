<?php

namespace App\Services;

use URL;
use File;
use Storage;
use RuntimeException;
use App\Models\Orders;
use App\Models\OrderPainterImages;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdatePainterSketchImageService {
    public function store($request)
    {
        $order = Orders::where('id', $request->order_id)->get()->first();
		$new_fname = Orders::getOrderImageName($order, 'sketch', false, $request->order_id, false);
        $data['orig_images'] = [];

        Validator::make($request->all(), [
            'painter_sketch_images' => ['required', 'array', 'min:1'],
            'painter_sketch_images.*' => ['required', 'file', 'mimes:png,bmp,jpg,jpeg,psd,heic,heif,fig,pdf'],
        ])->validate();

        if ($request->hasFile('painter_sketch_images'))
        {
            $files = $request->file('painter_sketch_images');

            $j = -1;
            foreach ($files as $file)
            {
                $j++;

				$file_extension = mb_strtolower(File::extension($file->getClientOriginalName()));

                try
                {
					$file_name = Storage::disk('uploads')->putFileAs('orders', $file, $new_fname."_".$j.".".$file_extension);
                   // $file_name = Storage::disk('uploads')->put('uploads', $file);
                } catch (RunTimeException $e)
                {
                    throw ValidationException::withMessages([
                        'painter_sketch_images' => 'Unable to store the uploaded sketch.',
                    ]);
                }

                $data['orig_images'][$j] = URL::to('/').'/'.$file_name;
                OrderPainterImages::insert([
                    'order_id' => $request->order_id,
                    'image' => $file_name,
                    'is_img_sketch' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return implode(', ', $data['orig_images']);
        }

        return null;
    }
}
