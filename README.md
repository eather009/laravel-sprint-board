# laravel-sprint-board

Standalone **Laravel Sprint Board API** package — JSON API for sprints, members, and linked issues. Install in any Laravel 10–12 app to run a sprint board without shipping UI.

- **Owner:** [Iftekhar Ahmed Eather](https://github.com/eather009) (`eather009`)
- **GitHub:** https://github.com/eather009/laravel-sprint-board  
- **Composer:** `eather009/laravel-sprint-board`  
- **Default tracker:** [Backlog](https://backlog.com/) (swappable via `IssueTracker` contract)  
- **Users:** host Laravel `User` model  
- **UI:** not included (host / SPA)
- **License:** MIT

## Requirements

- PHP 8.2+
- Laravel 10.x / 11.x / 12.x
- Auth middleware of your choice (default config expects [Laravel Sanctum](https://laravel.com/docs/sanctum))

## Install

```bash
composer require eather009/laravel-sprint-board
php artisan vendor:publish --tag=sprint-config
php artisan migrate
```

Optional publishes:

```bash
php artisan vendor:publish --tag=sprint-lang
php artisan vendor:publish --tag=sprint-migrations
```

Bind Backlog credentials in the host (space URL + API key) when using the default tracker, then call `/api/sprints` (CRUD API arrives in a later release).

### Local path repository

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../laravel-sprint-board"
    }
  ],
  "require": {
    "eather009/laravel-sprint-board": "*"
  }
}
```

## Configuration

Publish `config/sprint.php` to set:

- `user_model`, `table_prefix`
- `route_prefix` (default `api/sprints`)
- `middleware` (default `['api', 'auth:sanctum']`)
- `tracker_default` (default `backlog`)
- Backlog closed statuses, priorities, and cache TTLs

## Extension contracts

| Contract | Purpose |
|----------|---------|
| `UserResolver` | Current authenticated sprint user |
| `UserDirectory` | Search users to add as members |
| `IssueTracker` | Hydrate / closed detection / priority (default: `BacklogIssueTracker`) |
| `BacklogCredentials` | Host-supplied Backlog space + API key |
| `SprintAuthorizer` | View / manage / completion ACL |

Bind replacements in your app service provider, e.g. `$this->app->bind(IssueTracker::class, MyTracker::class)`.

## Status

| Item | State |
|------|--------|
| Owner | Iftekhar Ahmed Eather (`eather009`) |
| Default tracker | Backlog |
| Package skeleton | Phase 1 ✅ |
| Schema / models | Phase 2 ✅ |
| Domain services | Phase 3 ✅ |
| HTTP API | Upcoming Phase 4 |
| License | MIT |

## Development

```bash
composer install
composer test
composer pint
```

See [CONTRIBUTING.md](CONTRIBUTING.md) and [CHANGELOG.md](CHANGELOG.md).
