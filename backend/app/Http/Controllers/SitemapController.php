<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Enums\CampaignStatus;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $campaigns = Campaign::whereIn('status', [
            CampaignStatus::ACTIVE,
            CampaignStatus::SUCCESS,
        ])->get(['slug', 'updated_at']);

        $frontendUrl = config('app.frontend_url');

        $content = '<?xml version="1.0" encoding="UTF-8"?>';
        $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Static pages
        $staticUrls = [
            ['loc' => $frontendUrl . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $frontendUrl . '/campaigns', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $frontendUrl . '/login', 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['loc' => $frontendUrl . '/register', 'priority' => '0.3', 'changefreq' => 'monthly'],
        ];

        foreach ($staticUrls as $url) {
            $content .= '<url>';
            $content .= '<loc>' . e($url['loc']) . '</loc>';
            $content .= '<priority>' . $url['priority'] . '</priority>';
            $content .= '<changefreq>' . $url['changefreq'] . '</changefreq>';
            $content .= '</url>';
        }

        // Campaign pages
        foreach ($campaigns as $campaign) {
            $content .= '<url>';
            $content .= '<loc>' . e($frontendUrl . '/campaigns/' . $campaign->slug) . '</loc>';
            $content .= '<lastmod>' . $campaign->updated_at?->toW3cString() . '</lastmod>';
            $content .= '<priority>0.8</priority>';
            $content .= '<changefreq>weekly</changefreq>';
            $content .= '</url>';
        }

        $content .= '</urlset>';

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
