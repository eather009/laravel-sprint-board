<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Models;

use Eather009\LaravelSprintBoard\Database\Factories\SprintFactory;
use Eather009\LaravelSprintBoard\Enums\SprintStatus;
use Eather009\LaravelSprintBoard\Support\SprintTable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|string $leader_id
 * @property int|string $created_by
 * @property string $name
 * @property string|null $goal
 * @property string|null $description
 * @property string|null $planning_note
 * @property array<string, mixed>|null $retrospective
 * @property array<string, mixed>|null $dashboard_settings
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property SprintStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, SprintMember> $members
 * @property-read Collection<int, SprintIssue> $issues
 */
class Sprint extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'retrospective' => 'array',
        'dashboard_settings' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => SprintStatus::class,
    ];

    public function getTable(): string
    {
        return SprintTable::sprints();
    }

    protected static function newFactory(): SprintFactory
    {
        return SprintFactory::new();
    }

    public function members(): HasMany
    {
        return $this->hasMany(SprintMember::class, 'sprint_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(SprintIssue::class, 'sprint_id');
    }

    public function userModelClass(): string
    {
        return (string) config('sprint.user_model', 'App\\Models\\User');
    }
}
