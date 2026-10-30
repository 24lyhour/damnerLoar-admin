<?php

namespace Modules\Merchant\Http\Requests\Dashboard\V1\MerchantRequest;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Merchant\Models\Merchant;

/**
 * Reject and suspend both need a written reason (it goes to the notes /
 * the merchant record and later the audit log).
 */
class MerchantReasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $merchant = $this->route('merchant');
        $ability = $this->routeIs('*.suspend') ? 'suspend' : 'review';

        return $merchant instanceof Merchant && ($this->user()?->can($ability, $merchant) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function reason(): string
    {
        return $this->string('reason')->trim()->value();
    }
}
