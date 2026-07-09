<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
