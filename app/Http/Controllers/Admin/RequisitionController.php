<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PrStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PurchaseRequisition;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Admin view of every customer request: assign an officer, move it along.
 * Customers' drafts are private, so drafts are never listed or managed here.
 */
class RequisitionController extends Controller
{
    public function index(Request $request): View
    {
        $filter = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));
        $groups = PrStatus::groups();

        $query = PurchaseRequisition::visibleTo($request->user())
            ->where('status', '!=', PrStatus::Draft->value)
            ->with(['organization', 'officer'])
            ->latest('updated_at');

        if ($filter === 'unassigned') {
            $query->whereNull('assigned_officer_id')->withStatus(...PrStatus::active());
        } elseif ($filter !== 'drafts' && isset($groups[$filter])) {
            $query->withStatus(...$groups[$filter]);
        } else {
            $filter = 'all';
        }

        if ($search !== '') {
            $term = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'ilike', $term)
                    ->orWhere('pr_number', 'ilike', $term)
                    ->orWhereHas('organization', fn (Builder $o) => $o->where('legal_name', 'ilike', $term));
            });
        }

        return view('admin.requisitions.index', [
            'requisitions' => $query->paginate(15)->withQueryString(),
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    public function show(Request $request, PurchaseRequisition $requisition): View
    {
        $this->guard($requisition);
        Gate::authorize('view', $requisition);

        $requisition->load(['organization.users', 'requester', 'officer', 'category', 'items', 'statusHistory.changedBy']);

        $transitions = collect($requisition->status->allowedTransitions())
            ->filter(fn (PrStatus $to) => Gate::allows('transition', [$requisition, $to]))
            ->values();

        $officers = User::where('role', UserRole::ProcurementOfficer->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.requisitions.show', compact('requisition', 'transitions', 'officers'));
    }

    public function assign(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        $this->guard($requisition);
        Gate::authorize('assign', $requisition);

        if ($requisition->status->isTerminal()) {
            return back()->with('warning', 'This request is finished, so it cannot be reassigned.');
        }

        $data = $request->validate([
            'officer_id' => ['required', Rule::exists('users', 'id')
                ->where('role', UserRole::ProcurementOfficer->value)
                ->where('is_active', true)
                ->whereNull('deleted_at')],
        ]);

        $admin = $request->user();
        $officer = User::findOrFail($data['officer_id']);
        $previous = $requisition->officer;

        DB::transaction(function () use ($requisition, $officer, $previous, $admin) {
            $requisition->forceFill(['assigned_officer_id' => $officer->id])->save();

            if ($requisition->status === PrStatus::Submitted) {
                // Picking up a new request also starts the review.
                $requisition->transitionTo(PrStatus::InReview, $admin, "Assigned to {$officer->name}");
            } else {
                $requisition->statusHistory()->create([
                    'from_status' => $requisition->status,
                    'to_status' => $requisition->status,
                    'changed_by' => $admin->id,
                    'note' => $previous ? "Reassigned from {$previous->name} to {$officer->name}" : "Assigned to {$officer->name}",
                ]);
            }
        });

        return redirect()->route('admin.requisitions.show', $requisition)
            ->with('success', "Assigned to {$officer->name}.");
    }

    public function transition(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        $this->guard($requisition);

        $data = $request->validate([
            'to' => ['required', Rule::enum(PrStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $to = PrStatus::from($data['to']);
        Gate::authorize('transition', [$requisition, $to]);

        // The customer sees these two moves, so they need an explanation.
        if (in_array($to, [PrStatus::NeedsClarification, PrStatus::Rejected], true) && blank($data['note'] ?? null)) {
            throw ValidationException::withMessages(['note' => 'Add a note so the customer knows why.']);
        }

        // Every working stage needs someone responsible for it.
        $needsOfficer = ! in_array($to, [PrStatus::NeedsClarification, PrStatus::Rejected, PrStatus::Cancelled], true);
        if ($needsOfficer && $requisition->assigned_officer_id === null) {
            throw ValidationException::withMessages(['to' => 'Assign a procurement officer first.']);
        }

        $requisition->transitionTo($to, $request->user(), $data['note'] ?? null);

        return redirect()->route('admin.requisitions.show', $requisition)
            ->with('success', "Moved to {$to->staffLabel()}.");
    }

    /** A customer's draft is private until they submit it. */
    private function guard(PurchaseRequisition $requisition): void
    {
        abort_if($requisition->status === PrStatus::Draft, 404);
    }
}
