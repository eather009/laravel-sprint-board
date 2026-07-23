<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Enums;

enum IssueCompletionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
}
