<?php

return [

    /*
    | First admin account, used only by AdminUserSeeder.
    | Values come from .env so no secret is committed to git.
    */
    'admin' => [
        'name' => env('ADMIN_NAME', 'Admin'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    /*
    | When true, a client organization needs an active subscription to submit a PR.
    | Keep false until plans have real prices and you can issue subscriptions.
    */
    'require_subscription' => env('REQUIRE_SUBSCRIPTION', false),

    /*
    | When may a vendor see a customer's contact details? (Slide 6: "Contact details of our customers".)
    |   awarded  only on requests where the vendor won the award (default, the safest)
    |   invited  on every request the vendor was invited to quote on
    |   never    vendors never see customer contacts
    | Showing contacts earlier lets a vendor deal with the customer directly and bypass SureProcure.
    */
    'vendor_customer_contacts' => env('VENDOR_CUSTOMER_CONTACTS', 'awarded'),

];
