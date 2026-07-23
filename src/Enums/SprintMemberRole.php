<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Enums;

enum SprintMemberRole: string
{
    case Leader = 'leader';
    case Member = 'member';
}
