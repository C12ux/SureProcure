<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorInquiry;
use Illuminate\View\View;

class VendorInquiryController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.vendor-inquiries.index', [
            'inquiries' => VendorInquiry::latest('id')->paginate(15),
        ]);
    }
}
