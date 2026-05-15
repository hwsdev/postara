<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_id',
        'type',
        'data',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }
}
