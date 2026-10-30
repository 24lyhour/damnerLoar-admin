<?php

namespace Modules\Merchant\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use InertiaUI\Modal\Modal;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\ApproveMerchantAction;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\GetMerchantEditDataAction;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\ReactivateMerchantAction;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\RejectMerchantAction;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\RequestMerchantChangesAction;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\SuspendMerchantAction;
use Modules\Merchant\Http\Requests\Dashboard\V1\MerchantRequest\MerchantReasonRequest;
use Modules\Merchant\Http\Requests\Dashboard\V1\MerchantRequest\RequestMerchantChangesRequest;
use Modules\Merchant\Models\Merchant;

/** Status changes on the merchant approval page (spec 03.2 + workflow p.5). */
class MerchantReviewController extends Controller
{
    /** Who may open each confirm modal (same policy as the POST that follows). */
    private const ABILITY = [
        'approve' => 'review',
        'request-changes' => 'review',
        'reject' => 'review',
        'suspend' => 'suspend',
        'reactivate' => 'suspend',
    ];

    /**
     * Confirm slideover before a status change, e.g. GET …/suspend.
     * The route sets {review}; the page is one component for all five.
     */
    public function confirm(Merchant $merchant, string $review, GetMerchantEditDataAction $action): Modal
    {
        Gate::authorize(self::ABILITY[$review], $merchant);

        return Inertia::modal('merchant::Dashboard/V1/Merchant/Review', [
            ...$action->execute($merchant),
            'review' => $review,
        ])->baseRoute('merchant.merchants.show', $merchant);
    }

    public function approve(Request $request, Merchant $merchant, ApproveMerchantAction $approve): RedirectResponse
    {
        Gate::authorize('review', $merchant);

        $approve->execute($merchant, $this->admin($request));

        return $this->done("{$merchant->name} is approved and can now sell.");
    }

    public function requestChanges(RequestMerchantChangesRequest $request, Merchant $merchant, RequestMerchantChangesAction $requestChanges): RedirectResponse
    {
        $requestChanges->execute($merchant, $this->admin($request), $request->message());

        return $this->done('Changes requested. The merchant has been emailed.');
    }

    public function reject(MerchantReasonRequest $request, Merchant $merchant, RejectMerchantAction $reject): RedirectResponse
    {
        $reject->execute($merchant, $this->admin($request), $request->reason());

        return $this->done("{$merchant->name}'s application was rejected.");
    }

    public function suspend(MerchantReasonRequest $request, Merchant $merchant, SuspendMerchantAction $suspend): RedirectResponse
    {
        $suspend->execute($merchant, $this->admin($request), $request->reason());

        return $this->done("{$merchant->name} is suspended. Payouts are on hold.");
    }

    public function reactivate(Request $request, Merchant $merchant, ReactivateMerchantAction $reactivate): RedirectResponse
    {
        Gate::authorize('suspend', $merchant);

        $reactivate->execute($merchant, $this->admin($request));

        return $this->done("{$merchant->name} is active again.");
    }

    private function admin(Request $request): User
    {
        /** @var User $user Routes require auth */
        $user = $request->user();

        return $user;
    }

    private function done(string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return back();
    }
}
