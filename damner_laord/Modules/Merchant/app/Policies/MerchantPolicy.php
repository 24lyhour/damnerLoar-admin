<?php

namespace Modules\Merchant\Policies;

use App\Models\User;
use Modules\Merchant\Models\Merchant;

/**
 * Who may see and manage merchants.
 *
 * For now: any admin account (a user with no merchant_id); merchant staff
 * never reach the admin. When roles arrive (Admin module), map these to
 * the spec matrix p.43: Super Admin / Operations = full, Finance / Support = view.
 */
class MerchantPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, Merchant $merchant): bool
    {
        return $this->isAdmin($user);
    }

    /** Invite a new merchant. */
    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    /** Edit company details and the commission rate. */
    public function update(User $user, Merchant $merchant): bool
    {
        return $this->isAdmin($user);
    }

    /** Approve, reject, request changes, verify documents, add notes. */
    public function review(User $user, Merchant $merchant): bool
    {
        return $this->isAdmin($user);
    }

    /** Suspend or reactivate a live merchant. */
    public function suspend(User $user, Merchant $merchant): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->merchant_id === null;
    }
}
