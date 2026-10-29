<?php

namespace App\Concerns;

use Illuminate\Support\Str;

/**
 * Public identifier for a model: filled on create and used in URLs,
 * so internal numeric ids never leak to the browser or the apps.
 *
 * Requires a unique `uuid` column.
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function (self $model): void {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
