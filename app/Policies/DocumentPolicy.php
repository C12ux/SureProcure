<?php

namespace App\Policies;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\OfficerProfile;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * A document is only as visible as the record it is attached to, so each check
 * delegates to the parent record's own policy. That keeps one source of truth.
 */
class DocumentPolicy extends BasePolicy
{
    /** Download / view. Always serve files via Document::temporaryUrl() after this check. */
    public function view(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $parent = $document->documentable;

        return $parent !== null && Gate::forUser($user)->allows('view', $parent);
    }

    /** authorize('upload', [Document::class, $parentModel]) */
    public function upload(User $user, Model $parent): bool
    {
        return match (true) {
            $parent instanceof PurchaseRequisition => Gate::forUser($user)->allows('update', $parent)
                || $this->isAssignedOfficer($user, $parent),
            $parent instanceof Vendor,
            $parent instanceof OfficerProfile,
            $parent instanceof Quotation,
            $parent instanceof PurchaseOrder,
            $parent instanceof Payment => Gate::forUser($user)->allows('update', $parent),
            default => false,
        };
    }

    /** Mark a document verified/rejected. Partner documents are admin-only. */
    public function verify(User $user, Document $document): bool
    {
        return $user->isAdmin()
            || ($this->isApprovedOfficer($user) && $document->documentable instanceof Vendor);
    }

    /** Uploader may remove their own file until it has been reviewed. */
    public function delete(User $user, Document $document): bool
    {
        return $user->isAdmin()
            || ((int) $document->uploaded_by === (int) $user->id && $document->status === DocumentStatus::Pending);
    }
}
