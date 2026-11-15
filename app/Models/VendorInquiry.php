<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorInquiry extends Model
{
    use HasFactory;

    protected $fillable = ['company_name', 'contact_name', 'email', 'message'];
}
