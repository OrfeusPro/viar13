<?php

namespace App\Services;

use Storage;
use URL;
use Illuminate\Support\Facades\Validator;

class SendClientPainterImageService {

    public function store($request, bool $appendToClientImages = true)
    {
            $validator = Validator::make($request->all(), [
                'client_images.*' => 'image|mimes:jpeg,png,jpg,gif,png,pdf,fig,heic,heif|max:5048',
            ]);

            if ($validator->fails())
            {
                return redirect()->back()->withErrors([
                    'images' => 'Invalid filesize or extension.',
                ]);
            }

            $new_client_images_urls = '';
            $files = $request->file('client_images');
            $client_images_old = '';

            if ($appendToClientImages) {
                $client_images_old = (string) \DB::table('orders')
                    ->where('id', intval($request->order_id))
                    ->pluck('client_images')
                    ->first();
            }

            foreach ($files as $file)
            {
                $file_name = Storage::disk('uploads')->put('uploads', $file);
                $new_client_images_urls .= URL::to('/').'/'.$file_name.',';
            }

            $new_client_images_urls = rtrim($new_client_images_urls, ',');
            $existing_client_images = trim($client_images_old, ',');
            $img_urls = $appendToClientImages
                ? trim(implode(',', array_filter([$new_client_images_urls, $existing_client_images])), ',')
                : $existing_client_images;

            return [
                'comment_images' => $new_client_images_urls,
                'client_images_urls' => $img_urls
            ];
    }

}
