<?php

namespace Modules\Merchant\Http\Requests\Dashboard\V1\MerchantRequest;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Merchant\Models\Merchant;

class RequestMerchantChangesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $merchant = $this->route('merchant');

        return $merchant instanceof Merchant && ($this->user()?->can('review', $merchant) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function message(): string
    {
        return $this->string('message')->trim()->value();
    }
}
