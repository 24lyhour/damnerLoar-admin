<?php

namespace Modules\Merchant\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Merchant\Enums\MerchantDocumentStatus;
use Modules\Merchant\Enums\MerchantDocumentType;
use Modules\Merchant\Models\MerchantDocument;

/**
 * One approval-checklist row. A required document that was never
 * uploaded has no record; `missing()` builds its row so the checklist
 * always shows every required item.
 *
 * @mixin MerchantDocument
 */
class MerchantDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type->toOption(),
            'status' => $this->status->toOption(),
            'fileName' => $this->file_name,
            'fileSize' => $this->file_size,
            'note' => $this->note,
            'expiresAt' => $this->expires_at?->toDateString(),
            'verifiedAt' => $this->verified_at?->toIso8601String(),
            'canVerify' => $this->file_path !== null && ! $this->isExpired(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function missing(MerchantDocumentType $type): array
    {
        return [
            'uuid' => null,
            'type' => $type->toOption(),
            'status' => MerchantDocumentStatus::Missing->toOption(),
            'fileName' => null,
            'fileSize' => null,
            'note' => null,
            'expiresAt' => null,
            'verifiedAt' => null,
            'canVerify' => false,
        ];
    }
}
