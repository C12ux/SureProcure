<?php

/*
| Landing page content. Edit text here without touching any HTML.
| Do not put CSS class names in this file (Tailwind only scans views).
*/

return [

    'contact_email' => env('SUREPROCURE_CONTACT_EMAIL'),

    'steps' => [
        ['title' => 'Lodge a purchase requisition', 'text' => 'Tell us what you need, how many, your budget and when you need it. Save a draft and submit when it is ready.'],
        ['title' => 'Confirm the details with your officer', 'text' => 'Your dedicated procurement officer reviews the request and asks questions until the requirements are clear.'],
        ['title' => 'We source from accredited vendors', 'text' => 'Only vendors that passed our document review are invited to send quotations.'],
        ['title' => 'Compare bids in one evaluation form', 'text' => 'Each quotation is scored on commercial and technical criteria, with a written recommendation.'],
        ['title' => 'Approve and issue the purchase order', 'text' => 'Review the form, ask for changes if you need them, then approve. Your officer records the purchase order.'],
    ],

    'audiences' => [
        [
            'id' => 'for-customers',
            'title' => 'Customers',
            'description' => 'Tell your procurement officer what you need, then track the request from submission to purchase order. When the bids are ready, compare them in one evaluation and decide what to approve.',
        ],
        [
            'id' => 'vendors',
            'title' => 'Vendors',
            'description' => 'If invited to apply for accreditation, submit your company documents for review. Once accredited, you can receive invitations to quote on requests that match what you supply.',
        ],
        [
            'id' => 'officers',
            'title' => 'Procurement officers',
            'description' => 'Manage your assigned requisitions, source accredited suppliers, and assess quotations on technical and commercial criteria. Send the customer a recommendation and record the approved purchase order.',
        ],
    ],
];
