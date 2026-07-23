<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Models;

use Eather009\LaravelSprintBoard\Database\Factories\SprintFactory;
use Eather009\LaravelSprintBoard\Enums\SprintStatus;
use Eather009\LaravelSprintBoard\Support\SprintTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
