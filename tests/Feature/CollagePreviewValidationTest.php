<?php

namespace Tests\Feature;

use App\Http\Requests\ConstructBasketRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CollagePreviewValidationTest extends TestCase
{
    public function test_collage_generated_preview_is_accepted_alongside_its_main_image(): void
    {
        $input = ['name'=>'Collage', 'price'=>50, 'size'=>'40x50', 'image'=>'cHJldmlldw==', 'image_offset'=>'aW1hZ2U='];
        $request = ConstructBasketRequest::create('/basket/add/construct', 'POST', $input);
        $validator = Validator::make($input, $request->rules());
        $request->withValidator($validator);
        $this->assertTrue($validator->passes(), $validator->errors()->toJson());

        unset($input['image_offset']);
        $request = ConstructBasketRequest::create('/basket/add/construct', 'POST', $input);
        $validator = Validator::make($input, $request->rules());
        $request->withValidator($validator);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('image_offset'));
    }

    public function test_original_file_mode_still_requires_a_real_upload(): void
    {
        $input = ['name'=>'Collage', 'price'=>50, 'size'=>'40x50', 'is_orig_file'=>1, 'image'=>'aW1hZ2U=', 'collageSvgImage_hash'=>md5('source')];
        $request = ConstructBasketRequest::create('/basket/add/construct', 'POST', $input);
        $validator = Validator::make($input, $request->rules());
        $request->withValidator($validator);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('image'));

        $input['image'] = UploadedFile::fake()->image('source.png');
        $request = ConstructBasketRequest::create('/basket/add/construct', 'POST', $input, [], ['image'=>$input['image']]);
        $validator = Validator::make($input, $request->rules());
        $request->withValidator($validator);
        $this->assertTrue($validator->passes(), $validator->errors()->toJson());
    }
}
