<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Vendors and procurement officers join by invitation. No email is sent yet:
 * the admin copies the link and shares it. The link is shown once, because
 * only a hash of it is stored; "New link" makes a replacement.
 */
class InvitationController extends Controller
{
    private const DAYS = 14;

    public function index(): View
    {
        return view('admin.invitations.index', [
            'invitations' => Invitation::with(['inviter', 'user'])->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.invitations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'role' => ['required', Rule::in([UserRole::Vendor->value, UserRole::ProcurementOfficer->value])],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', Rule::unique('users', 'email')],
            'company_name' => ['required_if:role,'.UserRole::Vendor->value, 'nullable', 'string', 'max:255'],
        ], [
            'email.unique' => 'An account with this email already exists.',
            'company_name.required_if' => 'Enter the vendor’s company name.',
        ]);

        $role = UserRole::from($data['role']);

        $alreadyPending = Invitation::pending()
            ->whereRaw('lower(email) = ?', [$data['email']])
            ->where('role', $role->value)
            ->exists();

        if ($alreadyPending) {
            throw ValidationException::withMessages([
                'email' => 'There is already a pending invitation for this email. Use "New link" on the Invitations page.',
            ]);
        }

        [$invitation, $token] = Invitation::issue(
            $data['email'],
            $role,
            $role === UserRole::Vendor ? $data['company_name'] : null,
            $request->user(),
            self::DAYS,
        );

        return $this->showLink($invitation, $token, 'Invitation created.');
    }

    public function regenerate(Invitation $invitation): RedirectResponse
    {
        if (! $invitation->isPending()) {
            return back()->with('warning', 'Only pending invitations can get a new link.');
        }

        return $this->showLink($invitation, $invitation->regenerate(self::DAYS), 'New link created. The old one no longer works.');
    }

    public function revoke(Invitation $invitation): RedirectResponse
    {
        if (! $invitation->isPending()) {
            return back()->with('warning', 'Only pending invitations can be revoked.');
        }

        $invitation->forceFill(['revoked_at' => now()])->save();

        return back()->with('success', 'Invitation revoked.');
    }

    /** Flash the link for the next page only; it cannot be shown again. */
    private function showLink(Invitation $invitation, string $token, string $message): RedirectResponse
    {
        return redirect()->route('admin.invitations.index')
            ->with('success', $message)
            ->with('invite_link', $invitation->acceptUrl($token))
            ->with('invite_for', $invitation->email)
            ->with('invite_expires', $invitation->expires_at->format('F j, Y'));
    }
}
