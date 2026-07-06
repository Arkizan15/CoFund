<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Campaign;
use App\Models\CampaignImage;
use App\Models\CampaignUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    public function index(): JsonResponse
    {
        $campaigns = Campaign::with(['category', 'user', 'images'])
            ->whereIn('status', ['active', 'success', 'failed'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ], 200);
    }

    public function show(string $slug): JsonResponse
    {
        $campaign = Campaign::with(['category', 'user', 'tiers', 'backings.user', 'updates', 'images'])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $campaign,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->role !== RoleEnum::CREATOR) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya creator yang dapat membuat kampanye.',
            ], 403);
        }

        if ($user->hasVerifiedEmail() === false) {
            return response()->json([
                'success' => false,
                'message' => 'Email harus diverifikasi sebelum membuat kampanye.',
            ], 403);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'target_amount' => ['required', 'numeric', 'min:100000'],
            'deadline' => ['required', 'date', 'after_or_equal:' . now()->addDays(7)->format('Y-m-d')],
            'video_url' => ['nullable', 'url'],
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        $validated['user_id'] = Auth::id();
        $validated['status'] = 'draft';

        $campaign = Campaign::create($validated);

        // Extract YouTube thumbnail and save as campaign image
        if (!empty($validated['video_url'])) {
            $thumbnailUrl = $this->extractYouTubeThumbnail($validated['video_url']);
            if ($thumbnailUrl) {
                CampaignImage::create([
                    'campaign_id' => $campaign->id,
                    'title' => 'Video Thumbnail',
                    'image_url' => $thumbnailUrl,
                    'is_primary' => true,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Kampanye berhasil dibuat sebagai draft.',
            'data' => $campaign,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Anda bukan pemilik kampanye ini.',
            ], 403);
        }

        if ($campaign->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Kampanye hanya bisa diedit saat status draft.',
            ], 422);
        }

        $validated = $request->validate([
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'title' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['sometimes', 'required', 'string'],
            'target_amount' => ['sometimes', 'required', 'numeric', 'min:100000'],
            'deadline' => ['sometimes', 'required', 'date', 'after_or_equal:' . now()->addDays(7)->format('Y-m-d')],
            'video_url' => ['nullable', 'url'],
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        }

        $campaign->update($validated);

        // Handle thumbnail for video_url
        if (!empty($validated['video_url'])) {
            $thumbnailUrl = $this->extractYouTubeThumbnail($validated['video_url']);
            if ($thumbnailUrl) {
                // Remove old video thumbnail if exists
                $campaign->images()
                    ->where('title', 'Video Thumbnail')
                    ->delete();

                CampaignImage::create([
                    'campaign_id' => $campaign->id,
                    'title' => 'Video Thumbnail',
                    'image_url' => $thumbnailUrl,
                    'is_primary' => true,
                ]);
            }
        } elseif ($campaign->wasChanged('video_url')) {
            // video_url was cleared, remove the old thumbnail
            $campaign->images()
                ->where('title', 'Video Thumbnail')
                ->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Kampanye berhasil diperbarui.',
            'data' => $campaign,
        ], 200);
    }

    /**
     * Extract YouTube video thumbnail URL from various YouTube URL formats.
     */
    private function extractYouTubeThumbnail(string $url): ?string
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/',
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return 'https://img.youtube.com/vi/' . $matches[1] . '/maxresdefault.jpg';
            }
        }

        return null;
    }

    public function uploadImage(Request $request, int $id): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $file = $request->file('image');
        $filename = 'campaign_' . $campaign->id . '_' . time() . '.' . $file->extension();
        $path = $file->storeAs('public/campaigns', $filename);

        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah gambar.',
            ], 500);
        }

        $imageUrl = Storage::url($path);

        // Remove old primary image (non-thumbnail)
        $campaign->images()
            ->where('is_primary', true)
            ->where('title', '!=', 'Video Thumbnail')
            ->delete();

        $campaignImage = CampaignImage::create([
            'campaign_id' => $campaign->id,
            'title' => 'Uploaded Image',
            'image_url' => $imageUrl,
            'is_primary' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gambar berhasil diunggah.',
            'data' => $campaignImage,
        ], 201);
    }

    public function deleteImage(int $id): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($campaign->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Gambar hanya bisa dihapus saat status draft.',
            ], 422);
        }

        // Find and delete the primary non-thumbnail image
        $image = $campaign->images()
            ->where('is_primary', true)
            ->where('title', '!=', 'Video Thumbnail')
            ->first();

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada gambar yang dapat dihapus.',
            ], 404);
        }

        // Delete physical file if it's a local upload (not external URL)
        $localPath = str_replace('/storage/', 'public/', $image->image_url);
        if (Storage::exists($localPath)) {
            Storage::delete($localPath);
        }

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gambar berhasil dihapus.',
        ], 200);
    }

    public function submitForReview(int $id): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($campaign->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya kampanye draft yang bisa disubmit.',
            ], 422);
        }

        $campaign->status = 'review';
        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => 'Kampanye dikirim ke admin untuk direview.',
            'data' => $campaign,
        ], 200);
    }

    public function myCampaigns(): JsonResponse
    {
        $campaigns = Campaign::with(['category'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ], 200);
    }

    public function storeUpdate(Request $request, int $id): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);
        $user = Auth::user();

        if ($campaign->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pemilik kampanye yang dapat menambahkan update.',
            ], 403);
        }

        if (!in_array($campaign->status, ['active', 'review'])) {
            return response()->json([
                'success' => false,
                'message' => 'Update hanya dapat ditambahkan pada kampanye aktif atau dalam review.',
            ], 422);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string'],
        ]);

        $update = CampaignUpdate::create([
            'campaign_id' => $campaign->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Update berhasil ditambahkan.',
            'data' => $update,
        ], 201);
    }
}
