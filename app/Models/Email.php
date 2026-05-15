<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'message_id',
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'html',
        'text',
        'status',
        'template_id',
        'campaign_id',
        'tags',
        'headers',
        'idempotency_key',
    ];

    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'tags' => 'array',
        'headers' => 'array',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(EmailEvent::class);
    }

    public function getOpenCount(): int
    {
        return $this->events()->where('type', 'opened')->count();
    }

    public function getClickCount(): int
    {
        return $this->events()->where('type', 'clicked')->count();
    }
}
