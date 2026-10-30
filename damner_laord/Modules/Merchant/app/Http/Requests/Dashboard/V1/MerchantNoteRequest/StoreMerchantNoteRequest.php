<?php

namespace Modules\Merchant\Http\Requests\Dashboard\V1\MerchantNoteRequest;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Merchant\Models\Merchant;

class StoreMerchantNoteRequest extends FormRequest
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
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
