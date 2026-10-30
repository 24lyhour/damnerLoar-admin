<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantDocumentAction;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Modules\Merchant\Enums\MerchantDocumentStatus;
use Modules\Merchant\Models\MerchantDocument;

/**
 * Ticks or unticks one item on the approval checklist (spec 03.2).
 * Only an uploaded, unexpired document can be marked verified.
 */
class VerifyMerchantDocumentAction
{
    public function execute(MerchantDocument $document, User $actor, bool $verified): MerchantDocument
    {
        if ($verified) {
            $this->ensureCanBeVerified($document);

            $document->forceFill([
                'status' => MerchantDocumentStatus::Verified,
                'verified_at' => now(),
                'verified_by' => $actor->id,
            ]);
        } else {
            $document->forceFill([
                'status' => MerchantDocumentStatus::PendingReview,
                'verified_at' => null,
                'verified_by' => null,
            ]);
        }

        $document->save();

        return $document;
    }

    private function ensureCanBeVerified(MerchantDocument $document): void
    {
        $problem = match (true) {
            $document->file_path === null => 'The merchant hasn\'t uploaded this document yet.',
            $document->isExpired() => 'This document has expired. Ask the merchant for a new one.',
            default => null,
        };

        if ($problem !== null) {
            throw ValidationException::withMessages(['document' => $problem]);
        }
    }
}
