# laravel-sprint-board

Standalone **Laravel Sprint Board API** package — JSON API for sprints, members, and linked issues. Install in any Laravel 10–12 app to run a sprint board without shipping UI.

- **Owner:** [Iftekhar Ahmed Eather](https://github.com/eather009) (`eather009`)
- **GitHub:** https://github.com/eather009/laravel-sprint-board  
- **Packagist:** https://packagist.org/packages/eather009/laravel-sprint-board  
- **Composer:** `eather009/laravel-sprint-board`  
- **Default tracker:** [Backlog](https://backlog.com/) (swappable via `IssueTracker` contract; use `null` for offline)  
- **Users:** host Laravel `User` model  
- **UI:** not included (host / SPA)
- **License:** MIT

[![Latest Version on Packagist](https://img.shields.io/packagist/v/eather009/laravel-sprint-board.svg?style=flat-square)](https://packagist.org/packages/eather009/laravel-sprint-board)
[![Total Downloads](https://img.shields.io/packagist/dt/eather009/laravel-sprint-board.svg?style=flat-square)](https://packagist.org/packages/eather009/laravel-sprint-board)
[![tests](https://github.com/eather009/laravel-sprint-board/actions/workflows/tests.yml/badge.svg)](https://github.com/eather009/laravel-sprint-board/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](LICENSE.md)

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

Bind Backlog credentials via env or a custom `BacklogCredentials` binding:

```env
BACKLOG_SPACE_URL=https://your-space.backlog.com
BACKLOG_API_KEY=your-api-key
```

Or in a service provider (see [`examples/basic-usage.php`](examples/basic-usage.php)).

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
- `tracker_default` (`backlog` or `null`)
- Backlog closed statuses, priorities, and cache TTLs
- `dashboard_widgets`

## Extension contracts

| Contract | Purpose |
|----------|---------|
| `UserResolver` | Current authenticated sprint user |
| `UserDirectory` | Search / find users to add as members |
| `IssueTracker` | Hydrate / closed detection / priority (default: `BacklogIssueTracker`) |
| `BacklogCredentials` | Host-supplied Backlog space + API key |
| `SprintAuthorizer` | View / manage / completion ACL |

```php
use Eather009\LaravelSprintBoard\Contracts\IssueTracker;
use App\Sprint\MyTracker;

$this->app->bind(IssueTracker::class, MyTracker::class);
```

See [`examples/basic-usage.php`](examples/basic-usage.php) and the OpenAPI 3 specification [`docs/openapi.yaml`](docs/openapi.yaml) (preview in [Swagger Editor](https://editor.swagger.io/) or Redoc).

## API (v1)

Prefix: `/api/sprints` (configurable). Auth required.

| Method | Path | Purpose |
|--------|------|---------|
| GET/POST | `/` | List / create sprints |
| GET/PUT/PATCH/DELETE | `/{sprint}` | Show / update / delete |
| GET/PUT | `/{sprint}/members` | List / sync members |
| GET/POST | `/{sprint}/issues` | List / link issues |
| DELETE | `/{sprint}/issues/{issue}` | Unlink |
| PUT | `/{sprint}/issues/{issue}/completion` | Set completion |
| POST | `/{sprint}/issues/refresh` | Hydrate + closed sync |
| POST | `/{sprint}/issues/priority-sync` | Remote priority push (501 if unsupported) |
| GET | `/{sprint}/dashboard` | Aggregates JSON |
| GET/PUT | `/{sprint}/retrospective` | Retrospective JSON |
| GET | `/{sprint}/export/issues.csv` | CSV export |
| GET | `/{sprint}/export/summary.txt` | Text summary |

Example:

```bash
curl -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  "$APP_URL/api/sprints"
```

## Status

| Item | State |
|------|--------|
| Owner | Iftekhar Ahmed Eather (`eather009`) |
| Default tracker | Backlog |
| Package skeleton | Phase 1 ✅ |
| Schema / models | Phase 2 ✅ |
| Domain services | Phase 3 ✅ |
| HTTP API | Phase 4 ✅ |
| Hydrate / dashboard | Phase 5 ✅ |
| Docs / release prep | Phase 6 ✅ |
| Backlog HTTP driver | v0.1.1 ✅ |
| License | MIT |

## Packagist

Published at [packagist.org/packages/eather009/laravel-sprint-board](https://packagist.org/packages/eather009/laravel-sprint-board).

Prefer a version constraint, e.g. `composer require eather009/laravel-sprint-board:^0.1.2`.

Ensure the Packagist ↔ GitHub webhook/sync is enabled so new tags auto-update. For local path or VCS installs, see the development notes below / [CONTRIBUTING.md](CONTRIBUTING.md).

## Development

```bash
composer install
composer test
composer pint
# PHPStan (Larastan requires Laravel 11+ tooling — install separately)
composer require --dev larastan/larastan:^3.10 phpstan/phpstan:^2.0
composer phpstan
```

See [CONTRIBUTING.md](CONTRIBUTING.md), [CHANGELOG.md](CHANGELOG.md), and [`docs/openapi.yaml`](docs/openapi.yaml).
