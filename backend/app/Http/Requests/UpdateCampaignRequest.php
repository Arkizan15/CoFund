<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $campaignId = $this->route('id');

        return [
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'title' => ['sometimes', 'required', 'string', 'max:100'],
            'slug' => [
                'nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('campaigns', 'slug')->ignore($campaignId),
            ],
            'description' => ['sometimes', 'required', 'string'],
            'target_amount' => ['sometimes', 'required', 'numeric', 'min:100000'],
            'deadline' => ['sometimes', 'required', 'date', 'after_or_equal:' . now()->addDays(7)->format('Y-m-d')],
            'video_url' => ['nullable', 'url'],
            'tiers' => ['nullable', 'array', 'min:1'],
            'tiers.*.id' => ['nullable', 'exists:campaign_tiers,id'],
            'tiers.*.name' => ['required', 'string', 'max:100'],
            'tiers.*.min_amount' => ['required', 'numeric', 'min:10000'],
            'tiers.*.quota' => ['nullable', 'integer', 'min:0'],
            'tiers.*.reward_description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
