<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:10000'],
            'success_redirect_url' => ['nullable', 'url'],
            'failure_redirect_url' => ['nullable', 'url'],
        ];
    }
}
