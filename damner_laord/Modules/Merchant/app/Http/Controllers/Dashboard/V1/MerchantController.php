<?php

namespace Modules\Merchant\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use InertiaUI\Modal\Modal;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\GetMerchantCreateDataAction;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\GetMerchantEditDataAction;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\GetMerchantIndexDataAction;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\GetMerchantShowDataAction;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\InviteMerchantAction;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\UpdateMerchantAction;
use Modules\Merchant\Http\Requests\Dashboard\V1\MerchantRequest\IndexMerchantRequest;
use Modules\Merchant\Http\Requests\Dashboard\V1\MerchantRequest\InviteMerchantRequest;
use Modules\Merchant\Http\Requests\Dashboard\V1\MerchantRequest\UpdateMerchantRequest;
use Modules\Merchant\Models\Merchant;

class MerchantController extends Controller
{
    /** Spec 03.1 Merchant list. */
    public function index(IndexMerchantRequest $request, GetMerchantIndexDataAction $action): Response
    {
        return Inertia::render('merchant::Dashboard/V1/Merchant/Index', $action->execute($request->filters()));
    }

    /** "Invite merchant" slideover over the list. */
    public function create(GetMerchantCreateDataAction $action): Modal
    {
        Gate::authorize('create', Merchant::class);

        return Inertia::modal('merchant::Dashboard/V1/Merchant/Invite', $action->execute())
            ->baseRoute('merchant.merchants.index');
    }

    public function store(InviteMerchantRequest $request, InviteMerchantAction $action): RedirectResponse
    {
        $merchant = $action->execute($request->details());

        return $this->done("{$merchant->name} invited. They'll get an email to finish their application.");
    }

    /** Spec 03.2 Merchant approval / detail. */
    public function show(Merchant $merchant, GetMerchantShowDataAction $action): Response
    {
        Gate::authorize('view', $merchant);

        return Inertia::render('merchant::Dashboard/V1/Merchant/Show', $action->execute($merchant));
    }

    /** "Edit details" slideover over the merchant page. */
    public function edit(Merchant $merchant, GetMerchantEditDataAction $action): Modal
    {
        Gate::authorize('update', $merchant);

        return Inertia::modal('merchant::Dashboard/V1/Merchant/Edit', $action->execute($merchant))
            ->baseRoute('merchant.merchants.show', $merchant);
    }

    public function update(UpdateMerchantRequest $request, Merchant $merchant, UpdateMerchantAction $action): RedirectResponse
    {
        $action->execute($merchant, $request->details());

        return $this->done("{$merchant->name} saved.");
    }

    private function done(string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return back();
    }
}
