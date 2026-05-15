<?php

namespace App\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class WorkspaceScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check() && session()->has('current_workspace_id')) {
            $builder->where($model->getTable().'.workspace_id', session('current_workspace_id'));
        }
    }
}
