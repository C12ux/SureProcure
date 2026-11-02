<?php

namespace App\Http\Controllers\Client;

use App\Enums\PrPriority;
use App\Enums\PrStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\RequisitionRequest;
use App\Models\Category;
use App\Models\Organization;
use App\Models\PurchaseRequisition;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Customer-side purchase requisitions. Every action checks a policy;
 * lists are always scoped with PurchaseRequisition::visibleTo($user).
 */
class RequisitionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $groups = PrStatus::groups();
        $filter = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));

        $query = PurchaseRequisition::visibleTo($user)->with('category')->latest();

        if (isset($groups[$filter])) {
            $query->withStatus(...$groups[$filter]);
        } else {
            $filter = 'all';
        }

        if ($search !== '') {
            $term = '%'.addcslashes($search, '%_\\').'%';
            $query->where(fn (Builder $q) => $q->where('title', 'ilike', $term)->orWhere('pr_number', 'ilike', $term));
        }

        return view('requisitions.index', [
            'requisitions' => $query->paginate(12)->withQueryString(),
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', [PurchaseRequisition::class, $this->organization($request)]);

        return view('requisitions.create', [
            'requisition' => new PurchaseRequisition,
            'categories' => $this->categories(),
        ]);
    }

    public function store(RequisitionRequest $request): RedirectResponse
    {
        $user = $request->user();
        $organization = $this->organization($request);
        Gate::authorize('create', [PurchaseRequisition::class, $organization]);

        $data = $request->validated();
        $items = array_values($data['items']);
        unset($data['items']);

        $requisition = DB::transaction(function () use ($data, $items, $user, $organization) {
            $pr = PurchaseRequisition::create($data + [
                'organization_id' => $organization->id,
                'requested_by' => $user->id,
            ]);
            $pr->items()->createMany($items);

            return $pr;
        });

        if ($request->input('action') === 'submit') {
            return $this->submitAndRedirect($requisition, $user);
        }

        return redirect()->route('requisitions.show', $requisition)->with('success', 'Draft saved.');
    }

    public function show(Request $request, PurchaseRequisition $requisition): View
    {
        Gate::authorize('view', $requisition);

        $requisition->load(['organization', 'requester', 'officer', 'category', 'items', 'statusHistory.changedBy']);

        return view('requisitions.show', [
            'requisition' => $requisition,
            'can' => [
                'update' => Gate::allows('update', $requisition),
                'submit' => Gate::allows('transition', [$requisition, PrStatus::Submitted]),
                'cancel' => Gate::allows('transition', [$requisition, PrStatus::Cancelled]),
                'delete' => Gate::allows('delete', $requisition),
            ],
        ]);
    }

    public function edit(Request $request, PurchaseRequisition $requisition): View
    {
        Gate::authorize('update', $requisition);

        return view('requisitions.edit', [
            'requisition' => $requisition->load('items'),
            'categories' => $this->categories(),
        ]);
    }

    public function update(RequisitionRequest $request, PurchaseRequisition $requisition): RedirectResponse
    {
        Gate::authorize('update', $requisition);

        $data = $request->validated();
        $items = array_values($data['items']);
        unset($data['items']);

        DB::transaction(function () use ($requisition, $data, $items) {
            $requisition->update($data);
            $requisition->items()->delete();
            $requisition->items()->createMany($items);
        });

        if ($request->input('action') === 'submit' && $requisition->status === PrStatus::Draft) {
            return $this->submitAndRedirect($requisition, $request->user());
        }

        return redirect()->route('requisitions.show', $requisition)->with('success', 'Changes saved.');
    }

    public function submit(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        return $this->submitAndRedirect($requisition, $request->user());
    }

    public function cancel(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        Gate::authorize('transition', [$requisition, PrStatus::Cancelled]);

        $requisition->transitionTo(PrStatus::Cancelled, $request->user(), 'Cancelled by customer');

        return redirect()->route('requisitions.show', $requisition)->with('success', 'Request cancelled.');
    }

    public function destroy(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        Gate::authorize('delete', $requisition);

        $requisition->delete(); // soft delete

        return redirect()->route('requisitions.index')->with('success', 'Draft deleted.');
    }

    /** CSV of every requisition the user can see. */
    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $filename = 'sureprocure-requisitions-'.now()->format('Ymd').'.csv';

        // Stop spreadsheet apps from running text that starts like a formula.
        $safe = fn ($value) => is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;

        return response()->streamDownload(function () use ($user, $safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // lets Excel read UTF-8
            fputcsv($out, ['Number', 'Title', 'Category', 'Status', 'Priority', 'Budget', 'Currency', 'Needed by', 'Submitted at', 'Created at', 'Officer']);

            PurchaseRequisition::visibleTo($user)
                ->with(['category', 'officer'])
                ->orderBy('id')
                ->chunk(200, function ($rows) use ($out, $safe) {
                    foreach ($rows as $pr) {
                        fputcsv($out, [
                            $pr->pr_number,
                            $safe($pr->title),
                            $safe($pr->category?->name),
                            $pr->status->label(),
                            $pr->priority->label(),
                            $pr->budget,
                            $pr->currency,
                            $pr->needed_by_date?->toDateString(),
                            $pr->submitted_at?->toDateTimeString(),
                            $pr->created_at?->toDateTimeString(),
                            $safe($pr->officer?->name),
                        ]);
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ---- helpers --------------------------------------------------------

    private function organization(Request $request): Organization
    {
        return $request->user()->organizations->first()
            ?? abort(403, 'Your account is not linked to a company.');
    }

    private function categories()
    {
        return Category::active()->orderBy('name')->get();
    }

    private function submitAndRedirect(PurchaseRequisition $requisition, User $user): RedirectResponse
    {
        Gate::authorize('transition', [$requisition, PrStatus::Submitted]);

        // Business rule: an active subscription is needed to submit.
        // Controlled by REQUIRE_SUBSCRIPTION in .env (off while you are still setting up plans).
        if (config('sureprocure.require_subscription') && ! $requisition->organization->hasActiveSubscription()) {
            return redirect()->route('requisitions.show', $requisition)
                ->with('warning', 'Saved as a draft. Your company needs an active subscription before a request can be submitted.');
        }

        $requisition->transitionTo(PrStatus::Submitted, $user, 'Submitted by customer');

        return redirect()->route('requisitions.show', $requisition)
            ->with('success', 'Submitted. Your procurement officer will review it shortly.');
    }
}
