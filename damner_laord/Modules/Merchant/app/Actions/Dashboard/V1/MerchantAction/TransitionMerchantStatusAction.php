<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantAction;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Events\MerchantStatusChanged;
use Modules\Merchant\Models\Merchant;

/**
 * The one place a merchant's status changes. Checks the lifecycle rules in
 * MerchantStatus::nextStatuses(), saves, and fires MerchantStatusChanged.
 * Used by the approve / request changes / reject / suspend / reactivate Actions.
 */
class TransitionMerchantStatusAction
{
    /**
     * @param  array<string, mixed>  $extra  Other columns to save with the status
     *
     * @throws ValidationException when the move isn't allowed
     */
    public function execute(
        Merchant $merchant,
        MerchantStatus $to,
        User $actor,
        ?string $reason = null,
        array $extra = [],
    ): Merchant {
        $from = $merchant->status;

        if (! $from->canTransitionTo($to)) {
            throw ValidationException::withMessages([
                'status' => "A merchant that is \"{$from->label()}\" can't be changed to \"{$to->label()}\".",
            ]);
        }

        return DB::transaction(function () use ($merchant, $from, $to, $actor, $reason, $extra): Merchant {
            $merchant->forceFill(['status' => $to, ...$extra])->save();

            MerchantStatusChanged::dispatch($merchant, $from, $to, $actor, $reason);

            return $merchant;
        });
    }
}
