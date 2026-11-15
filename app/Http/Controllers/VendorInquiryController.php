<?php

namespace App\Http\Controllers;

use App\Models\VendorInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VendorInquiryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $destination = route('home').'#vendor-inquiry';

        try {
            $data = $request->validate([
                'company_name' => ['required', 'string', 'max:255'],
                'contact_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'message' => ['required', 'string', 'max:2000'],
            ]);
        } catch (ValidationException $exception) {
            throw $exception->redirectTo($destination);
        }

        VendorInquiry::create($data);

        return redirect()->to($destination)
            ->with('inquiry_received', true);
    }
}
