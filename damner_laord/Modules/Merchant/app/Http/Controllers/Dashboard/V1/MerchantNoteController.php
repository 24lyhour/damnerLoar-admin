<?php

namespace Modules\Merchant\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Modules\Merchant\Actions\Dashboard\V1\MerchantNoteAction\AddMerchantNoteAction;
use Modules\Merchant\Http\Requests\Dashboard\V1\MerchantNoteRequest\StoreMerchantNoteRequest;
use Modules\Merchant\Models\Merchant;

class MerchantNoteController extends Controller
{
    public function store(StoreMerchantNoteRequest $request, Merchant $merchant, AddMerchantNoteAction $addNote): RedirectResponse
    {
        /** @var User $admin Routes require auth */
        $admin = $request->user();

        $addNote->execute($merchant, $admin, $request->string('body')->value());

        return back();
    }
}
