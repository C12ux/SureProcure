<?php

namespace App\Support;

/** Fixed choices from the Supplier Accreditation Form. */
class VendorOptions
{
    public const ORG_TYPES = [
        'sole_proprietorship' => 'Sole proprietorship',
        'partnership' => 'Partnership',
        'corporation' => 'Corporation',
        'cooperative' => 'Cooperative',
        'one_person_corporation' => 'One person corporation',
        'other' => 'Others',
    ];

    public const INDUSTRIES = ['Manufacturing', 'FMCG', 'Energy / Renewable', 'Construction', 'Services', 'Government'];

    public const ISO = ['ISO 9001', 'ISO 14001', 'ISO 45001'];
}
