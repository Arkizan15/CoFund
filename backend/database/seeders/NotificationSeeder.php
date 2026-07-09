<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Get all campaign slugs for reference links
        $campaigns = \App\Models\Campaign::select('id', 'slug', 'title')->get();

        // Get all users
        $users = User::all();

        $now = now();

        foreach ($users as $user) {
            $isCreator = $user->role === 'creator' || $user->role === 'admin';
            $isBacker = $user->role === 'backer';

            // Pick a random campaign for this user's notifications
            $randomCampaign = $campaigns->random();

            $notifications = [];

            // 1. Announcement from admin (all users)
            $notifications[] = [
                'user_id' => $user->id,
                'type' => 'announcement',
                'title' => 'Pembaruan Platform CoFund',
                'body' => 'Kami telah merilis fitur baru yang memungkinkan Anda melihat statistik pendanaan secara real-time. Cek halaman dashboard Anda sekarang untuk informasi lebih lanjut.',
                'data' => ['announced_at' => $now->copy()->subDays(2)->toISOString()],
                'read_at' => $this->randomReadAt($now),
                'created_at' => $now->copy()->subDays(2),
            ];

            // 2. Campaign approved (creators + backers who follow)
            $notifications[] = [
                'user_id' => $user->id,
                'type' => 'campaign_approved',
                'title' => 'Kampanye Disetujui!',
                'body' => "Kampanye \"{$randomCampaign->title}\" telah disetujui oleh admin dan sekarang aktif. Kampanye dapat mulai menerima pendanaan.",
                'data' => [
                    'campaign_id' => $randomCampaign->id,
                    'campaign_slug' => $randomCampaign->slug,
                ],
                'read_at' => $this->randomReadAt($now),
                'created_at' => $now->copy()->subDays(5),
            ];

            // 3. Deadline approaching (backers)
            $notifications[] = [
                'user_id' => $user->id,
                'type' => 'deadline_approaching',
                'title' => 'Deadline Kampanye Mendekat!',
                'body' => "Kampanye \"{$randomCampaign->title}\" akan berakhir dalam 3 hari. Jangan lewatkan kesempatan untuk mendukung!",
                'data' => [
                    'campaign_id' => $randomCampaign->id,
                    'campaign_slug' => $randomCampaign->slug,
                    'days_left' => 3,
                ],
                'read_at' => $this->randomReadAt($now),
                'created_at' => $now->copy()->subDays(1),
            ];

            // 4. Backing success / funding received
            $notifications[] = [
                'user_id' => $user->id,
                'type' => 'backing_success',
                'title' => 'Pendanaan Berhasil!',
                'body' => "Pendanaan sebesar Rp " . number_format(rand(50000, 500000), 0, ',', '.') . " untuk kampanye \"{$randomCampaign->title}\" telah berhasil. Dana telah masuk ke escrow.",
                'data' => [
                    'campaign_id' => $randomCampaign->id,
                    'campaign_slug' => $randomCampaign->slug,
                ],
                'read_at' => $this->randomReadAt($now),
                'created_at' => $now->copy()->subDays(3),
            ];

            // 5. Campaign update posted
            $notifications[] = [
                'user_id' => $user->id,
                'type' => 'campaign_update',
                'title' => 'Pembaruan Kampanye',
                'body' => "Kampanye \"{$randomCampaign->title}\" telah menerbitkan pembaruan baru: \"Perkembangan Terbaru Proyek — Terima kasih atas dukungan Anda semua!\"",
                'data' => [
                    'campaign_id' => $randomCampaign->id,
                    'campaign_slug' => $randomCampaign->slug,
                    'update_id' => 1,
                ],
                'read_at' => $this->randomReadAt($now),
                'created_at' => $now->copy()->subDays(4),
            ];

            // 6. Top up success (wallet)
            $notifications[] = [
                'user_id' => $user->id,
                'type' => 'top_up',
                'title' => 'Top Up Saldo Berhasil',
                'body' => 'Top up saldo sebesar Rp ' . number_format(rand(100000, 1000000), 0, ',', '.') . ' telah berhasil. Saldo Anda sekarang Rp ' . number_format(rand(200000, 2000000), 0, ',', '.') . '.',
                'data' => null,
                'read_at' => $this->randomReadAt($now),
                'created_at' => $now->copy()->subDays(6),
            ];

            // 7. Campaign rejected (for creators)
            $notifications[] = [
                'user_id' => $user->id,
                'type' => 'campaign_rejected',
                'title' => 'Kampanye Ditolak',
                'body' => "Kampanye \"{$randomCampaign->title}\" ditolak oleh admin. Alasan: Deskripsi kampanye perlu dilengkapi dengan detail penggunaan dana yang lebih jelas.",
                'data' => [
                    'campaign_id' => $randomCampaign->id,
                    'campaign_slug' => $randomCampaign->slug,
                    'reason' => 'Deskripsi kampanye perlu dilengkapi dengan detail penggunaan dana yang lebih jelas.',
                ],
                'read_at' => $this->randomReadAt($now),
                'created_at' => $now->copy()->subDays(7),
            ];

            // 8. Disbursement / pencairan dana (for creators)
            $notifications[] = [
                'user_id' => $user->id,
                'type' => 'disbursement',
                'title' => 'Pencairan Dana Berhasil!',
                'body' => "Dana kampanye \"{$randomCampaign->title}\" sebesar Rp " . number_format(rand(1000000, 10000000), 0, ',', '.') . " telah dicairkan ke saldo Anda. Dana dapat digunakan atau ditarik kapan saja.",
                'data' => [
                    'campaign_id' => $randomCampaign->id,
                    'campaign_slug' => $randomCampaign->slug,
                ],
                'read_at' => $this->randomReadAt($now),
                'created_at' => $now->copy()->subDays(8),
            ];

            // 9. Withdraw success
            $notifications[] = [
                'user_id' => $user->id,
                'type' => 'withdraw',
                'title' => 'Penarikan Dana Berhasil',
                'body' => 'Penarikan dana sebesar Rp ' . number_format(rand(50000, 500000), 0, ',', '.') . ' ke rekening Bank BCA a.n. ' . $user->name . ' telah berhasil diproses.',
                'data' => null,
                'read_at' => $this->randomReadAt($now),
                'created_at' => $now->copy()->subDays(9),
            ];

            // 10. System notification
            $notifications[] = [
                'user_id' => $user->id,
                'type' => 'system',
                'title' => 'Verifikasi Email Berhasil',
                'body' => 'Selamat datang di CoFund! Akun Anda telah berhasil diverifikasi. Anda sekarang dapat membuat kampanye atau mendukung kampanye favorit Anda.',
                'data' => null,
                'read_at' => $now->copy()->subDays(10),
                'created_at' => $now->copy()->subDays(10),
            ];

            // Insert 8 notifications per user (mix of read/unread, covering many types)
            // Keep at least 5 unread for a rich inbox experience
            $selected = array_slice($notifications, 0, 8);
            foreach ($selected as $notif) {
                Notification::create($notif);
            }
        }

        $totalUsers = $users->count();
        $totalNotifs = Notification::count();
        echo "Seeder: {$totalNotifs} notifikasi dibuat untuk {$totalUsers} pengguna.\n";
    }

    /**
     * 60% chance unread (null), 40% chance read (random datetime in past)
     */
    private function randomReadAt($now)
    {
        if (rand(1, 100) <= 60) {
            return null; // unread
        }
        return $now->copy()->subDays(rand(1, 5));
    }
}
