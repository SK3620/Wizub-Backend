<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException as ValidationValidationException;

class TranslateSubtitlesRequest extends FormRequest
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
            'array_count' => 'required|integer',
            'subtitles' => 'required|array',
            'subtitles.*.subtitle_id' => 'required|integer',
            'subtitles.*.en_subtitle' => 'nullable|string',
            'subtitles.*.ja_subtitle' => 'nullable|string',
            'subtitles.*.memo' => 'nullable|string',
            'subtitles.*.start' => 'required|numeric',
            'subtitles.*.duration' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'array_count.required' => '英語字幕を格納する配列の要素数は必須です。',
            'array_count.integer' => '英語字幕を格納する配列の要素数は整数である必要があります。',
            'subtitles.required' => '字幕情報は必須です。',
            'subtitles.array' => '字幕情報は配列である必要があります。',
            'subtitles.*.subtitle_id.required' => '字幕IDは必須です。',
            'subtitles.*.subtitle_id.integer' => '字幕IDは整数である必要があります。',
            'subtitles.*.en_subtitle.string' => '英語字幕は文字列である必要があります。',
            'subtitles.*.ja_subtitle.string' => '日本語字幕は文字列である必要があります。',
            'subtitles.*.memo.string' => '学習メモは文字列である必要があります。',
            'subtitles.*.start.required' => '字幕開始時間は必須です。',
            'subtitles.*.start.numeric' => '字幕開始時間は数値である必要があります。',
            'subtitles.*.duration.required' => '字幕表示時間は必須です。',
            'subtitles.*.duration.numeric' => '字幕表示時間は数値である必要があります。',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationValidationException($validator);
    }
}
