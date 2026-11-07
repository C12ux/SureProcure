<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccreditationStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Vendor;
use App\Support\VendorAccreditation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $filter = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));

        $tabs = [
            'review' => [AccreditationStatus::UnderReview],
            'accredited' => [AccreditationStatus::Accredited],
            'in_progress' => [AccreditationStatus::Pending],
            'rejected' => [AccreditationStatus::Rejected],
        ];

        $query = Vendor::with('organization')->latest('updated_at');

        if (isset($tabs[$filter])) {
            $query->whereIn('accreditation_status', array_map(fn ($s) => $s->value, $tabs[$filter]));
        } else {
            $filter = 'all';
        }

        if ($search !== '') {
            $term = '%'.addcslashes($search, '%_\\').'%';
            $query->whereHas('organization', fn (Builder $o) => $o
                ->where('legal_name', 'ilike', $term)
                ->orWhere('trade_name', 'ilike', $term));
        }

        return view('admin.vendors.index', [
            'vendors' => $query->paginate(15)->withQueryString(),
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    public function show(Vendor $vendor): View
    {
        Gate::authorize('view', $vendor);

        $vendor->load(['organization', 'categories', 'clients', 'certifications', 'reviewer', 'documents.verifier']);

        return view('admin.vendors.show', [
            'vendor' => $vendor,
            'accreditation' => new VendorAccreditation($vendor),
        ]);
    }

    /** Approve, send back for changes, or reject an application that is under review. */
    public function decide(Request $request, Vendor $vendor): RedirectResponse
    {
        Gate::authorize('review', $vendor);

        if ($vendor->accreditation_status !== AccreditationStatus::UnderReview) {
            return back()->with('warning', 'This application is not waiting for review.');
        }

        $data = $request->validate([
            'action' => ['required', Rule::in(['approve', 'changes', 'reject'])],
            'note' => ['nullable', 'string', 'max:2000'],
            'expires_on' => ['nullable', 'date', 'after:today'],
        ]);

        if (in_array($data['action'], ['changes', 'reject'], true) && blank($data['note'] ?? null)) {
            throw ValidationException::withMessages([
                'note' => $data['action'] === 'changes'
                    ? 'Say what the vendor needs to fix.'
                    : 'Give a reason for the rejection.',
            ]);
        }

        $admin = $request->user();
        $note = $data['note'] ?? null;

        switch ($data['action']) {
            case 'approve':
                $vendor->accredit($admin, filled($data['expires_on'] ?? null) ? Carbon::parse($data['expires_on']) : null);
                $vendor->forceFill(['remarks' => $note])->save();
                $message = 'Vendor accredited.';
                break;
            case 'changes':
                $vendor->requestChanges($admin, $note);
                $message = 'Sent back to the vendor for changes.';
                break;
            default:
                $vendor->reject($admin, $note);
                $message = 'Application rejected.';
        }

        ActivityLog::record('vendor.'.$data['action'], $vendor, ['note' => $note]);

        return redirect()->route('admin.vendors.show', $vendor)->with('success', $message);
    }

    /** Mark one uploaded document as verified or rejected. */
    public function reviewDocument(Request $request, Document $document): RedirectResponse
    {
        Gate::authorize('verify', $document);
        abort_unless($document->documentable instanceof Vendor, 404);

        $action = $request->validate(['action' => ['required', Rule::in(['verify', 'reject'])]])['action'];

        $action === 'verify' ? $document->verify($request->user()) : $document->reject($request->user());

        return back()->with('success', $action === 'verify' ? 'Document verified.' : 'Document rejected.');
    }
}
