<?php

/*
| Sidebar links per role. Items whose route does not exist yet are hidden automatically,
| so add a role's pages here as they are built.
| match: the route name pattern that highlights the link.
*/

return [

    'client' => [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'match' => 'dashboard'],
        ['label' => 'Requisitions', 'route' => 'requisitions.index', 'icon' => 'document', 'match' => 'requisitions.*'],
    ],

    'vendor' => [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'match' => 'dashboard'],
        ['label' => 'Accreditation', 'route' => 'vendor.accreditation.show', 'icon' => 'shield', 'match' => 'vendor.accreditation.*'],
    ],

    'procurement_officer' => [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'match' => 'dashboard'],
        ['label' => 'Requests', 'route' => 'officer.requisitions.index', 'icon' => 'document', 'match' => 'officer.*'],
    ],

    'admin' => [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'match' => 'dashboard'],
        ['label' => 'Requests', 'route' => 'admin.requisitions.index', 'icon' => 'document', 'match' => 'admin.requisitions.*'],
        ['label' => 'Vendors', 'route' => 'admin.vendors.index', 'icon' => 'shield', 'match' => 'admin.vendors.*'],
        ['label' => 'Customers', 'route' => 'admin.customers.index', 'icon' => 'building', 'match' => 'admin.customers.*'],
        ['label' => 'Invitations', 'route' => 'admin.invitations.index', 'icon' => 'mail', 'match' => 'admin.invitations.*'],
    ],
];
