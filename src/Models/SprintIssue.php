<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Models;

use Eather009\LaravelSprintBoard\Database\Factories\SprintIssueFactory;
use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Support\SprintTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SprintIssue extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'added_at' => 'datetime',
        'completion_updated_at' => 'datetime',
        'completion_status' => IssueCompletionStatus::class,
        'priority_id' => 'integer',
    ];

    public function getTable(): string
    {
        return SprintTable::issues();
    }

    protected static function newFactory(): SprintIssueFactory
    {
        return SprintIssueFactory::new();
    }

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class, 'sprint_id');
    }
}
