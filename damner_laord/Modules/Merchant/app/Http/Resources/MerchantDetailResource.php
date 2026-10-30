<?php

namespace Modules\Merchant\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Merchant\Enums\MerchantDocumentType;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Models\MerchantDocument;

/**
 * Merchant approval / detail page (spec 03.2). Expects documents,
 * notes.author and approvedBy to be loaded. Matches `MerchantDetail`
 * in Modules/Merchant/resources/js/types.
 *
 * @mixin Merchant
 */
class MerchantDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Row fields + editable details + `can` (same as the list)
            ...(new MerchantListResource($this->resource))->toArray($request),
            'appliedAt' => $this->created_at?->toIso8601String(),
            'approvedAt' => $this->approved_at?->toIso8601String(),
            'approvedBy' => $this->approvedBy?->name,
            'suspendedReason' => $this->suspended_reason,
            'pendingDocumentCount' => $this->pendingDocumentCount(),
            'checklist' => $this->checklist($request),
            'notes' => MerchantNoteResource::collection($this->notes)->resolve($request),
        ];
    }

    /**
     * Every required document for this merchant type, in checklist order.
     *
     * @return list<array<string, mixed>>
     */
    private function checklist(Request $request): array
    {
        $byType = $this->documents->keyBy(fn (MerchantDocument $doc): string => $doc->type->value);

        return array_map(
            fn (MerchantDocumentType $type): array => $byType->has($type->value)
                ? (new MerchantDocumentResource($byType->get($type->value)))->toArray($request)
                : MerchantDocumentResource::missing($type),
            MerchantDocumentType::requiredFor($this->type),
        );
    }
}
