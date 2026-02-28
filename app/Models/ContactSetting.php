<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    protected $fillable = [
        'location_line_1',
        'location_line_2',
        'hours_line_1',
        'hours_line_2',
        'phone',
        'email',
    ];
}
