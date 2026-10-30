<?php

namespace App\Concerns;

use Illuminate\Support\Str;

/**
 * Public identifier for a model, used in URLs so internal numeric ids
 * never leak to the browser or the apps. Requires a unique `uuid` column.
 *
 * The uuid is set when the model object is built (not in a "creating"
 * event), so it also works when events are muted, e.g. in seeders that use
 * WithoutModelEvents. Models loaded from the database get theirs replaced
 * by the stored value.
 */
trait HasUuid
{
    public function initializeHasUuid(): void
    {
        $this->attributes['uuid'] ??= (string) Str::uuid();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
