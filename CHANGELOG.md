# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Domain services: sprint CRUD/member sync, issue link/unlink, completion sibling sync, status resolver
- Default authorizer, Eloquent user resolver/directory, and `NullIssueTracker`
- Schema migrations for `sprints`, `sprint_members`, `sprint_issues` (guarded, configurable table prefix)
- Eloquent models, factories, and status/role/completion enums
- Package skeleton: service provider, publishable config, route group stub, contracts, and default `BacklogIssueTracker` binding
- Orchestra Testbench smoke + model/domain tests and GitHub Actions CI matrix (PHP 8.2–8.4 × Laravel 10–12)
