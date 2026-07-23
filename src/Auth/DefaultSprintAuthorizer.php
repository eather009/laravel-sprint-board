<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Auth;

use Eather009\LaravelSprintBoard\Contracts\SprintAuthorizer;
use Eather009\LaravelSprintBoard\Contracts\SprintUser;
use Eather009\LaravelSprintBoard\Enums\SprintMemberRole;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Models\SprintIssue;
use Eather009\LaravelSprintBoard\Models\SprintMember;

class DefaultSprintAuthorizer implements SprintAuthorizer
{
    public function canView(SprintUser $user, object $sprint): bool
    {
        if ($user->isSprintAdmin()) {
            return true;
        }

        return $this->isLeader($user, $sprint) || $this->isMember($user, $sprint);
    }

    public function canManage(SprintUser $user, object $sprint): bool
    {
        return $user->isSprintAdmin() || $this->isLeader($user, $sprint);
    }

    public function canAssign(SprintUser $user, object $sprint): bool
    {
        return $this->canManage($user, $sprint);
    }

    public function canRemove(SprintUser $user, object $sprint): bool
    {
        return $this->canManage($user, $sprint);
    }

    public function canUpdateCompletion(SprintUser $user, object $sprint, object $issue): bool
    {
        if ($this->canManage($user, $sprint)) {
            return true;
        }

        if (! $issue instanceof SprintIssue) {
            return false;
        }

        return (string) $issue->added_by === (string) $user->id();
    }

    protected function isLeader(SprintUser $user, object $sprint): bool
    {
        if (! $sprint instanceof Sprint) {
            return false;
        }

        if ((string) $sprint->leader_id === (string) $user->id()) {
            return true;
        }

        return $sprint->members()
            ->where('user_id', $user->id())
            ->where('role', SprintMemberRole::Leader->value)
            ->exists();
    }

    protected function isMember(SprintUser $user, object $sprint): bool
    {
        if (! $sprint instanceof Sprint) {
            return false;
        }

        return $sprint->members()
            ->where('user_id', $user->id())
            ->exists();
    }

    public function memberRecord(Sprint $sprint, SprintUser $user): ?SprintMember
    {
        /** @var SprintMember|null $member */
        $member = SprintMember::query()
            ->where('sprint_id', $sprint->getKey())
            ->where('user_id', $user->id())
            ->first();

        return $member;
    }
}
