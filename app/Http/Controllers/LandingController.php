<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        $showDemo = app()->environment('local', 'testing');

        try {
            $vendors = Organization::query()
                ->whereHas('vendor', fn (Builder $vendors) => $vendors->accredited())
                ->when(! $showDemo, fn (Builder $organizations) => $organizations->where('legal_name', 'not like', 'DEMO | %'))
                ->orderByDesc('id')
                ->limit(4)
                ->get(['legal_name', 'trade_name']);
        } catch (QueryException $exception) {
            report($exception);

            $vendors = null;
        }

        return view('welcome', compact('vendors'));
    }
}
