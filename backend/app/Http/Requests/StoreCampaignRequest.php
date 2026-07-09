<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:campaigns,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['required', 'string'],
            'target_amount' => ['required', 'numeric', 'min:100000'],
            'deadline' => ['required', 'date', 'after_or_equal:' . now()->addDays(7)->format('Y-m-d')],
            'video_url' => ['nullable', 'url'],
            'tiers' => ['required', 'array', 'min:1'],
            'tiers.*.name' => ['required', 'string', 'max:100'],
            'tiers.*.min_amount' => ['required', 'numeric', 'min:10000'],
            'tiers.*.quota' => ['nullable', 'integer', 'min:0'],
            'tiers.*.reward_description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
