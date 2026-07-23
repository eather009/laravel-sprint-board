# laravel-sprint-board

Standalone **Laravel Sprint Board API** package — JSON API for sprints, members, and linked issues. Install in any Laravel 10–12 app to run a sprint board without shipping UI.

- **Owner:** [Iftekhar Ahmed Eather](https://github.com/eather009) (`eather009`)
- **GitHub:** https://github.com/eather009/laravel-sprint-board  
- **Composer (planned):** `eather009/laravel-sprint-board`  
- **Default tracker:** [Backlog](https://backlog.com/) (swappable via `IssueTracker` contract)  
- **Users:** host Laravel `User` model  
- **UI:** not included (host / SPA)

> Implementation not started. Local planning lives in `PLAN.md` (not published).

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
| Owner | Iftekhar Ahmed Eather (`eather009`) |
| Default tracker | Backlog |
| Package skeleton | Pending Phase 1 |
| License | MIT |
