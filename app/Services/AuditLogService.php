<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public function record(
        string $action,
        string $modelType,
        int $modelId,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {

        AuditLog::create([

            'user_id' => Auth::id(),

            'action' => $action,

            'model_type' => $modelType,

            'model_id' => $modelId,

            'old_values' => $oldValues,

            'new_values' => $newValues,

            'ip_address' => Request::ip(),

        ]);
    }
}
