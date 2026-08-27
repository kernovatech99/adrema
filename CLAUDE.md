# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

AdReMa ("AddressManagement") is a Laravel 11 + Inertia/Vue 3 app for German Scout groups (DPSG) to manage member data, courses, invoicing/contributions, and syncs with the external NaMi member database. Backend is PHP 8.3, frontend is Vue 3 + TypeScript rendered via Inertia (no separate SPA API).

## Commands

### PHP / backend

- Run all tests: `php artisan test` (or `vendor/bin/pest`)
- Run a single test file: `php artisan test tests/Feature/Member/SomeTest.php`
- Run a single test by name: `php artisan test --filter=test_name`
- Static analysis: `vendor/bin/phpstan analyse` (larastan, level 6, config in `phpstan.neon`)
- Test suites are split in `phpunit.xml`: `Unit`, `Feature`, `Fileshare`, `Arch` (architecture rules in `tests/Arch.php`), `NamiUnit` (packages/laravel-nami), `EndToEnd`

### JS / frontend

- Dev server: `npm run dev` (alias for `vite`)
- Production build: `npm run prod`
- Lint: `npm run lint`, autofix: `npm run fix`
- Rebuild SVG sprite: `npm run img`
- The `packages/adrema-form` package (public event-registration form, embeddable) has its own `npm run build` / `build-import` (import mode is used when embedded in the main app) and is built separately.

### Environment

- Local dev runs via Docker Compose (`docker-compose.yml`); `.app.env` holds environment config, copied from `.app.env.example`.
- Submodules must be initialized: `git submodule update --init`.
- CI (`.drone.yml`) runs: composer install → npm build (main app + `adrema-form`) → `php artisan migrate` → `php artisan test` → `vendor/bin/phpstan analyse`.

## Architecture

### Backend structure

Code under `app/` is organized by **domain module**, not by technical layer (no global `app/Http/Controllers` for most features). Each domain (e.g. `Member`, `Activity`, `Form`, `Invoice`, `Course`, `Fileshare`, `Contribution`, `Mailman`, `Mailgateway`, `Efz`, `Prevention`) typically contains its own `Actions/`, models, requests, and resources together. Older/shared code still lives in `app/Actions`.

- **Actions pattern**: business logic lives in single-purpose Action classes (`lorisleiva/laravel-actions`, `use AsAction`) with a `handle()` method, invoked directly from routes in `routes/web.php` rather than through traditional controllers.
- **NaMi integration**: `packages/laravel-nami` is a local Composer package (symlinked via `repositories` path in `composer.json`) wrapping the external NaMi API (members, courses, memberships, confessions, fees, regions, etc.). App-level `Pull*Action`/`Insert*Action` classes (`app/Actions/PullMemberAction.php`, `InsertMemberAction.php`, etc.) sync NaMi data into local models. - Please never edit files here, unless explicitly specified.
- **Local packages** (all under `packages/`, wired via Composer path repositories): `laravel-nami` (NaMi API client), `table-document` (PDF/table document generation), `flysystem-webdav` (WebDAV filesystem driver, used for cloud file storage/CardDAV-adjacent features), `tex` (LaTeX rendering, used for invoices/Bescheinigungen), `medialibrary-helper` (Vue components + backend helpers for file uploads, e.g. `FSinglefile`/`FMultiplefiles`), `adrema-form` (standalone Vite/Vue app for public event registration forms, built separately and embedded/imported into the main app). - Please never edit files here, unless explicitly specified
- **Modules feature flag**: `App\Module\Module` enum (`bill`, `course`, `event`) + `ModuleSettings` gate optional features per-installation; checked server-side via `hasModule()` and client-side via the `hasModule` Vue mixin (`resources/js/mixins/hasModule.js`).
- Data transfer uses `spatie/laravel-data` (`Data` classes) in several domains instead of plain arrays/DTOs.
- PHPStan type aliases for complex array shapes (e.g. contribution API payloads) are defined centrally in `phpstan.neon`.

### Frontend structure

- Entry point `resources/js/app.js` bootstraps Inertia + Pinia + Vue; pages are Vue SFCs under `resources/js/views/**`, resolved by path via Inertia's `resolve`.
- `resources/js/layouts/AppLayout.vue` is the default page layout, applied automatically unless a page sets its own `layout`.
- Global mixins (`hasModule`, `hasFlash`) are applied app-wide instead of per-component imports.
- State is managed with Pinia stores (`resources/js/stores`).

### Testing conventions

- Tests use Pest (`pestphp/pest`), with `tests/TestCase.php` / `tests/EndToEndTestCase.php` / `tests/FileshareTestCase.php` as base cases for different suites.
- `tests/RequestFactories` (worksome/request-factories) and `tests/Datasets` provide reusable test data/request builders.
- `tests/Arch.php` enforces architectural constraints via Pest's arch testing.
