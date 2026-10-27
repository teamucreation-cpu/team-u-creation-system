<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = [
        'location',
        'phone',
        'email',
        'facebook_url',
        'business_days',
        'opening_time',
        'closing_time',
    ];
}
