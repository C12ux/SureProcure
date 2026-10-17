<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

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
            self::BusinessPermit => "Mayor's / Business Permit",
            self::Bir2303 => 'BIR Registration (Form 2303)',
            self::PhilgepsRegistration => 'PhilGEPS Registration',
            self::GeneralInformationSheet => 'General Information Sheet (GIS)',
            self::Cv => 'Curriculum Vitae',
            self::GovernmentId => 'Valid Government ID',
            default => \Illuminate\Support\Str::headline($this->value),
        };
    }

    /** Documents a vendor must upload before review (PhilGEPS is "if applicable"). */
    public static function vendorRequired(): array
    {
        return [
            self::SecDtiRegistration,
            self::BusinessPermit,
            self::Bir2303,
            self::ArticlesOfIncorporation,
            self::GeneralInformationSheet,
            self::AuditedFinancials,
        ];
    }

    /** Documents a procurement partner must upload (certifications are optional). */
    public static function officerRequired(): array
    {
        return [self::Cv, self::GovernmentId, self::Portfolio, self::ReferenceLetter];
    }
}
