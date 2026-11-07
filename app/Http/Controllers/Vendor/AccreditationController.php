<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\AccreditationStatus;
use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Document;
use App\Models\Vendor;
use App\Support\VendorAccreditation;
use App\Support\VendorOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The vendor's side of accreditation, following the Supplier Accreditation Form:
 * 1. company profile, 2. capability and compliance, 3. documents, then submit.
 * Saving is lenient so progress is never lost; completeness is checked on submit.
 */
class AccreditationController extends Controller
{
    public function show(Request $request): View
    {
        $vendor = $this->vendor($request);
        Gate::authorize('view', $vendor);

        return view('vendor.accreditation.show', [
            'vendor' => $vendor->load('organization'),
            'accreditation' => new VendorAccreditation($vendor),
            'canEdit' => Gate::allows('update', $vendor),
        ]);
    }

    // ---- Step 1: company profile ----------------------------------------

    public function company(Request $request): View
    {
        $vendor = $this->editable($request)->load(['organization', 'categories', 'clients']);

        $clients = old('clients', $vendor->clients->map(fn ($c) => [
            'client_name' => $c->client_name,
            'project_description' => $c->project_description,
        ])->all());

        return view('vendor.accreditation.company', [
            'vendor' => $vendor,
            'organization' => $vendor->organization,
            'categories' => Category::active()->orderBy('name')->get(),
            'selectedCategories' => collect(old('category_ids', $vendor->categories->pluck('id')->all()))->map(fn ($id) => (int) $id)->all(),
            'orgTypes' => VendorOptions::ORG_TYPES,
            'clientRows' => array_values($clients) ?: [['client_name' => '', 'project_description' => '']],
        ]);
    }

    public function saveCompany(Request $request): RedirectResponse
    {
        $vendor = $this->editable($request);

        $data = $request->validate([
            'trade_name' => ['nullable', 'string', 'max:255'],
            'business_address' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'url', 'max:255'],
            'year_established' => ['nullable', 'integer', 'between:1800,'.now()->year],
            'org_type' => ['nullable', Rule::in(array_keys(VendorOptions::ORG_TYPES))],
            'tin' => ['nullable', 'string', 'max:30'],

            'representative_name' => ['nullable', 'string', 'max:255'],
            'representative_title' => ['nullable', 'string', 'max:150'],
            'mobile_number' => ['nullable', 'string', 'max:50'],
            'landline' => ['nullable', 'string', 'max:50'],

            'vat_registered' => ['nullable', 'boolean'],
            'bank_reference' => ['nullable', 'string', 'max:255'],
            'products_services' => ['nullable', 'string', 'max:5000'],

            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [Rule::exists('categories', 'id')->where('is_active', true)],

            'clients' => ['nullable', 'array', 'max:5'],
            'clients.*.client_name' => ['nullable', 'string', 'max:255'],
            'clients.*.project_description' => ['nullable', 'string', 'max:500'],
        ], [
            'website.url' => 'Enter the full website address, starting with https://',
            'year_established.between' => 'Enter a valid year.',
        ]);

        DB::transaction(function () use ($vendor, $data) {
            $vendor->organization->update(Arr::only($data, ['trade_name', 'business_address', 'website', 'year_established', 'org_type', 'tin']));

            $vendor->update(Arr::only($data, [
                'representative_name', 'representative_title', 'mobile_number', 'landline',
                'vat_registered', 'bank_reference', 'products_services',
            ]));

            $vendor->categories()->sync($data['category_ids'] ?? []);

            $vendor->clients()->delete();
            $rows = collect($data['clients'] ?? [])
                ->filter(fn ($c) => filled($c['client_name'] ?? null))
                ->map(fn ($c) => ['client_name' => $c['client_name'], 'project_description' => $c['project_description'] ?? null])
                ->values()->all();
            $vendor->clients()->createMany($rows);
        });

