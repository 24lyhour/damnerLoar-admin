<?php

namespace Modules\Merchant\Http\Requests\Dashboard\V1\MerchantRequest;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Merchant\Models\Merchant;

/**
 * "Edit details" form on the merchant page. The admin types commission as a
 * percent (12.5); it is stored as basis points (1250).
 */
class UpdateMerchantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $merchant = $this->route('merchant');

        return $merchant instanceof Merchant && ($this->user()?->can('update', $merchant) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'commission_percent' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['commission_percent' => 'commission'];
    }

    /**
     * Validated details, ready for the model.
     *
     * @return array<string, mixed>
     */
    public function details(): array
    {
        $details = $this->safe()->except('commission_percent');

        // 12.5 % → 1250 basis points, rounded to avoid float noise.
        $details['commission_rate'] = (int) round((float) $this->input('commission_percent') * 100);

        return $details;
    }
}
