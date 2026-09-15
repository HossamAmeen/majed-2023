<?php

namespace App\Http\Requests\BackEnd;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        //'user_name' , 'full_name','email', 'phone' ,'password',
        return [
            'user_name' => ['required', 'string'],
            'full_name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string'],
            'image' => [ 'image'],
        ];
    }
}
