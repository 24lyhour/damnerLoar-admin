<?php

namespace Modules\Merchant\Enums;

use App\Concerns\EnumOptions;

/**
 * Merchant lifecycle (spec p.5): applies → review → changes requested ⇄ review
 * → approved (active) → suspended. Rejected applications stay for the record.
 */
enum MerchantStatus: string
{
    use EnumOptions;

    case Pending = 'pending';
    case ChangesRequested = 'changes_requested';
    case Active = 'active';
    case Suspended = 'suspended';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending approval',
            self::ChangesRequested => 'Changes requested',
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Rejected => 'Rejected',
        };
    }

    public function labelKh(): string
    {
        return match ($this) {
            self::Pending => 'កំពុងរង់ចាំការអនុម័ត',
            self::ChangesRequested => 'ត្រូវការកែប្រែ',
            self::Active => 'សកម្ម',
            self::Suspended => 'បានផ្អាក',
            self::Rejected => 'បានបដិសេធ',
        };
    }

    /** Pending states are grey, never orange (spec p.3). */
    public function tone(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Suspended, self::Rejected => 'error',
            self::Pending, self::ChangesRequested => 'neutral',
        };
    }

    /** Only active merchants sell on the apps and receive payouts. */
    public function canSell(): bool
    {
        return $this === self::Active;
    }
}
