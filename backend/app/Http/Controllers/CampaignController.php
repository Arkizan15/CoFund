<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class CampaignController extends Controller
{
    public function index(): JsonResponse
    {
        $campaigns = Campaign::with(['category', 'user'])->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $campaigns
        ], 200);
    }

    public function show(string $slug): JsonResponse
    {
        $campaign = Campaign::with(['category', 'user'])->where('slug', $slug)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $campaign
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'target_amount' => ['required', 'numeric', 'min:100000'],
            'deadline' => ['required', 'date', 'after_or_equal:+7 days'],
            'video_url' => ['nullable', 'url'],
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        $validated['user_id'] = Auth::id();
        $validated['status'] = 'draft';

        $campaign = Campaign::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Campaign created successfully as draft.',
            'data' => $campaign
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not own this campaign.'
            ], 403);
        }

        if ($campaign->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Campaign can only be updated when status is still draft.'
            ], 422);
        }

        $validated = $request->validate([
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'target_amount' => ['sometimes', 'required', 'numeric', 'min:100000'],
            'deadline' => ['sometimes', 'required', 'date', 'after_or_equal:+7 days'],
            'video_url' => ['nullable', 'url'],
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        }

        $campaign->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Campaign updated successfully.',
            'data' => $campaign
        ], 200);
    }
}