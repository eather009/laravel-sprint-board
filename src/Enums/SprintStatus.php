<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Enums;

enum SprintStatus: string
{
    case Planning = 'planning';
    case Running = 'running';
    case Completed = 'completed';
}
