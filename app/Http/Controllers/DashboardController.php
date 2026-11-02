<?php

namespace App\Http\Controllers;

use App\Enums\PrStatus;
use App\Enums\UserRole;
use App\Models\PurchaseRequisition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return match ($user->role) {
            UserRole::Client => $this->customer($user),
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
}
