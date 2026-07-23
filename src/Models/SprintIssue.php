<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Models;

use Eather009\LaravelSprintBoard\Database\Factories\SprintIssueFactory;
use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Support\SprintTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $sprint_id
 * @property string $tracker
 * @property string $external_project_id
 * @property string $external_issue_id
 * @property int|string $added_by
 * @property Carbon|null $added_at
 * @property int|null $priority_id
 * @property IssueCompletionStatus $completion_status
 * @property string|null $completion_note
 * @property int|string|null $completion_updated_by
 * @property Carbon|null $completion_updated_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Sprint|null $sprint
 */
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
