<?php

namespace App\Http\Requests;

use App\Entity\BasketType;
use Illuminate\Foundation\Http\FormRequest;

class BasketStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [

            'basketType' => 'required',

        ];

        if (!($this->request->get('basketType'))) {
            return $rules;
        }

        switch ($this->request->get('basketType')) {

            case BasketType::CANVAS_TYPE:

                $rules = [

                    'basketType' => 'required',

                    'userImage' => 'required|image',

                    'formId' => 'required',

                    'sizeId' => 'required',

                    // 'effectId' => 'required',

                    'executionId' => 'required',

                    'canvasId' => 'required',

                ];

                break;

            case BasketType::MODULAR_PICTURES_TYPE:

                $rules = [

                    'basketType' => 'required',

                    'size' => 'required',

                    'executionId' => 'required',

                    'canvasId' => 'required',

                ];

                break;

            case BasketType::COLLAGE_TYPE:

                $rules = [

                    'basketType' => 'required',

                    'allImages' => 'required',

                    'finalImg' => 'required',

                    'sizeId' => 'required',

                    'canvasId' => 'required',

                ];

                break;

            default:

                break;

        }

        return $rules;
    }
}
