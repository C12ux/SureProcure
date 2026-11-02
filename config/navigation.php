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
    ],

    'procurement_officer' => [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'match' => 'dashboard'],
    ],

    'admin' => [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'match' => 'dashboard'],
    ],
];
