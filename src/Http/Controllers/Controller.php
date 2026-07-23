<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Controllers;

use Eather009\LaravelSprintBoard\Contracts\SprintUser;
use Eather009\LaravelSprintBoard\Contracts\UserResolver;
use Eather009\LaravelSprintBoard\Exceptions\SprintAuthorizationException;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    protected function actor(UserResolver $resolver): SprintUser
    {
        return $resolver->current()
            ?? throw new SprintAuthorizationException('Unauthenticated.');
    }
}
