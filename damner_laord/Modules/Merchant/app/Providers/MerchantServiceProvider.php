<?php

namespace Modules\Merchant\Providers;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Policies\MerchantPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class MerchantServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Merchant';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'merchant';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        // Module models aren't auto-discovered by Laravel's policy guesser.
        Gate::policy(Merchant::class, MerchantPolicy::class);

        // Sidebar badge "Merchants 3": applications waiting for review.
        // A closure, so the count only runs when a page is rendered for an admin.
        Inertia::share('navBadges.merchants', function (): ?int {
            $user = Auth::user();

            return $user instanceof User && $user->can('viewAny', Merchant::class)
                ? Merchant::awaitingReviewCount()
                : null;
        });
    }

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
