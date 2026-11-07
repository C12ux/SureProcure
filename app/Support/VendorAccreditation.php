<?php

namespace App\Support;

use App\Models\Vendor;

/**
 * Works out how complete a vendor's application is.
 * One list of checks, grouped into the three steps shown to the vendor.
 */
class VendorAccreditation
{
    public const SECTIONS = [
        'company' => 'Company profile',
        'capability' => 'Capability and compliance',
        'documents' => 'Documents',
    ];

    /** @var array<int, array{section: string, label: string, ok: bool}> */
    private array $checks = [];

    public function __construct(private Vendor $vendor)
    {
        $vendor->loadMissing(['organization', 'categories', 'clients', 'documents']);
        $this->build();
    }

    private function build(): void
    {
        $v = $this->vendor;
        $o = $v->organization;

        // Company profile (form sections A to F)
        $this->add('company', 'Business address', filled($o?->business_address));
        $this->add('company', 'Year established', $o?->year_established !== null);
        $this->add('company', 'Type of organization', filled($o?->org_type));
        $this->add('company', 'TIN', filled($o?->tin));
        $this->add('company', 'Authorized representative', filled($v->representative_name));
        $this->add('company', 'Representative’s position', filled($v->representative_title));
        $this->add('company', 'Mobile number', filled($v->mobile_number));
        $this->add('company', 'VAT registration', $v->vat_registered !== null);
        $this->add('company', 'Bank reference', filled($v->bank_reference));
        $this->add('company', 'Products and services offered', filled($v->products_services));
        $this->add('company', 'At least one category you serve', $v->categories->isNotEmpty());
        $this->add('company', 'At least 3 clients in your portfolio', $v->clients->count() >= 3);

        // Capability and compliance (sections G and H)
        $this->add('capability', 'Years in industry', $v->years_in_industry !== null);
        $this->add('capability', 'Industries served', ! empty($v->industries_served));
        $this->add('capability', 'Number of employees', $v->employees_count !== null);
        $this->add('capability', 'Production or service capacity', filled($v->service_capacity));
        $this->add('capability', 'Facilities and equipment', filled($v->facilities_equipment));
        $this->add('capability', 'Legal disputes answer', $v->has_legal_disputes !== null);
        if ($v->has_legal_disputes) {
            $this->add('capability', 'Details of the legal disputes', filled($v->legal_disputes_details));
        }
        $this->add('capability', 'HSE compliance answer', $v->complies_hse !== null);
        if ($v->complies_hse) {
            $this->add('capability', 'HSE certifications or policies', filled($v->hse_details));
        }

        // Documents (sections C and I)
        foreach (\App\Enums\DocumentType::vendorChecklist() as $row) {
            if (! $row['required']) {
                continue;
            }

            $count = $v->documents->filter(fn ($d) => $d->type === $row['type'])->count();
            $label = $row['type']->label();
            $this->add('documents', $row['min'] > 1 ? "{$label} ({$count} of {$row['min']})" : $label, $count >= $row['min']);
        }
    }

    private function add(string $section, string $label, bool $ok): void
    {
        $this->checks[] = ['section' => $section, 'label' => $label, 'ok' => $ok];
    }

    /** @return array<string, array{label: string, checks: array, passed: int, total: int, done: bool, missing: array}> */
    public function sections(): array
    {
        $out = [];

        foreach (self::SECTIONS as $key => $label) {
            $checks = array_values(array_filter($this->checks, fn ($c) => $c['section'] === $key));
            $passed = count(array_filter($checks, fn ($c) => $c['ok']));

            $out[$key] = [
                'label' => $label,
                'checks' => $checks,
                'passed' => $passed,
                'total' => count($checks),
                'done' => $passed === count($checks),
                'missing' => array_values(array_map(fn ($c) => $c['label'], array_filter($checks, fn ($c) => ! $c['ok']))),
            ];
        }

        return $out;
    }

    public function isComplete(): bool
    {
        return collect($this->checks)->every(fn ($c) => $c['ok']);
    }

    public function percent(): int
    {
        $total = count($this->checks);

        return $total === 0 ? 0 : (int) round(count(array_filter($this->checks, fn ($c) => $c['ok'])) / $total * 100);
    }
}
