<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException as ValidationValidationException;

class CheckVideoAlreadySavedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'video_id' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'video_id.required' => '動画IDは必須です。',
            'video_id.string' => '動画IDは文字列である必要があります。',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationValidationException($validator);
    }
}
