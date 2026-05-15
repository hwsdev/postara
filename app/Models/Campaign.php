<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'template_id',
        'contact_list_id',
        'segment_filter',
        'from_name',
        'from_email',
        'subject',
        'preview_text',
        'status',
        'scheduled_at',
        'sent_at',
    ];

    protected $casts = [
        'segment_filter' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function contactList(): BelongsTo
    {
        return $this->belongsTo(ContactList::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function getStats(): array
    {
        $emails = $this->emails();

        return [
            'sent' => $emails->count(),
            'delivered' => $emails->where('status', 'delivered')->count(),
            'bounced' => $emails->where('status', 'bounced')->count(),
            'opened' => $emails->whereHas('events', fn ($q) => $q->where('type', 'opened'))->count(),
            'clicked' => $emails->whereHas('events', fn ($q) => $q->where('type', 'clicked'))->count(),
        ];
    }
}
