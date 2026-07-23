# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Real Backlog HTTP driver (`hydrate`, `updatePriority`) with `ConfigBacklogCredentials` / env support

## [0.1.0] - 2026-07-24

### Added

- JSON HTTP API under configurable `/api/sprints` (CRUD, members, issues, completion, dashboard, retrospective, exports)
- Domain services: sprint CRUD/member sync, issue link/unlink, completion sibling sync, status resolver
- Hydrate cache, closed-issue sync (`refresh`), and dashboard aggregates
- Default authorizer, Eloquent user resolver/directory, `NullIssueTracker` and `BacklogIssueTracker` stub
- Schema migrations for `sprints`, `sprint_members`, `sprint_issues` (guarded, configurable table prefix)
- Eloquent models, factories, and status/role/completion enums
- Package service provider, publishable config/lang/migrations
- Orchestra Testbench suite and GitHub Actions CI matrix (PHP 8.2–8.4 × Laravel 10–12)
- Examples and security/contributing docs

[Unreleased]: https://github.com/eather009/laravel-sprint-board/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/eather009/laravel-sprint-board/releases/tag/v0.1.0
