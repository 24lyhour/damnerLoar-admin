<?php

namespace Modules\Merchant\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Merchant\Models\MerchantNote;

/**
 * @mixin MerchantNote
 */
class MerchantNoteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'body' => $this->body,
            'author' => $this->author->name,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
