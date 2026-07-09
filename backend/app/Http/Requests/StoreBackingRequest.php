<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'tier_id' => ['nullable', 'exists:campaign_tiers,id'],
            'amount' => ['required', 'numeric', 'min:10000'],
        ];
    }
}
