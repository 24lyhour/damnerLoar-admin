<?php

namespace Modules\Merchant\Http\Requests\Dashboard\V1\MerchantRequest;

use Illuminate\Validation\Rule;
use Modules\Merchant\Enums\MerchantType;
use Modules\Merchant\Models\Merchant;

/**
 * "Invite merchant" modal: the same details as editing, plus the type,
 * and an email so the invite can be sent.
 */
class InviteMerchantRequest extends UpdateMerchantRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Merchant::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'type' => ['required', Rule::enum(MerchantType::class)],
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}
