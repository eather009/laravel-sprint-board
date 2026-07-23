<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Contracts;

/**
 * ACL for sprint operations. Concrete model types arrive in Phase 2+.
 */
interface SprintAuthorizer
{
    public function canView(SprintUser $user, object $sprint): bool;

    public function canManage(SprintUser $user, object $sprint): bool;

    public function canAssign(SprintUser $user, object $sprint): bool;

    public function canRemove(SprintUser $user, object $sprint): bool;

    public function canUpdateCompletion(SprintUser $user, object $sprint, object $issue): bool;
}
