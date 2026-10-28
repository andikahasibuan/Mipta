# AGENTS.md

## Project state

- Laravel 13.39 application: PHP 8.5 runtime (`composer.json` requires `^8.3`), Pest 5, Tailwind CSS 4, Vite 8.
- This is still a fresh skeleton: the only route is `GET /` → `resources/views/welcome.blade.php`, the only model is `App\Models\User`, and there are no API routes, auth scaffolding, or domain features. Don't assume application code exists; search before building.
- `.env` points at SQLite (`database/database.sqlite`, migrations already run). Tests use in-memory SQLite.
- No `.git` directory and no CI workflows in this workspace; run verification locally instead of relying on a pipeline.

## Commands

- First-time setup: `composer run setup` (installs PHP/JS deps, creates `.env`, generates key, migrates, builds assets).
- Development: `composer run dev` → `php artisan dev`, which concurrently runs `artisan serve`, `queue:listen`, and `npm run dev`. `php artisan dev:list` shows the managed processes.
- Assets: `npm run build`. An "Unable to locate file in Vite manifest" error means assets are stale — build or start the dev server.
- Tests: `php artisan test --compact`. Narrow with a path (`php artisan test --compact tests/Feature/ExampleTest.php`) or `--filter=testName`. `vendor/bin/pest` accepts the same arguments. `composer test` additionally clears config first.
- Formatting: `vendor/bin/pint --dirty --format agent` after editing PHP. Do not use `--test`.
- Routes: `php artisan route:list --except-vendor`.

## Testing gotchas

- `tests/Pest.php` binds every Feature test to `Tests\TestCase`, but `RefreshDatabase` is commented out. Any test that touches the database must opt in to the trait itself, or its tables won't exist in the in-memory SQLite database.
- Use model factories (e.g. `UserFactory`) rather than creating records by hand.
- Create tests with `php artisan make:test --pest SomeFeatureTest` (omit the `Feature/` prefix). Don't delete existing tests without approval.

## Frontend

- Tailwind 4 is CSS-first: configuration lives in `resources/css/app.css` (`@import 'tailwindcss'`, `@theme`); there is no `tailwind.config.js`. Add `@source` entries there if styles in a new directory are not picked up.
- Vite entrypoints are fixed in `vite.config.js` (`resources/css/app.css`, `resources/js/app.js`). Fonts (Instrument Sans) are fetched through `laravel-vite-plugin/fonts`.
- `.npmrc` sets `ignore-scripts=true`; keep `npm install --ignore-scripts` behavior in mind for native/postinstall-dependent packages.

## Conventions and tooling

- Laravel 13 model configuration uses PHP attributes: `#[Fillable([...])]` and `#[Hidden([...])]` in `app/Models/User.php`, not `$fillable`/`$hidden` properties. Follow that style.
- Use `php artisan make:* --no-interaction` for new framework files and named routes via `route()`.
- Skills are installed under `.agents/skills/` (not `**/skills/**`). Activate the matching skill before Laravel, testing, Tailwind, or deployment work. There is no `.ai/rules` directory in this repo.
- Laravel Boost MCP is configured in `opencode.json` via `php artisan boost:mcp`. Prefer Boost tools (`search-docs`, `database-query`, `browser-logs`, `last-error`) over shell/file guesses.
- `composer update` runs `php artisan boost:update`, which refreshes Boost guidelines and skills and can overwrite this file. Re-check AGENTS.md and `.agents/skills` after dependency updates.
- Ask before changing dependencies or creating new top-level directories. Do not create documentation files unless requested.
