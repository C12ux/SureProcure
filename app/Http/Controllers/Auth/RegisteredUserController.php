<?php

namespace App\Http\Controllers\Auth;

use App\Enums\OrganizationType;
use App\Enums\OrgRole;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Customer self-registration: creates the user, their company, and an owner membership.
     * The user's platform role defaults to "client" in the database.
     * (Vendors and procurement officers are invited; they do not use this form.)
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password, // hashed by the User model cast
            ]);

            $organization = Organization::create([
                'type' => OrganizationType::Client,
                'legal_name' => $request->company_name,
                'email' => $request->email,
            ]);

            $organization->users()->attach($user->id, ['org_role' => OrgRole::Owner->value]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
