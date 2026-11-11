<?php

namespace App\Http\Controllers\Officer;

use App\Enums\InvitationStatus;
use App\Http\Controllers\Controller;
use App\Models\BidEvaluation;
use App\Models\Document;
use App\Models\Quotation;
use App\Models\PurchaseRequisition;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * The officer secures quotations from the invited vendors and records them here.
 * (A vendor-side quote screen can come later; this matches the "secure proposal" step.)
 */
class QuotationController extends Controller
{
    public function store(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        $data = $request->validate([
            'vendor_id' => ['required', 'integer', Rule::exists('vendors', 'id')],
            'total_amount' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'lead_time_days' => ['nullable', 'integer', 'between:0,3650'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
            'payment_terms' => ['nullable', 'string', 'max:1000'],
            'delivery_terms' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ], [
            'vendor_id.required' => 'Choose which vendor sent this quotation.',
            'total_amount.required' => 'Enter the total quoted amount.',
            'valid_until.after_or_equal' => 'The validity date cannot be in the past.',
            'file.max' => 'The file must be 10 MB or smaller.',
            'file.mimes' => 'Upload a PDF, an image (JPG or PNG) or a Word file.',
            'file.uploaded' => 'The file could not be uploaded. It may be larger than the server allows.',
        ]);

        $vendor = Vendor::findOrFail($data['vendor_id']);

        // Requires: request in sourcing, vendor accredited and invited, officer assigned to this request.
        Gate::authorize('create', [Quotation::class, $requisition, $vendor]);

        if ($requisition->quotations()->where('vendor_id', $vendor->id)->exists()) {
            throw ValidationException::withMessages(['vendor_id' => 'This vendor already has a quotation on this request.']);
        }

        DB::transaction(function () use ($request, $requisition, $vendor, $data) {
            $quotation = $requisition->quotations()->create([
                'vendor_id' => $vendor->id,
                'total_amount' => $data['total_amount'],
                'currency' => $requisition->currency,
                'lead_time_days' => $data['lead_time_days'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'delivery_terms' => $data['delivery_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'submitted_by' => $request->user()->id,
            ]);

            $requisition->invitations()->where('vendor_id', $vendor->id)->update([
                'status' => InvitationStatus::Quoted->value,
                'responded_at' => now(),
            ]);

            if ($file = $request->file('file')) {
                Gate::authorize('upload', [Document::class, $quotation]);

                $size = $file->getSize();
                $mime = $file->getMimeType();
                $path = 'quotations/'.$quotation->id.'/'.Str::uuid().'.'.($file->extension() ?: 'bin');

                Storage::disk(Document::DISK)->putFileAs(dirname($path), $file, basename($path));

                $quotation->documents()->create([
                    'type' => 'quotation',
                    'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
                    'storage_path' => $path,
                    'mime_type' => $mime,
                    'size_bytes' => $size,
                    'uploaded_by' => $request->user()->id,
                ]);
            }

            $requisition->statusHistory()->create([
                'from_status' => $requisition->status,
                'to_status' => $requisition->status,
                'changed_by' => $request->user()->id,
                'note' => "Quotation received from {$vendor->name}",
            ]);
        });

        return back()->with('success', 'Quotation recorded.');
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        Gate::authorize('delete', $quotation);

        DB::transaction(function () use ($quotation) {
            // The evaluation must not point at a quotation that no longer exists.
            BidEvaluation::where('recommended_quotation_id', $quotation->id)->update(['recommended_quotation_id' => null]);

            foreach ($quotation->documents as $document) {
                try {
                    Storage::disk(Document::DISK)->delete($document->storage_path);
                } catch (\Throwable $e) {
                    report($e);
                }
                $document->delete();
            }

            $quotation->purchaseRequisition->invitations()
                ->where('vendor_id', $quotation->vendor_id)
                ->update(['status' => InvitationStatus::Invited->value, 'responded_at' => null]);

            $quotation->delete();
        });

        return back()->with('success', 'Quotation removed.');
    }
}
