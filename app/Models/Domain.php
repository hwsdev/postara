<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'domain',
        'dkim_public_key',
        'dkim_private_key',
        'dkim_selector',
        'status',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    protected $hidden = [
        'dkim_private_key',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function getSpfRecord(): string
    {
        return 'v=spf1 include:_spf.postara.dev ~all';
    }

    public function getDkimRecord(): string
    {
        $key = str_replace(["\n", '-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----'], '', $this->dkim_public_key ?? '');

        return "v=DKIM1; k=rsa; p={$key}";
    }

    public function getDmarcRecord(): string
    {
        return "v=DMARC1; p=quarantine; rua=mailto:dmarc@{$this->domain}";
    }
}