        return redirect()->route('vendor.accreditation.show')->with('success', 'Company profile saved.');
    }

    // ---- Step 2: capability and compliance --------------------------------

    public function capability(Request $request): View
    {
        $vendor = $this->editable($request)->load('certifications');

        $industries = $vendor->industries_served ?? [];
        $certNames = $vendor->certifications->pluck('name')->all();

        return view('vendor.accreditation.capability', [
            'vendor' => $vendor,
            'industryOptions' => VendorOptions::INDUSTRIES,
            'selectedIndustries' => old('industries', array_values(array_intersect($industries, VendorOptions::INDUSTRIES))),
            'industryOther' => old('industry_other', implode(', ', array_diff($industries, VendorOptions::INDUSTRIES))),
            'isoOptions' => VendorOptions::ISO,
            'selectedIso' => old('iso', array_values(array_intersect($certNames, VendorOptions::ISO))),
            'otherCertifications' => old('other_certifications', implode("\n", array_diff($certNames, VendorOptions::ISO))),
        ]);
    }

    public function saveCapability(Request $request): RedirectResponse
    {
        $vendor = $this->editable($request);

        $data = $request->validate([
            'years_in_industry' => ['nullable', 'integer', 'between:0,200'],
            'industries' => ['nullable', 'array'],
            'industries.*' => [Rule::in(VendorOptions::INDUSTRIES)],
            'industry_other' => ['nullable', 'string', 'max:255'],
            'employees_count' => ['nullable', 'integer', 'between:0,10000000'],
            'service_capacity' => ['nullable', 'string', 'max:2000'],
            'facilities_equipment' => ['nullable', 'string', 'max:2000'],

            'iso' => ['nullable', 'array'],
            'iso.*' => [Rule::in(VendorOptions::ISO)],
            'other_certifications' => ['nullable', 'string', 'max:2000'],

            'has_legal_disputes' => ['nullable', 'boolean'],
            'legal_disputes_details' => ['nullable', 'string', 'max:3000', 'required_if:has_legal_disputes,1'],
            'complies_hse' => ['nullable', 'boolean'],
            'hse_details' => ['nullable', 'string', 'max:3000', 'required_if:complies_hse,1'],
        ], [
            'legal_disputes_details.required_if' => 'Please describe the legal disputes or sanctions.',
            'hse_details.required_if' => 'List your HSE certifications or policies.',
        ]);

        $industries = array_values(array_filter([...($data['industries'] ?? []), trim((string) ($data['industry_other'] ?? ''))]));

        $certNames = collect([
            ...($data['iso'] ?? []),
            ...preg_split('/\R/', (string) ($data['other_certifications'] ?? '')),
        ])->map(fn ($n) => Str::limit(trim((string) $n), 150, ''))->filter()->unique()->take(15)->values();

        DB::transaction(function () use ($vendor, $data, $industries, $certNames) {
            $vendor->update([
                'years_in_industry' => $data['years_in_industry'] ?? null,
                'industries_served' => $industries,
                'employees_count' => $data['employees_count'] ?? null,
                'service_capacity' => $data['service_capacity'] ?? null,
                'facilities_equipment' => $data['facilities_equipment'] ?? null,
                'has_legal_disputes' => $data['has_legal_disputes'] ?? null,
                'legal_disputes_details' => ($data['has_legal_disputes'] ?? null) ? ($data['legal_disputes_details'] ?? null) : null,
                'complies_hse' => $data['complies_hse'] ?? null,
                'hse_details' => ($data['complies_hse'] ?? null) ? ($data['hse_details'] ?? null) : null,
            ]);

            $vendor->certifications()->delete();
            $vendor->certifications()->createMany($certNames->map(fn ($name) => ['name' => $name])->all());
        });

        return redirect()->route('vendor.accreditation.show')->with('success', 'Capability and compliance saved.');
    }

    // ---- Step 3: documents -------------------------------------------------

    public function documents(Request $request): View
    {
        $vendor = $this->vendor($request);
        Gate::authorize('view', $vendor);

        return view('vendor.accreditation.documents', [
            'vendor' => $vendor->load('documents'),
            'checklist' => DocumentType::vendorChecklist(),
            'canEdit' => Gate::allows('update', $vendor),
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $vendor = $this->editable($request);

        $allowed = array_map(fn ($row) => $row['type']->value, DocumentType::vendorChecklist());
        $type = (string) $request->input('type');
        abort_unless(in_array($type, $allowed, true), 422);

        // One error bag per document row, so a bad file only shows under its own row.
        $bag = 'upload_'.$type;

        $request->validateWithBag($bag, [
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ], [
            'file.required' => 'Choose a file to upload.',
            'file.max' => 'The file must be 10 MB or smaller.',
            'file.mimes' => 'Upload a PDF, an image (JPG or PNG) or a Word file.',
            'file.uploaded' => 'The file could not be uploaded. It may be larger than the server allows.',
        ]);

        if ($vendor->documents()->where('type', $type)->count() >= 10) {
            return back()->withErrors(['file' => 'You can upload up to 10 files for each item.'], $bag);
        }

        $file = $request->file('file');
        $size = $file->getSize();
        $mime = $file->getMimeType();
        $path = 'vendors/'.$vendor->id.'/'.$type.'/'.Str::uuid().'.'.($file->extension() ?: 'bin');

        Storage::disk(Document::DISK)->putFileAs(dirname($path), $file, basename($path));

        $vendor->documents()->create([
            'type' => $type,
            'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
            'storage_path' => $path,
            'mime_type' => $mime,
            'size_bytes' => $size,
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()->route('vendor.accreditation.documents')->with('success', 'File uploaded.');
    }

    // ---- Submit ------------------------------------------------------------

    public function submit(Request $request): RedirectResponse
    {
        $vendor = $this->editable($request);

        if (! in_array($vendor->accreditation_status, [AccreditationStatus::Pending, AccreditationStatus::Rejected], true)) {
            return back()->with('warning', 'This application cannot be submitted right now.');
        }

        if (! (new VendorAccreditation($vendor))->isComplete()) {
            return back()->with('warning', 'Finish every step before you submit.');
        }

        $data = $request->validate([
            'declared_by' => ['required', 'string', 'max:255'],
            'certify' => ['accepted'],
        ], [
            'declared_by.required' => 'Type your full name to sign the declaration.',
            'certify.accepted' => 'Please confirm the declaration.',
        ]);

        $vendor->submitForReview($data['declared_by']);
        ActivityLog::record('vendor.submitted', $vendor);

        return redirect()->route('vendor.accreditation.show')
            ->with('success', 'Submitted. SureProcure will review your application and get back to you.');
    }

    // ---- helpers -----------------------------------------------------------

    private function vendor(Request $request): Vendor
    {
        return $request->user()->organizations->first()?->vendor
            ?? abort(403, 'Your account is not linked to a vendor company.');
    }

    /** The vendor, after checking that this user may edit the application right now. */
    private function editable(Request $request): Vendor
    {
        $vendor = $this->vendor($request);
        Gate::authorize('update', $vendor);

        return $vendor;
    }
}
