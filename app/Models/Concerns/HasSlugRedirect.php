<?php

namespace App\Models\Concerns;

use App\Models\Redirect;

trait HasSlugRedirect
{
    public static function bootHasSlugRedirect(): void
    {
        static::updating(function ($model) {
            if ($model->isDirty('slug')) {
                $old = $model->getOriginal('slug');
                if (! $old) {
                    return;
                }
                $prefix = method_exists($model, 'slugRoutePrefix') ? $model->slugRoutePrefix() : '';
                Redirect::updateOrCreate(
                    ['source' => '/'.$prefix.$old],
                    ['destination' => '/'.$prefix.$model->slug, 'status_code' => 301, 'is_active' => true]
                );
            }
        });
    }
}
