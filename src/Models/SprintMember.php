<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Models;

use Eather009\LaravelSprintBoard\Database\Factories\SprintMemberFactory;
use Eather009\LaravelSprintBoard\Enums\SprintMemberRole;
use Eather009\LaravelSprintBoard\Support\SprintTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SprintMember extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'role' => SprintMemberRole::class,
    ];

    public function getTable(): string
    {
        return SprintTable::members();
    }

    protected static function newFactory(): SprintMemberFactory
    {
        return SprintMemberFactory::new();
    }

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class, 'sprint_id');
    }
}
