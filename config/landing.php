<?php

/*
| Landing page content. Edit text here without touching any HTML.
| Do not put CSS class names in this file (Tailwind only scans views).
*/

return [

    // Sample data for the bid evaluation card in the hero.
    'bids' => [
        ['name' => 'Vendor A', 'technical' => 86, 'commercial' => 91, 'total' => 88.5, 'recommended' => true],
        ['name' => 'Vendor B', 'technical' => 78, 'commercial' => 84, 'total' => 81,   'recommended' => false],
        ['name' => 'Vendor C', 'technical' => 90, 'commercial' => 68, 'total' => 79,   'recommended' => false],
    ],

    'steps' => [
        ['title' => 'Lodge a purchase requisition', 'text' => 'Tell us what you need, how many, your budget and when you need it. Save a draft and submit when it is ready.'],
        ['title' => 'Confirm the details with your officer', 'text' => 'Your dedicated procurement officer reviews the request and asks questions until the requirements are clear.'],
        ['title' => 'We source from accredited vendors', 'text' => 'Only vendors that passed our document review are invited to send quotations.'],
        ['title' => 'Compare bids in one evaluation form', 'text' => 'Each quotation is scored on commercial and technical criteria, with a written recommendation.'],
        ['title' => 'Approve and issue the purchase order', 'text' => 'Review the form, ask for changes if you need them, then approve. Your officer records the purchase order.'],
    ],

    // accent: 'default' or 'success' (tick color). cta.style: 'light' (filled) or 'outline'. cta.route: a route name.
    'audiences' => [
        [
            'id' => 'for-customers',
            'title' => 'Customers',
            'intro' => 'Lodge requisitions and follow each one through to the purchase order.',
            'items' => [
                'Lodge a purchase requisition in minutes',
                'Track the status of every request',
                'See your procurement data in one place',
                'Download your data whenever you need it',
            ],
            'note' => 'Create an account first. An annual subscription is needed to submit requests.',
            'accent' => 'default',
            'cta' => ['route' => 'register', 'label' => 'Create a customer account', 'style' => 'light'],
        ],
        [
            'id' => 'vendors',
            'title' => 'Vendors',
            'intro' => 'Get accredited, then get invited to quote on customer requests.',
            'items' => [
                'Accreditation is by invitation',
                'Submit your company documents once',
                'Accredited vendors are included in sourcing',
                'Your dashboard shows your procurement agent’s contact details and your data',
            ],
            'note' => 'Vendors subscribe annually once their accreditation is approved.',
            'accent' => 'success',
            'cta' => ['route' => 'login', 'label' => 'Log in with your invitation', 'style' => 'outline'],
        ],
        [
            'id' => 'officers',
            'title' => 'Procurement officers',
            'intro' => 'Run corporate accounts as the dedicated officer behind every request.',
            'items' => [
                'Manage the requisitions assigned to you',
                'Source vendors and collect quotations',
                'Score bids on commercial and technical criteria',
                'Send your recommendation to the customer',
            ],
            'note' => 'Officers are accredited before they receive accounts.',
            'accent' => 'default',
            'cta' => ['route' => 'login', 'label' => 'Officer log in', 'style' => 'outline'],
        ],
    ],
];
