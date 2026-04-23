<?php

namespace App\Models;

use App\Enums\ContactRequestStatus;
use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'message',
        'consent_accepted', 'consent_text_hash',
        'utm', 'referer_url', 'landing_url',
        'ip_hash', 'user_agent',
        'status', 'handled_at',
    ];

    protected $casts = [
        'utm' => 'array',
        'consent_accepted' => 'boolean',
        'status' => ContactRequestStatus::class,
        'handled_at' => 'datetime',
    ];
}
