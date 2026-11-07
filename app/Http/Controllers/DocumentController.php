<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/**
 * Files live in a private bucket. Nobody gets a permanent link: access is checked
 * by the document policy, then the browser is sent to a link that expires in minutes.
 */
class DocumentController extends Controller
{
    public function show(Document $document): RedirectResponse
    {
        Gate::authorize('view', $document);

        return redirect()->away($document->temporaryUrl(5));
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        Gate::authorize('delete', $document);

        // A vendor cannot change files once the application is locked for review.
        $parent = $document->documentable;
        if ($parent instanceof Vendor && ! $request->user()->isAdmin()) {
            Gate::authorize('update', $parent);
        }

        try {
            Storage::disk(Document::DISK)->delete($document->storage_path);
        } catch (\Throwable $e) {
            report($e);
        }

        $document->delete(); // soft delete keeps the record

        return back()->with('success', 'File removed.');
    }
}
