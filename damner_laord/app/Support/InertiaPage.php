<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Maps an Inertia page name to its Vue file, matching
 * resources/js/lib/resolvePage.ts on the frontend:
 *
 *   "dashboard/Overview"                → resources/js/pages/dashboard/Overview.vue
 *   "merchant::Dashboard/Merchant/Index" → Modules/Merchant/resources/js/pages/Dashboard/Merchant/Index.vue
 *
 * Used by app.blade.php so @vite preloads the right page file. A wrong path
 * here makes every page fail when the built manifest is used (production).
 */
final class InertiaPage
{
    public static function viteEntry(string $component): string
    {
        if (! str_contains($component, '::')) {
            return "resources/js/pages/{$component}.vue";
        }

        [$module, $path] = explode('::', $component, 2);

        return 'Modules/'.Str::studly($module)."/resources/js/pages/{$path}.vue";
    }
}
