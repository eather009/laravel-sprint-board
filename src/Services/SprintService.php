<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Services;

use Eather009\LaravelSprintBoard\Contracts\SprintAuthorizer;
use Eather009\LaravelSprintBoard\Contracts\SprintUser;
use Eather009\LaravelSprintBoard\Contracts\UserDirectory;
use Eather009\LaravelSprintBoard\Enums\SprintMemberRole;
use Eather009\LaravelSprintBoard\Enums\SprintStatus;
use Eather009\LaravelSprintBoard\Exceptions\SprintAuthorizationException;
use Eather009\LaravelSprintBoard\Exceptions\SprintValidationException;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Models\SprintMember;
use Illuminate\Support\Facades\DB;

class SprintService
{
    public function __construct(
        protected SprintAuthorizer $authorizer,
        protected UserDirectory $directory,
        protected SprintStatusResolver $statusResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array{user_id: int|string, role?: string, display_name?: string}>  $members
     */
    public function create(SprintUser $actor, array $attributes, array $members = []): Sprint
    {
        $attributes = $this->normalizeDates($attributes);

        return DB::transaction(function () use ($actor, $attributes, $members): Sprint {
            $sprint = new Sprint;
            $sprint->fill([
                'leader_id' => $attributes['leader_id'] ?? $actor->id(),
                'created_by' => $actor->id(),
                'name' => $attributes['name'] ?? null,
                'goal' => $attributes['goal'] ?? null,
                'description' => $attributes['description'] ?? null,
                'planning_note' => $attributes['planning_note'] ?? null,
                'retrospective' => $attributes['retrospective'] ?? null,
                'dashboard_settings' => $attributes['dashboard_settings'] ?? null,
                'start_date' => $attributes['start_date'],
                'end_date' => $attributes['end_date'],
                'status' => $attributes['status'] ?? SprintStatus::Planning,
            ]);

            if ($sprint->name === null || $sprint->name === '') {
                throw new SprintValidationException('Sprint name is required.');
            }

            $this->assertDateRange($sprint);
            $sprint->status = $this->statusResolver->resolve($sprint);
            $sprint->save();

            $memberRows = $this->prepareMemberRows($sprint, $actor, $members);
            $this->replaceMembers($sprint, $memberRows);

            return $sprint->fresh(['members', 'issues']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SprintUser $actor, Sprint $sprint, array $attributes): Sprint
    {
        $this->assertCanManage($actor, $sprint);

        $attributes = $this->normalizeDates($attributes);

        $sprint->fill(collect($attributes)->only([
            'name',
            'goal',
            'description',
            'planning_note',
            'retrospective',
            'dashboard_settings',
            'start_date',
            'end_date',
            'leader_id',
            'status',
        ])->all());

        if (isset($attributes['start_date']) || isset($attributes['end_date'])) {
            $this->assertDateRange($sprint);
            if (! isset($attributes['status'])) {
                $sprint->status = $this->statusResolver->resolve($sprint);
            }
        }

        $sprint->save();

        return $sprint->fresh(['members', 'issues']);
    }

    public function delete(SprintUser $actor, Sprint $sprint): void
    {
        $this->assertCanRemove($actor, $sprint);
        $sprint->delete();
    }

    /**
     * Replace sprint members. Leader is always retained.
     *
     * @param  array<int, array{user_id: int|string, role?: string, display_name?: string}>  $members
     */
    public function syncMembers(SprintUser $actor, Sprint $sprint, array $members): Sprint
    {
        $this->assertCanAssign($actor, $sprint);

        return DB::transaction(function () use ($actor, $sprint, $members): Sprint {
            $rows = $this->prepareMemberRows($sprint, $actor, $members);
            $this->replaceMembers($sprint, $rows);

            return $sprint->fresh(['members', 'issues']);
        });
    }

    /**
     * @param  array<int, array{user_id: int|string, role?: string, display_name?: string}>  $members
     * @return array<int, array{user_id: int|string, role: string, display_name: string}>
     */
    protected function prepareMemberRows(Sprint $sprint, SprintUser $actor, array $members): array
    {
        $byId = [];

        $leaderId = $sprint->leader_id ?: $actor->id();
        $leader = $this->directory->find($leaderId)
            ?? throw new SprintValidationException('Sprint leader was not found in the user directory.');

        $byId[(string) $leader->id()] = [
            'user_id' => $leader->id(),
            'role' => SprintMemberRole::Leader->value,
            'display_name' => $leader->displayName(),
        ];

        foreach ($members as $row) {
            if (! isset($row['user_id'])) {
                throw new SprintValidationException('Each member requires a user_id.');
            }

            $user = $this->directory->find($row['user_id']);
            if ($user === null) {
                throw new SprintValidationException("Member user [{$row['user_id']}] was not found.");
            }

            $role = $row['role'] ?? SprintMemberRole::Member->value;
            if ($role === SprintMemberRole::Leader->value) {
                $role = SprintMemberRole::Leader->value;
            } else {
                $role = SprintMemberRole::Member->value;
            }

            // Keep leader role sticky for the sprint leader id.
            if ((string) $user->id() === (string) $leaderId) {
                $role = SprintMemberRole::Leader->value;
            }

            $byId[(string) $user->id()] = [
                'user_id' => $user->id(),
                'role' => $role,
                'display_name' => $row['display_name'] ?? $user->displayName(),
            ];
        }

        return array_values($byId);
    }

    /**
     * @param  array<int, array{user_id: int|string, role: string, display_name: string}>  $rows
     */
    protected function replaceMembers(Sprint $sprint, array $rows): void
    {
        $sprint->members()->delete();

        foreach ($rows as $row) {
            SprintMember::query()->create([
                'sprint_id' => $sprint->id,
                'user_id' => $row['user_id'],
                'role' => $row['role'],
                'display_name' => $row['display_name'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function normalizeDates(array $attributes): array
    {
        if (! isset($attributes['start_date'], $attributes['end_date'])) {
            if (! isset($attributes['start_date']) && ! isset($attributes['end_date'])) {
                return $attributes;
            }

            throw new SprintValidationException('Both start_date and end_date are required when setting dates.');
        }

        return $attributes;
    }

    protected function assertDateRange(Sprint $sprint): void
    {
        if ($sprint->start_date === null || $sprint->end_date === null) {
            throw new SprintValidationException('Sprint start_date and end_date are required.');
        }

        if ($sprint->end_date->lt($sprint->start_date)) {
            throw new SprintValidationException('Sprint end_date must be on or after start_date.');
        }
    }

    protected function assertCanManage(SprintUser $actor, Sprint $sprint): void
    {
        if (! $this->authorizer->canManage($actor, $sprint)) {
            throw new SprintAuthorizationException('You are not allowed to manage this sprint.');
        }
    }

    protected function assertCanAssign(SprintUser $actor, Sprint $sprint): void
    {
        if (! $this->authorizer->canAssign($actor, $sprint)) {
            throw new SprintAuthorizationException('You are not allowed to assign sprint members.');
        }
    }

    protected function assertCanRemove(SprintUser $actor, Sprint $sprint): void
    {
        if (! $this->authorizer->canRemove($actor, $sprint)) {
            throw new SprintAuthorizationException('You are not allowed to delete this sprint.');
        }
    }
}
