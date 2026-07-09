<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLoggerService
{
    /**
     * Log an admin activity.
     */
    public static function log(
        int $userId,
        string $action,
        ?string $resourceType = null,
        ?int $resourceId = null,
        ?string $description = null,
        ?array $metadata = null,
        ?string $ipAddress = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => $ipAddress ?? request()->ip(),
        ]);
    }
}
