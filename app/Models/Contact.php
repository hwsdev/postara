<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'email',
        'name',
        'custom_fields',
        'tags',
        'subscribed',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'tags' => 'array',
        'subscribed' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(ContactList::class, 'contact_list_contact')
            ->withTimestamps();
    }
}
