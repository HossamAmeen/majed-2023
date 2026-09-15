<?php

namespace App\Http\Requests\BackEnd;

use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
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
        return [
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
            'en_question' => ['required', 'string'],
            'en_answer' => ['required', 'string'],
        ];
    }
}
