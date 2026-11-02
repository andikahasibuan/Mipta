# AGENTS.md

## Project purpose

MIPTA (Sistem Monitoring Distribusi Minyakita) is an internal admin web app for Dinas Perdagangan Kota Padang. It monitors Minyakita distribution from distributors to markets (pasar) and recipient stores (toko penerima). All user-facing copy is Indonesian.

Goals reflected by the current UI and routes:

- Centralize distribution reports and monthly carton totals.
- Monitor active distributors and covered markets.
- Support supervision and follow-up.
- Produce monthly recaps and exports.
- Provide role-based access for admin, distributor, kepala dinas, and UPTD pasar (only admin is implemented today).

Design references live in `doc/designs/` (dashboard, users/master data, login). Match the existing UI and palette instead of inventing a new style.

## Current state (snapshot 2026-11-02)

Laravel 13.39 on PHP 8.5, Pest 5, Tailwind CSS 4, Vite 8, Chart.js 4. Local database is MySQL `mipta`; tests use in-memory SQLite.

### Implemented

- Login/logout with email or username, remember me, and a 5-attempt rate limit. Only `App\Enums\Role::Admin` may sign in.
- Admin layout with an 8-item sidebar, notification dropdown, and profile menu.
- `/admin` dashboard: distributor/market statistics from the database, month/market/distributor filters, Chart.js line chart, and a per-distributor panel. Report and carton values are intentionally `0`.
- `/admin/users`: database-backed summary cards (users, distributors, stores). Export button is disabled.
- `/admin/distributors`: paginated, read-only table (no create/edit/delete yet).
- Models and factories: `User`, `Distributor`, `Market`, `RecipientStore`.
- 77 Pest tests pass (240 assertions at the time of writing).

### Placeholder or missing

- Title-only views: `admin/distribution`, `admin/monitoring`, `admin/supervision`, `admin/reports`, `admin/activity`, `admin/profile`.
- No CRUD for master data, no exports, no API, no user management, no password reset, no non-admin dashboards.
- No distribution-report domain. Commit `af04f63` removed `DistributionReport`, `ReportStatus`, its migration, factory, and tests, so dashboard report metrics are hard-coded zero.
- `resources/views/welcome.blade.php` is the unused Laravel default page; `/` always redirects.
- Do not "fix" the zero metrics or fill placeholder pages without confirming domain requirements with the user.

## Domain model

- `User`: name, unique username, email, password, role (`App\Enums\Role`, default admin). `initials()` powers the avatar.
- `Distributor`: unique code, name, `is_active`.
- `Market` (pasar): unique code, name, `is_active`; `hasMany` recipient stores.
- `RecipientStore` (toko penerima): `market_id` (`restrictOnDelete`), unique code, name, `is_active`; `belongsTo` market.
- Missing: distribution reports. The removed schema was `code`, `distributor_id`, `market_id`, `report_date`, `cartons`, `status` (`ReportStatus::Menunggu`). Confirm before restoring.

## Routes and access

- 13 web routes, no API routes.
- `/` redirects guests to `login` and authenticated users to `admin.dashboard`.
- `GET/POST login` is guest-only; `POST logout` requires auth.
- Every `/admin/*` route uses `auth` + `role:admin` (middleware alias registered in `bootstrap/app.php`).
- Only `Role::Admin` can authenticate; the other roles exist in the enum but have no UI.

## Commands

- First-time setup: `composer run setup`. It requires the MySQL database `mipta` to exist and valid `.env` credentials; it installs dependencies, migrates, and builds assets.
- Development: `composer run dev` → `php artisan dev`, which concurrently runs `artisan serve`, `queue:listen`, and `npm run dev`. `php artisan dev:list` shows managed processes.
- Assets: `npm run build`. An "Unable to locate file in Vite manifest" error means assets are stale — build or start the dev server.
- Tests: `php artisan test --compact`. Narrow with a path (`php artisan test --compact tests/Feature/ExampleTest.php`) or `--filter=testName`. `vendor/bin/pest` accepts the same arguments. `composer test` also clears config first.
- Formatting: `vendor/bin/pint --dirty --format agent` after editing PHP. Do not use `--test`.
- Routes: `php artisan route:list --except-vendor`.

## Testing gotchas

- `tests/Pest.php` binds Feature tests to `Tests\TestCase`, but `RefreshDatabase` is commented out. Any test touching the database must opt in with `uses(RefreshDatabase::class)`, or tables will not exist.
- Use model factories rather than creating records by hand.
- Test descriptions are written in Indonesian (`it('menampilkan ...')`); follow that convention.
- Create tests with `php artisan make:test --pest SomeFeatureTest` (omit the `Feature/` prefix). Do not delete existing tests without approval.

## Frontend

- Tailwind 4 is CSS-first: configuration lives in `resources/css/app.css` (`@import 'tailwindcss'`, `@theme`); there is no `tailwind.config.js`. Add `@source` entries if styles in a new directory are not picked up.
- Vite entrypoints are fixed in `vite.config.js` (`resources/css/app.css`, `resources/js/app.js`). Instrument Sans is fetched through `laravel-vite-plugin/fonts`.
- `resources/js/app.js` exposes Chart.js as `window.Chart` and closes the profile/notification dropdowns on outside click or Escape.
- Reuse the existing palette (`#2D507E`, `#5191E4`, `#FDBA12`) and Blade patterns in `resources/views/layouts/admin.blade.php`.
- `.npmrc` sets `ignore-scripts=true`; keep `npm install --ignore-scripts` behavior in mind for native/postinstall-dependent packages.

## Conventions and tooling

- Laravel 13 model configuration uses PHP attributes: `#[Fillable([...])]` and `#[Hidden([...])]` (see `app/Models/User.php`), not `$fillable`/`$hidden` properties.
- Admin controllers live in `app/Http/Controllers/Admin`, views in `resources/views/admin`, and route names use the `admin.*` prefix. Use named routes via `route()`.
- Use `php artisan make:* --no-interaction` for new framework files.
- Skills are installed under `.agents/skills/`. Activate the matching skill before Laravel, testing, Tailwind, or deployment work. There is no `.ai/rules` directory.
- Laravel Boost MCP is configured in `opencode.json` via `php artisan boost:mcp`. Prefer Boost tools (`search-docs`, `database-query`, `browser-logs`, `last-error`) over shell/file guesses.
- `composer update` runs `php artisan boost:update`, which refreshes Boost guidelines and skills and can overwrite this file. Re-check AGENTS.md after dependency updates.
- Ask before changing dependencies or creating new top-level directories. Do not create documentation files unless requested.

## Repository snapshot (2026-11-02)

- Git repository exists; remote is `github.com/andikahasibuan/Mipta`. Current branch is `pimpinan`; `origin/main` is one commit ahead (`7390ba8`) and removes the Activity and Profile pages. Check the branch before working on those pages.
- The local MySQL database currently has one seeded admin and no distributor/market/store rows.
- The seed admin account is defined in `database/seeders/AdminSeeder.php` (local development only).
- No CI workflows; run verification locally.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>
