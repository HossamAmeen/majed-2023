<?php

namespace App\Http\Requests\BackEnd;

use Illuminate\Foundation\Http\FormRequest;

class ClienicRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // 'address','en_address','phone','en_phone','appointment','en_appointment'
        return [
            'address' => ['required', 'string'],
            'en_address' => ['required', 'string'],
            'phone' => ['required', 'string'],
            
            'appointment' => ['required', 'string'],
            'en_appointment' => ['required', 'string'],
        ];
    }
}
