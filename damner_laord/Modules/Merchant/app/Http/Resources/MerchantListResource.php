<?php

namespace Modules\Merchant\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Models\Merchant;

/**
 * One row of the merchant list (spec 03.1). Matches `MerchantListItem` in
 * Modules/Merchant/resources/js/types.
 *
 * @mixin Merchant
 */
class MerchantListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'initials' => self::initials($this->name),
            'subtitle' => $this->website ?? $this->email,
            'type' => $this->type->toOption(),
            'city' => $this->city,
            'commissionPercent' => $this->commission_rate / 100,
            'status' => $this->status->toOption(),
            'joinedAt' => $this->created_at?->toIso8601String(),
            // Editable details, so "Edit details" works from the list too
            'legalName' => $this->legal_name,
            'contactName' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'address' => $this->address,
            'province' => $this->province,
            'can' => self::abilities($request, $this->resource),
        ];
    }

    /**
     * What the signed-in admin may do with this merchant right now. Shared
     * by the list row menu and the detail page; the server re-checks every
     * action anyway.
     *
     * @return array<string, bool>
     */
    public static function abilities(Request $request, Merchant $merchant): array
    {
        $user = $request->user();
        $status = $merchant->status;
        $canReview = $user?->can('review', $merchant) ?? false;
        $canSuspend = $user?->can('suspend', $merchant) ?? false;

        return [
            'update' => $user?->can('update', $merchant) ?? false,
            'review' => $canReview,
            'approve' => $canReview && $status->isUnderReview() && $status->canTransitionTo(MerchantStatus::Active),
            'requestChanges' => $canReview && $status->isUnderReview(),
            'reject' => $canReview && $status->canTransitionTo(MerchantStatus::Rejected),
            'suspend' => $canSuspend && $status->canTransitionTo(MerchantStatus::Suspended),
            'reactivate' => $canSuspend && $status === MerchantStatus::Suspended,
        ];
    }

    /** "Capital Express" → "CE" */
    public static function initials(string $name): string
    {
        return Str::of($name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
    }
}
