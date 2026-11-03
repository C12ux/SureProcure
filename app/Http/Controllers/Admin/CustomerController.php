<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrganizationType;
use App\Enums\PrStatus;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __invoke(): View
    {
        $customers = Organization::query()
            ->where('type', OrganizationType::Client->value)
            ->withCount([
                'users',
                'purchaseRequisitions as submitted_count' => fn ($q) => $q->where('status', '!=', PrStatus::Draft->value),
            ])
            ->withExists([
                'subscriptions as subscribed' => fn ($q) => $q->where('status', 'active')->whereDate('end_date', '>=', today()),
            ])
            ->with('users')
            ->latest()
            ->paginate(15);

        return view('admin.customers.index', compact('customers'));
    }
}
