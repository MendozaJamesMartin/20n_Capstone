<?php

namespace App\Services;

use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log($event, $auditableType = null, $auditableId = null, $oldValues = [], $newValues = [], $tags = null)
    {
        $user = Auth::user();

        // Convert any arrays to JSON for safe storage
        $oldValues = array_map(fn($v) => is_array($v) ? json_encode($v) : $v, $oldValues);
        $newValues = array_map(fn($v) => is_array($v) ? json_encode($v) : $v, $newValues);

        Audit::create([
            'user_type'      => $user ? get_class($user) : null,
            'user_id'        => $user?->id,
            'event'          => $event,
            'auditable_type' => $auditableType,
            'auditable_id'   => $auditableId,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'url'            => request()->fullUrl(),
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'tags'           => $tags,
        ]);
    }
}
