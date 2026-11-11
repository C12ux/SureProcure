<?php

namespace App\Http\Controllers\Client;

use App\Enums\BidEvaluationStatus;
use App\Enums\PrStatus;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Models\PurchaseRequisition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** The customer's side of the bid evaluation: approve it, ask for changes, or reject it. */
class ReviewController extends Controller
{
    public function decide(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        $evaluation = $requisition->bidEvaluation;
        abort_unless($evaluation, 404);

        // Only a customer owner or approver, and only while the evaluation awaits a decision.
        Gate::authorize('decide', $evaluation);

        $data = $request->validate([
            'action' => ['required', Rule::in(['approve', 'changes', 'reject'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $note = filled($data['note'] ?? null) ? trim($data['note']) : null;

        if (in_array($data['action'], ['changes', 'reject'], true) && ! $note) {
            throw ValidationException::withMessages([
                'note' => $data['action'] === 'changes' ? 'Tell your officer what to change.' : 'Give a reason for rejecting.',
            ]);
        }

        $to = match ($data['action']) {
            'approve' => PrStatus::Approved,
            'changes' => PrStatus::NeedsClarification,
            default => PrStatus::Rejected,
        };

        Gate::authorize('transition', [$requisition, $to]);

        DB::transaction(function () use ($request, $requisition, $evaluation, $data, $note, $to) {
            if ($data['action'] === 'approve') {
                $evaluation->forceFill([
                    'status' => BidEvaluationStatus::Approved,
                    'decided_by' => $request->user()->id,
                    'decided_at' => now(),
                    'decision_remarks' => $note,
                ])->save();

                $requisition->quotations()->whereKey($evaluation->recommended_quotation_id)
                    ->update(['status' => QuotationStatus::Awarded->value]);
                $requisition->quotations()->where('id', '!=', $evaluation->recommended_quotation_id)
                    ->update(['status' => QuotationStatus::Rejected->value]);
            } elseif ($data['action'] === 'reject') {
                $evaluation->forceFill([
                    'status' => BidEvaluationStatus::Rejected,
                    'decided_by' => $request->user()->id,
                    'decided_at' => now(),
                    'decision_remarks' => $note,
                ])->save();
            } else {
                // Stays submitted; the officer revises it and sends it again.
                $evaluation->forceFill(['decision_remarks' => $note])->save();
            }

            $requisition->transitionTo($to, $request->user(), $note ?? 'Bid evaluation approved');
        });

        return redirect()->route('requisitions.show', $requisition)->with('success', match ($data['action']) {
            'approve' => 'Approved. Your officer will now raise the purchase order.',
            'changes' => 'Sent back to your officer with your note.',
            default => 'Rejected.',
        });
    }

    /** A plain note in the activity feed, visible to the officer. */
    public function reply(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        Gate::authorize('view', $requisition);

        if ($requisition->status === PrStatus::Draft || $requisition->status->isTerminal()) {
            return back()->with('warning', 'Notes can only be added while a request is being worked on.');
        }

        $data = $request->validate(['message' => ['required', 'string', 'max:2000']]);

        $requisition->statusHistory()->create([
            'from_status' => $requisition->status,
            'to_status' => $requisition->status,
            'changed_by' => $request->user()->id,
            'note' => $data['message'],
        ]);
        $requisition->touch();

        return back()->with('success', 'Note sent to your officer.');
    }
}
