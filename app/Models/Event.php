<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(
    'public_id',
    'project_id',
    'title',
    'message',
    'severity',
    'application',
    'context',
    'sensitive_context',
    'fingerprint',
    'occurred_at',
    'source_ip',
    'acknowledged_at',
)]
class Event extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'sensitive_context' => 'array',
            'occurred_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

