# laravel-sprint-board

Standalone **Laravel Sprint Board API** package — JSON API for sprints, members, and linked issues.

- **GitHub:** https://github.com/eather009/laravel-sprint-board  
- **Composer (planned):** `eather009/laravel-sprint-board`  
- **Default tracker:** [Backlog](https://backlog.com/) (swappable via `IssueTracker` contract)  
- **Users:** host Laravel `User` model  
- **UI:** not included (host / SPA)

> Implementation not started. See [`PLAN.md`](./PLAN.md).

## Intended install (after first release)

```bash
composer require eather009/laravel-sprint-board
php artisan vendor:publish --tag=sprint-config
php artisan migrate
```

Bind Backlog credentials in the host (space URL + API key), then call `/api/sprints`.

## Status

| Item | State |
|------|--------|
| Plan | Locked in `PLAN.md` |
| Default tracker | Backlog |
| Package skeleton | Pending Phase 1 |
| License | MIT |

## Local mirror

While developing alongside Biman: `packages/laravel-sprint-board/` on branch `plan/laravel-sprint-package`.
Canonical source of truth: this GitHub repository.
