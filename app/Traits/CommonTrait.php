<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait CommonTrait
{
    public static function bootCommonTrait(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if (Auth::check() && in_array('deleted_by', $model->getFillable())) {
                $model->deleted_by = Auth::id();
                $model->saveQuietly();
            }
        });
    }
}
