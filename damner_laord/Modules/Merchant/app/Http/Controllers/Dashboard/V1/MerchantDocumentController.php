<?php

namespace Modules\Merchant\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Modules\Merchant\Actions\Dashboard\V1\MerchantDocumentAction\VerifyMerchantDocumentAction;
use Modules\Merchant\Http\Requests\Dashboard\V1\MerchantDocumentRequest\VerifyMerchantDocumentRequest;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Models\MerchantDocument;

class MerchantDocumentController extends Controller
{
    /** Tick / untick a checklist item (spec 03.2). */
    public function verify(
        VerifyMerchantDocumentRequest $request,
        Merchant $merchant,
        MerchantDocument $document,
        VerifyMerchantDocumentAction $verify,
    ): RedirectResponse {
        /** @var User $admin Routes require auth */
        $admin = $request->user();

        $verify->execute($document, $admin, $request->boolean('verified'));

        return back();
    }
}
