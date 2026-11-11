<?php

namespace App\Http\Controllers\Officer;

use App\Enums\BidEvaluationStatus;
use App\Enums\PrStatus;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Models\BidEvaluation;
use App\Models\PurchaseRequisition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The Bid Evaluation Form: score every quotation on technical and commercial criteria,
 * pick a recommendation, and send it to the customer for approval.
 */
class EvaluationController extends Controller
{
    public function edit(Request $request, PurchaseRequisition $requisition): View|RedirectResponse
    {
        if ($redirect = $this->notReady($requisition)) {
            return $redirect;
        }

        $evaluation = $requisition->bidEvaluation()->with('scores')->first();
        $this->authorizeEvaluation($requisition, $evaluation);

        $quotations = $requisition->quotations()->with('vendor.organization')->orderBy('total_amount')->get();
        $criteria = config('evaluation.criteria');

        // [quotation id][type][criterion index] => saved score (or '' when empty)
        $saved = [];
        foreach ($evaluation?->scores ?? [] as $score) {
            $index = collect($criteria[$score->criterion_type->value])->search(fn ($c) => $c['name'] === $score->criterion);
            if ($index !== false) {
                $saved[$score->quotation_id][$score->criterion_type->value][$index] = (float) $score->score;
            }
        }

        $initial = [];
        foreach ($quotations as $quotation) {
            foreach ($criteria as $type => $list) {
                foreach ($list as $i => $criterion) {
                    $initial[$quotation->id][$type][$i] = old("scores.{$quotation->id}.{$type}.{$i}", $saved[$quotation->id][$type][$i] ?? '');
                }
            }
        }

        $weights = collect($criteria)->map(fn ($list) => collect($list)->pluck('weight')->all())->all();

        return view('officer.evaluation.edit', [
            'requisition' => $requisition,
            'evaluation' => $evaluation,
            'quotations' => $quotations,
            'criteria' => $criteria,
            'initial' => $initial,
            'weights' => $weights,
            'recommendedId' => old('recommended_quotation_id', $evaluation?->recommended_quotation_id),
            'recommendation' => old('recommendation', $evaluation?->recommendation),
        ]);
    }

    public function save(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        if ($redirect = $this->notReady($requisition)) {
            return $redirect;
        }

        $evaluation = $requisition->bidEvaluation;
        $this->authorizeEvaluation($requisition, $evaluation);

        $quotations = $requisition->quotations()->get();
        $criteria = config('evaluation.criteria');

        $request->validate([
            'scores' => ['nullable', 'array'],
            'scores.*.technical.*' => ['nullable', 'numeric', 'between:0,100'],
            'scores.*.commercial.*' => ['nullable', 'numeric', 'between:0,100'],
            'recommended_quotation_id' => ['nullable', Rule::in($quotations->pluck('id')->all())],
            'recommendation' => ['nullable', 'string', 'max:3000'],
        ], ['scores.*.*.*.between' => 'Scores must be between 0 and 100.']);

        $scores = (array) $request->input('scores', []);

        DB::transaction(function () use ($request, $requisition, &$evaluation, $quotations, $criteria, $scores) {
            // Saving always returns the form to draft until it is submitted again.
            $evaluation ??= new BidEvaluation(['purchase_requisition_id' => $requisition->id]);
            $evaluation->fill([
                'recommended_quotation_id' => $request->input('recommended_quotation_id') ?: null,
                'recommendation' => $request->input('recommendation'),
                'prepared_by' => $request->user()->id,
            ]);
            $evaluation->forceFill(['status' => BidEvaluationStatus::Draft, 'submitted_at' => null])->save();

            $evaluation->scores()->delete();

            $rows = [];
            foreach ($quotations as $quotation) {
                foreach ($criteria as $type => $list) {
                    foreach ($list as $i => $criterion) {
                        $value = $scores[$quotation->id][$type][$i] ?? null;
                        if ($value === null || $value === '') {
                            continue;
                        }
                        $rows[] = [
                            'quotation_id' => $quotation->id,
                            'criterion_type' => $type,
                            'criterion' => $criterion['name'],
                            'weight' => $criterion['weight'],
                            'score' => $value,
                        ];
                    }
                }
            }
            $evaluation->scores()->createMany($rows);
        });

        if ($request->input('action') !== 'submit') {
            return redirect()->route('officer.evaluation.edit', $requisition)->with('success', 'Draft saved.');
        }

        // Submitting needs a complete form.
        $problems = [];
        $needed = $quotations->count() * collect($criteria)->sum(fn ($list) => count($list));
        if ($evaluation->scores()->count() < $needed) {
            $problems[] = 'Score every vendor on every criterion.';
        }
        if (! $evaluation->recommended_quotation_id) {
            $problems[] = 'Choose the vendor you recommend.';
        }
        if (mb_strlen(trim((string) $evaluation->recommendation)) < 10) {
            $problems[] = 'Write a short recommendation for the customer.';
        }

        if ($problems) {
            return redirect()->route('officer.evaluation.edit', $requisition)
                ->with('success', 'Draft saved.')
                ->withErrors(['evaluation' => $problems]);
        }

        DB::transaction(function () use ($request, $requisition, $evaluation) {
            $evaluation->forceFill(['status' => BidEvaluationStatus::Submitted, 'submitted_at' => now(), 'decision_remarks' => null])->save();
            $requisition->quotations()->whereKey($evaluation->recommended_quotation_id)->update(['status' => QuotationStatus::Shortlisted->value]);
            $requisition->transitionTo(PrStatus::AwaitingClientReview, $request->user(), 'Bid evaluation sent for your approval');
        });

        return redirect()->route('officer.requisitions.show', $requisition)
            ->with('success', 'Sent to the customer for approval.');
    }

    /** Scoring only makes sense while the request is in bid evaluation and has quotations. */
    private function notReady(PurchaseRequisition $requisition): ?RedirectResponse
    {
        Gate::authorize('view', $requisition);

        if ($requisition->status !== PrStatus::BidEvaluation) {
            return redirect()->route('officer.requisitions.show', $requisition)
                ->with('warning', 'Move the request to bid evaluation first.');
        }

        if ($requisition->quotations()->doesntExist()) {
            return redirect()->route('officer.requisitions.show', $requisition)
                ->with('warning', 'Record at least one quotation first.');
        }

        return null;
    }

    private function authorizeEvaluation(PurchaseRequisition $requisition, ?BidEvaluation $evaluation): void
    {
        $evaluation
            ? Gate::authorize('update', $evaluation)
            : Gate::authorize('create', [BidEvaluation::class, $requisition]);
    }
}
