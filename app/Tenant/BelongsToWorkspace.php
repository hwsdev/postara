<?php

namespace App\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToWorkspace
{
    public static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope(new WorkspaceScope);

        static::creating(function ($model) {
            if (empty($model->workspace_id) && session()->has('current_workspace_id')) {
                $model->workspace_id = session('current_workspace_id');
            }
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Workspace::class);
    }
}
