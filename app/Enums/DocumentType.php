<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;
use Illuminate\Support\Str;

enum DocumentType: string
{
    use HasOptions;

    // Vendor accreditation
    case SecDtiRegistration = 'sec_dti_registration';
    case BusinessPermit = 'business_permit';
    case Bir2303 = 'bir_2303';
    case PhilgepsRegistration = 'philgeps_registration';
    case ArticlesOfIncorporation = 'articles_of_incorporation';
    case GeneralInformationSheet = 'general_information_sheet';
    case AuditedFinancials = 'audited_financials';
    case OtherLicense = 'other_license';
    case CompanyProfile = 'company_profile';
    case OrganizationalChart = 'organizational_chart';
    case ProductCatalog = 'product_catalog';
    case ClientReference = 'client_reference';
    case CodeOfEthics = 'code_of_ethics';
    case HseCertification = 'hse_certification';

    // Procurement partner accreditation
    case Cv = 'cv';
    case GovernmentId = 'government_id';
    case Certification = 'certification';
    case Portfolio = 'portfolio';
    case ReferenceLetter = 'reference_letter';

    // Transaction documents
    case Quotation = 'quotation';
    case PurchaseOrder = 'purchase_order';
    case Contract = 'contract';
    case ProofOfPayment = 'proof_of_payment';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SecDtiRegistration => 'SEC / DTI Registration',
            self::BusinessPermit => 'Mayor’s / Business Permit',
            self::Bir2303 => 'BIR Registration (Form 2303)',
            self::PhilgepsRegistration => 'PhilGEPS Registration',
            self::ArticlesOfIncorporation => 'Articles of Incorporation / Partnership',
            self::GeneralInformationSheet => 'General Information Sheet (GIS)',
            self::AuditedFinancials => 'Audited Financial Statements',
            self::OtherLicense => 'Other Licenses / Certifications',
            self::CompanyProfile => 'Company Profile',
            self::OrganizationalChart => 'Organizational Chart',
            self::ProductCatalog => 'Product Catalog / Service Portfolio',
            self::ClientReference => 'Client References',
            self::CodeOfEthics => 'Signed Code of Ethics & Anti-Bribery Commitment',
            self::HseCertification => 'HSE Certifications or Policies',
            self::Cv => 'Curriculum Vitae',
            self::GovernmentId => 'Valid Government ID',
            default => Str::headline($this->value),
        };
    }

    /**
     * Everything the Supplier Accreditation Form asks a vendor to attach (sections C and I).
     * min = how many files are needed; required = must be met before the application can be submitted.
     *
     * @return array<int, array{type: self, min: int, required: bool, hint: string}>
     */
    public static function vendorChecklist(): array
    {
        return [
            ['type' => self::SecDtiRegistration, 'min' => 1, 'required' => true, 'hint' => 'SEC registration for corporations and partnerships, DTI for sole proprietors.'],
            ['type' => self::BusinessPermit, 'min' => 1, 'required' => true, 'hint' => 'Your current mayor’s or business permit.'],
            ['type' => self::Bir2303, 'min' => 1, 'required' => true, 'hint' => 'Certificate of registration from the BIR.'],
            ['type' => self::ArticlesOfIncorporation, 'min' => 1, 'required' => true, 'hint' => 'Articles of incorporation or partnership.'],
            ['type' => self::GeneralInformationSheet, 'min' => 1, 'required' => true, 'hint' => 'Your latest General Information Sheet.'],
            ['type' => self::AuditedFinancials, 'min' => 1, 'required' => true, 'hint' => 'Audited financial statements for the latest 2 years. One combined file is fine.'],
            ['type' => self::CompanyProfile, 'min' => 1, 'required' => true, 'hint' => 'A short profile of your company.'],
            ['type' => self::OrganizationalChart, 'min' => 1, 'required' => true, 'hint' => 'Your organizational chart.'],
            ['type' => self::ProductCatalog, 'min' => 1, 'required' => true, 'hint' => 'Product catalog or service portfolio.'],
            ['type' => self::ClientReference, 'min' => 3, 'required' => true, 'hint' => 'Reference letters from at least 3 clients, one file each.'],
            ['type' => self::CodeOfEthics, 'min' => 1, 'required' => true, 'hint' => 'Signed Code of Ethics and Anti-Bribery Commitment.'],
            ['type' => self::PhilgepsRegistration, 'min' => 1, 'required' => false, 'hint' => 'Only if you are registered with PhilGEPS.'],
            ['type' => self::OtherLicense, 'min' => 1, 'required' => false, 'hint' => 'Any other licenses or certifications you hold.'],
            ['type' => self::HseCertification, 'min' => 1, 'required' => false, 'hint' => 'Certifications or policies that show health, safety and environment compliance.'],
        ];
    }

    /** @return array<int, self> */
    public static function vendorRequired(): array
    {
        return array_values(array_map(
            fn ($row) => $row['type'],
            array_filter(self::vendorChecklist(), fn ($row) => $row['required'])
        ));
    }

    /** Partner (officer) documents from the Procurement Partner Accreditation Form, section G. */
    public static function officerRequired(): array
    {
        return [self::Cv, self::GovernmentId, self::Portfolio, self::ReferenceLetter];
    }
}
