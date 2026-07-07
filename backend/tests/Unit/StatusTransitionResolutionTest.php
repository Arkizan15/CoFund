<?php

namespace Tests\Unit;

use App\Enums\BackingStatus;
use App\Enums\CampaignStatus;
use App\Models\Backing;
use App\Models\Campaign;
use Tests\TestCase;

class StatusTransitionResolutionTest extends TestCase
{
    public function test_backing_status_resolver_accepts_enum_or_raw_value(): void
    {
        $method = new \ReflectionMethod(Backing::class, 'resolveStatus');
        $method->setAccessible(true);

        $this->assertSame(BackingStatus::PENDING, $method->invoke(null, BackingStatus::PENDING));
        $this->assertSame(BackingStatus::COMPLETED, $method->invoke(null, 'completed'));
        $this->assertNull($method->invoke(null, 'invalid-status'));
    }

    public function test_campaign_status_resolver_accepts_enum_or_raw_value(): void
    {
        $method = new \ReflectionMethod(Campaign::class, 'resolveStatus');
        $method->setAccessible(true);

        $this->assertSame(CampaignStatus::DRAFT, $method->invoke(null, CampaignStatus::DRAFT));
        $this->assertSame(CampaignStatus::ACTIVE, $method->invoke(null, 'active'));
        $this->assertNull($method->invoke(null, 'invalid-status'));
    }
}
