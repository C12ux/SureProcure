<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationType;
use App\Enums\OrgRole;
use App\Enums\UserRole;
use App\Models\Invitation;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Public page behind an invitation link. Creates the account (email already
 * confirmed, because only the invited inbox received the link) and, for vendors,
 * their company and vendor record. Accreditation details come afterwards.
 */
class InvitationAcceptController extends Controller
{
    public function show(Request $request, string $token): View|Response
    {
        if ($request->user()) {
            return $this->invalid("You are signed in as {$request->user()->name}. Open the invitation link in a private window, or log out first.", 403);
        }

        $invitation = Invitation::findByToken($token);

        if (! $invitation) {
            return $this->invalid('This link is not valid. Check that you copied all of it, or ask SureProcure for a new one.', 404);
        }

        if (! $invitation->isPending()) {
            return $this->invalid(match ($invitation->status()) {
                'accepted' => 'This invitation has already been used. Try logging in instead.',
                'revoked' => 'This invitation was cancelled. Ask SureProcure for a new one.',
                default => 'This invitation has expired. Ask SureProcure for a new link.',
            }, 410);
        }

        return view('invitations.accept', ['invitation' => $invitation, 'token' => $token]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        abort_if($request->user(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($token, $data) {
            // Lock the row so a double click cannot create two accounts.
            $invitation = Invitation::where('token_hash', hash('sha256', $token))->lockForUpdate()->first();

            if (! $invitation || ! $invitation->isPending()) {
                throw ValidationException::withMessages(['password' => 'This invitation is no longer valid. Ask SureProcure for a new link.']);
            }

            if (User::withTrashed()->where('email', $invitation->email)->exists()) {
                throw ValidationException::withMessages(['password' => 'An account already exists for this email. Try logging in.']);
            }

            $user = User::forceCreate([
                'name' => $data['name'],
                'email' => $invitation->email,
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'], // hashed by the User model cast
                'role' => $invitation->role,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            if ($invitation->role === UserRole::Vendor) {
                $organization = Organization::create([
                    'type' => OrganizationType::Vendor,
                    'legal_name' => $invitation->company_name,
                    'email' => $invitation->email,
                ]);
                $organization->users()->attach($user->id, ['org_role' => OrgRole::Owner->value]);

                Vendor::create(['organization_id' => $organization->id]);
            }

            $invitation->forceFill(['accepted_at' => now(), 'user_id' => $user->id])->save();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    private function invalid(string $message, int $status): Response
    {
        return response()->view('invitations.invalid', ['message' => $message], $status);
    }
}
