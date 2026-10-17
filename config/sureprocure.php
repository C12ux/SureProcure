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
    | When true, a client organization needs an active subscription to lodge a PR.
    | Keep false until plans have real prices and you can issue subscriptions.
    */
    'require_subscription' => env('REQUIRE_SUBSCRIPTION', false),

];
