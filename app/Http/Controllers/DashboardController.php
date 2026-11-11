<?php

namespace App\Http\Controllers;

use App\Enums\AccreditationStatus;
use App\Enums\OrganizationType;
use App\Enums\PrStatus;
use App\Enums\UserRole;
use App\Models\Invitation;
use App\Models\Organization;
use App\Models\PrStatusHistory;
use App\Models\PurchaseRequisition;
use App\Models\User;
use App\Models\Vendor;
use App\Support\VendorAccreditation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return match ($user->role) {
            UserRole::Client => $this->customer($user),
            UserRole::Admin => $this->admin($user),
            UserRole::Vendor => $this->vendor($user),
            UserRole::ProcurementOfficer => $this->officer($user),
            default => view('dashboard.placeholder', ['role' => $user->role]),
        };
    }

    private function customer(User $user): View
    {
        $organization = $user->organizations->first();

        $counts = PurchaseRequisition::visibleTo($user)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->toBase() // plain query: keeps the status keys as strings
            ->pluck('total', 'status');

        $total = fn (array $statuses) => collect($statuses)
            ->sum(fn (PrStatus $s) => (int) ($counts[$s->value] ?? 0));

        $groups = PrStatus::groups();

        $stats = [
            'drafts' => $total($groups['drafts']),
            'in_progress' => $total($groups['in_progress']),
            'attention' => $total($groups['attention']),
            'completed' => $total($groups['completed']),
        ];

        $attention = PurchaseRequisition::visibleTo($user)
            ->withStatus(...$groups['attention'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $recent = PurchaseRequisition::visibleTo($user)
            ->with('category')
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.customer', [
            'user' => $user,
            'organization' => $organization,
            'subscribed' => $organization?->hasActiveSubscription() ?? false,
            'stats' => $stats,
            'attention' => $attention,
            'recent' => $recent,
        ]);
    }

    private function admin(User $user): View
    {
        $active = PrStatus::active();

        $stats = [
            'unassigned' => PurchaseRequisition::whereNull('assigned_officer_id')->withStatus(...$active)->count(),
            'active' => PurchaseRequisition::withStatus(...$active)->count(),
            'vendors_review' => Vendor::where('accreditation_status', AccreditationStatus::UnderReview->value)->count(),
            'customers' => Organization::where('type', OrganizationType::Client->value)->count(),
            'officers' => User::where('role', UserRole::ProcurementOfficer->value)->where('is_active', true)->count(),
            'invites' => Invitation::pending()->count(),
        ];

        $needsOfficer = PurchaseRequisition::with('organization')
            ->whereNull('assigned_officer_id')
            ->withStatus(...$active)
            ->oldest('submitted_at')
            ->limit(6)
            ->get();

        // Customers' private drafts are left out of the staff activity feed.
        $activity = PrStatusHistory::with(['purchaseRequisition.organization', 'changedBy'])
            ->whereHas('purchaseRequisition')
            ->where('to_status', '!=', PrStatus::Draft->value)
            ->latest('id')
            ->limit(8)
            ->get();

        return view('dashboard.admin', compact('user', 'stats', 'needsOfficer', 'activity'));
    }

    private function officer(User $user): View
    {
        $mine = fn () => PurchaseRequisition::visibleTo($user)->where('status', '!=', PrStatus::Draft->value);

        $stats = [
            'action' => $mine()->withStatus(...PrStatus::officerAction())->count(),
            'waiting' => $mine()->withStatus(...PrStatus::waitingOnCustomer())->count(),
            'completed' => $mine()->withStatus(PrStatus::Closed)->count(),
        ];

        $actionList = $mine()->with('organization')->withStatus(...PrStatus::officerAction())->latest('updated_at')->limit(6)->get();
        $waitingList = $mine()->with('organization')->withStatus(...PrStatus::waitingOnCustomer())->latest('updated_at')->limit(5)->get();

        return view('dashboard.officer', compact('user', 'stats', 'actionList', 'waitingList'));
    }

    private function vendor(User $user): View
    {
        $vendor = $user->organizations->first()?->vendor;

        return view('dashboard.vendor', [
            'user' => $user,
            'vendor' => $vendor,
            'accreditation' => $vendor ? new VendorAccreditation($vendor) : null,
        ]);
    }
}
