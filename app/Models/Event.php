<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'project_id',
        'event_type',
        'title',
        'message',
        'severity',
        'application',
        'environment',
        'context',
        'sensitive_context',
        'fingerprint',
        'occurred_at',
        'source_ip',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'sensitive_context' => 'array',
            'occurred_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    /**
     * Use the public UUID in API routes instead of the numeric ID.
     */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

