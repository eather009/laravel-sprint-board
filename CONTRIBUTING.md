# Contributing

Thanks for contributing to `eather009/laravel-sprint-board`.

## Development

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`
4. Format code: `composer pint`
5. Static analysis (Laravel 11+ tooling; Larastan is not in `require-dev` so Laravel 10 CI stays installable):

```bash
composer require --dev larastan/larastan:^3.10 phpstan/phpstan:^2.0
composer phpstan
```


## Pull requests

- Target `main` (or the active feature branch for large work)
- Include tests for new behavior
- Keep changes focused; follow Laravel coding style (Pint)
- Do not commit secrets, credentials, or local planning files (`PLAN.md`, `plans/`)

## Reporting issues

Use GitHub Issues with steps to reproduce, Laravel/PHP versions, and expected vs actual behavior.
