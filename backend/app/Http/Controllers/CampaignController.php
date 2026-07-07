<?php

namespace App\Http\Controllers;

use App\Enums\BackingStatus;
use App\Enums\CampaignStatus;
use App\Enums\RoleEnum;
use App\Mail\NotifikasiEmail;
use App\Models\Backing;
use App\Models\Campaign;
use App\Models\CampaignImage;
use App\Models\CampaignTier;
use App\Models\CampaignUpdate;
use App\Models\Notification;
use App\Services\CampaignSettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->settleExpiredCampaigns();

        $query = Campaign::with(['category', 'user', 'images']);

        $status = $request->query('status');
        if ($status && in_array($status, CampaignStatus::values())) {
            $query->where('status', $status);
        } else {
            $query->where('status', CampaignStatus::ACTIVE);
        }

        $sort = $request->query('sort', 'newest');
        if ($sort === 'popular') {
            $query->orderBy('collected_amount', 'desc');
        } elseif ($sort === 'ending_soon') {
            $query->orderBy('deadline', 'asc');
        } else {
            $query->latest();
        }

        $campaigns = $query->get();

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ], 200);
    }

    private function settleExpiredCampaigns(): void
    {
        $result = (new CampaignSettlementService())->settleExpiredCampaigns();
        if ($result['success'] > 0 || $result['failed'] > 0) {
            \Illuminate\Support\Facades\Log::info('Auto-settled expired campaigns on list access: ' .
                $result['success'] . ' disbursed, ' . $result['failed'] . ' refunded.');
        }
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
        ]);

        // Handle slug: if not provided, auto-generate from title
        if (!empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['slug']);
        } else {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        }

        $validated['user_id'] = Auth::id();
        $validated['status'] = CampaignStatus::DRAFT->value;

        $campaign = Campaign::create($validated);

        // Create tiers
        $tiers = $validated['tiers'] ?? [];
        foreach ($tiers as $tierData) {
            CampaignTier::create([
                'campaign_id' => $campaign->id,
                'name' => $tierData['name'],
                'min_amount' => $tierData['min_amount'],
                'quota' => $tierData['quota'] ?? 0,
                'remaining_quota' => $tierData['quota'] ?? 0,
                'reward_description' => $tierData['reward_description'] ?? null,
            ]);
        }

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

        // Reload with tiers
        $campaign->load('tiers');

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

        if ($campaign->status !== CampaignStatus::DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Kampanye hanya bisa diedit saat status draft.',
            ], 422);
        }

        $validated = $request->validate([
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'title' => ['sometimes', 'required', 'string', 'max:100'],
            'slug' => [
                'nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:campaigns,slug,' . $campaign->id,
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
        ]);

        // Handle slug: if provided, manual slug; else auto-generate from title
        if (array_key_exists('slug', $validated)) {
            if (!empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['slug']);
            } elseif (isset($validated['title'])) {
                $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
            }
            // If slug is empty string and title not changed, don't update slug
        } elseif (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        }

        $campaign->update($validated);

        // Handle tiers update if provided
        if ($request->has('tiers')) {
            $existingTierIds = $campaign->tiers()->pluck('id')->toArray();
            $incomingTierIds = [];

            foreach ($validated['tiers'] as $tierData) {
                if (isset($tierData['id'])) {
                    // Update existing tier
                    $tier = CampaignTier::findOrFail($tierData['id']);
                    if ($tier->campaign_id === $campaign->id) {
                        $tier->update([
                            'name' => $tierData['name'],
                            'min_amount' => $tierData['min_amount'],
                            'quota' => $tierData['quota'] ?? $tier->quota,
                            'reward_description' => $tierData['reward_description'] ?? $tier->reward_description,
                        ]);
                        // Only update remaining_quota if quota changed
                        if (isset($tierData['quota']) && $tierData['quota'] != $tier->quota) {
                            $diff = $tierData['quota'] - $tier->quota;
                            $tier->remaining_quota = max(0, $tier->remaining_quota + $diff);
                            $tier->save();
                        }
                        $incomingTierIds[] = $tierData['id'];
                    }
                } else {
                    // Create new tier
                    $newTier = CampaignTier::create([
                        'campaign_id' => $campaign->id,
                        'name' => $tierData['name'],
                        'min_amount' => $tierData['min_amount'],
                        'quota' => $tierData['quota'] ?? 0,
                        'remaining_quota' => $tierData['quota'] ?? 0,
                        'reward_description' => $tierData['reward_description'] ?? null,
                    ]);
                    $incomingTierIds[] = $newTier->id;
                }
            }

            // Delete tiers that were removed
            $tiersToDelete = array_diff($existingTierIds, $incomingTierIds);
            if (!empty($tiersToDelete)) {
                CampaignTier::whereIn('id', $tiersToDelete)->delete();
            }
        }

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
                    'is_primary' => $campaign->images()->where('is_primary', true)->count() === 0,
                ]);
            }
        } elseif ($campaign->wasChanged('video_url')) {
            // video_url was cleared, remove the old thumbnail
            $campaign->images()
                ->where('title', 'Video Thumbnail')
                ->delete();
        }

        // Reload with tiers
        $campaign->load('tiers');

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
            '/youtube\\.com\\/watch\\?v=([a-zA-Z0-9_-]{11})/',
            '/youtu\\.be\\/([a-zA-Z0-9_-]{11})/',
            '/youtube\\.com\\/embed\\/([a-zA-Z0-9_-]{11})/',
            '/youtube\\.com\\/shorts\\/([a-zA-Z0-9_-]{11})/',
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

        // Check max images (5 max, excluding video thumbnail)
        $currentImageCount = $campaign->images()
            ->where('title', '!=', 'Video Thumbnail')
            ->count();

        if ($currentImageCount >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Maksimal 5 gambar per kampanye.',
            ], 422);
        }

        $file = $request->file('image');
        $filename = 'campaign_' . $campaign->id . '_' . time() . '_' . uniqid() . '.' . $file->extension();
        $path = $file->storeAs('public/campaigns', $filename);

        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah gambar.',
            ], 500);
        }

        $imageUrl = Storage::url($path);

        // Set as primary if it's the first real image
        $isPrimary = $campaign->images()
            ->where('is_primary', true)
            ->where('title', '!=', 'Video Thumbnail')
            ->count() === 0;

        $campaignImage = CampaignImage::create([
            'campaign_id' => $campaign->id,
            'title' => 'Uploaded Image',
            'image_url' => $imageUrl,
            'is_primary' => $isPrimary,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gambar berhasil diunggah.',
            'data' => $campaignImage,
        ], 201);
    }

    public function setPrimaryImage(int $id, int $imageId): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $image = $campaign->images()->where('title', '!=', 'Video Thumbnail')->findOrFail($imageId);

        // Remove primary from all other images
        $campaign->images()
            ->where('is_primary', true)
            ->where('title', '!=', 'Video Thumbnail')
            ->update(['is_primary' => false]);

        $image->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Gambar utama berhasil diubah.',
            'data' => $image,
        ], 200);
    }

    public function deleteImage(int $id, ?int $imageId = null): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($campaign->status !== CampaignStatus::DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Gambar hanya bisa dihapus saat status draft.',
            ], 422);
        }

        // If specific image ID is provided, delete that one
        if ($imageId) {
            $image = $campaign->images()
                ->where('title', '!=', 'Video Thumbnail')
                ->findOrFail($imageId);
        } else {
            // Fallback: delete the primary non-thumbnail image (backward compatibility)
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
        }

        // Delete physical file if it's a local upload (not external URL)
        $localPath = str_replace('/storage/', 'public/', $image->image_url);
        if (Storage::exists($localPath)) {
            Storage::delete($localPath);
        }

        $image->delete();

        // If we deleted the primary, set another image as primary
        $remainingImages = $campaign->images()
            ->where('title', '!=', 'Video Thumbnail')
            ->get();
        if ($remainingImages->count() > 0 && !$remainingImages->contains('is_primary', true)) {
            $remainingImages->first()->update(['is_primary' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Gambar berhasil dihapus.',
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Anda bukan pemilik kampanye ini.',
            ], 403);
        }

        if ($campaign->status !== CampaignStatus::DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya kampanye draft yang bisa dihapus.',
            ], 422);
        }

        // Delete associated images from storage
        foreach ($campaign->images as $image) {
            $localPath = str_replace('/storage/', 'public/', $image->image_url);
            if (\Illuminate\Support\Facades\Storage::exists($localPath)) {
                \Illuminate\Support\Facades\Storage::delete($localPath);
            }
        }

        $campaign->images()->delete();
        $campaign->tiers()->delete();
        $campaign->updates()->delete();
        $campaign->backings()->delete();
        $campaign->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kampanye berhasil dihapus.',
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

        if ($campaign->status !== CampaignStatus::DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya kampanye draft yang bisa disubmit.',
            ], 422);
        }

        // Validate: at least 1 image (excluding video thumbnail) or a video thumbnail
        $imageCount = $campaign->images()
            ->where('title', '!=', 'Video Thumbnail')
            ->count();
        $hasVideoThumbnail = $campaign->images()
            ->where('title', 'Video Thumbnail')
            ->count() > 0;

        if ($imageCount === 0 && !$hasVideoThumbnail) {
            return response()->json([
                'success' => false,
                'message' => 'Kampanye harus memiliki minimal 1 gambar sebelum disubmit.',
            ], 422);
        }

        // Validate: at least 1 tier
        $tierCount = $campaign->tiers()->count();
        if ($tierCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Kampanye harus memiliki minimal 1 tier reward.',
            ], 422);
        }

        $campaign->status = CampaignStatus::REVIEW;
        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => 'Kampanye dikirim ke admin untuk direview.',
            'data' => $campaign,
        ], 200);
    }

    public function myCampaigns(): JsonResponse
    {
        $campaigns = Campaign::with(['category', 'tiers', 'images'])
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

        if (!in_array($campaign->status, [CampaignStatus::ACTIVE, CampaignStatus::REVIEW])) {
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

        // Send notification to all backers who backed this campaign
        $backers = Backing::where('campaign_id', $campaign->id)
            ->where('status', BackingStatus::COMPLETED)
            ->with('user')
            ->get();

        foreach ($backers as $backing) {
            if ($backing->user_id !== $user->id) { // Don't notify the creator
                Notification::create([
                    'user_id' => $backing->user_id,
                    'type' => 'campaign_update',
                    'title' => 'Pembaruan Kampanye: ' . $campaign->title,
                    'body' => $validated['title'] . ' — ' . Str::limit($validated['content'], 150),
                    'data' => [
                        'campaign_id' => $campaign->id,
                        'campaign_slug' => $campaign->slug,
                        'update_id' => $update->id,
                    ],
                    'created_at' => now(),
                ]);

                // Send email notification
                try {
                    $backerName = $backing->user->name ?? 'Backer';
                    Mail::to($backing->user->email)->send(new NotifikasiEmail(
                        'Pembaruan Kampanye: ' . $campaign->title,
                        'Halo ' . $backerName . '!',
                        '"' . $campaign->title . '" telah menerbitkan pembaruan baru:\n\n' .
                        $validated['title'] . '\n' .
                        Str::limit($validated['content'], 300) . '\n\n' .
                        'Klik tombol di bawah untuk melihat detail pembaruan.',
                        'Lihat Pembaruan',
                        url('/campaigns/' . $campaign->slug)
                    ));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Gagal kirim email notifikasi update ke ' . $backing->user->email . ': ' . $e->getMessage());
                }
            }
        }

        $emailCount = $backers->where('user_id', '!=', $user->id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Update berhasil ditambahkan. ' . $emailCount . ' backer telah diberitahu via notifikasi dan email.',
            'data' => $update,
        ], 201);
    }
}
