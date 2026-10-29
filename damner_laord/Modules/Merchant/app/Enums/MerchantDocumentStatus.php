<?php

namespace Modules\Merchant\Enums;

use App\Concerns\EnumOptions;

/** Per-document status on the approval checklist (spec 03.2). */
enum MerchantDocumentStatus: string
{
    use EnumOptions;

    case Missing = 'missing';
    case PendingReview = 'pending_review';
    case Verified = 'verified';
    case Expired = 'expired';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Missing => 'Missing',
            self::PendingReview => 'Pending review',
            self::Verified => 'Verified',
            self::Expired => 'Expired',
            self::Rejected => 'Rejected',
        };
    }

    public function labelKh(): string
    {
        return match ($this) {
            self::Missing => 'ខ្វះ',
            self::PendingReview => 'កំពុងពិនិត្យ',
            self::Verified => 'បានផ្ទៀងផ្ទាត់',
            self::Expired => 'ផុតកំណត់',
            self::Rejected => 'បានបដិសេធ',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Verified => 'success',
            self::Missing, self::Expired, self::Rejected => 'error',
            self::PendingReview => 'neutral',
        };
    }
}
