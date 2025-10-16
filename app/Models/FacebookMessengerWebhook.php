<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookMessengerWebhook extends Model
{
    protected $fillable = [
        'user_app_id',
        'user_id',
        'psid',
    ];
}
