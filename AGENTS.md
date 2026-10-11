# Repository Guidelines

## Repository Status

This repository contains a Laravel 13 application with Blade/Vite, Fortify, Filament, Spatie Permission and Media Library, and local development tools. Dependency versions are pinned in composer.lock and package-lock.json. Setup documentation is in docs/setup.md.

## Project Structure & Module Organization

When initializing Laravel, use its conventional layout: `app/` for application code, `routes/` for HTTP and console routes, `resources/` for views and frontend assets, and `public/` for the web entry point. Keep configuration in `config/`, database migrations and factories in `database/`, and automated tests in `tests/`. Avoid committing generated dependencies in `vendor/` or `node_modules/`.

## Build, Test, and Development Commands

The interactive bootstrap command is `php setup.php` (also available through `composer setup`). It works before vendor/ exists, asks for confirmation before each step, preserves existing keys/data, and optionally writes a Git-ignored SETUP.md. PHP/Composer and, for frontend assets, Node.js/npm must already be installed. Other commands are:

Without shell access, `public/setup.php` provides the browser wizard described in docs/browser-setup.md. It is disabled without the Git-ignored web-setup.local.php access configuration and locked after completion. Keep token authentication, HTTPS, native-session CSRF protection, fixed setup actions and the completion lock intact. Test browser setup only against temporary fixtures, never the developer's configuration or database.

- `composer install`: install PHP dependencies from the lockfile.
- `php artisan serve`: start the local application server.
- `php artisan migrate`: apply migrations to the configured database; verify the target environment first.
- `php artisan test`: run the application test suite.
- `npm install` and `npm run dev`: install frontend dependencies and start development assets, if those scripts are defined.
- `npm run build`: produce frontend assets, if configured.

## Coding Style & Naming Conventions

For initial PHP code, use four-space indentation, PascalCase class names, and camelCase method and variable names. Match namespaces to directory paths. Use descriptive migration names and keep controllers focused on request handling. Follow committed formatter settings once available; no formatter or linter is currently configured.

## Testing Guidelines

PHPUnit is configured in phpunit.xml with an isolated in-memory SQLite database; no coverage threshold is established. Place isolated tests in `tests/Unit/` and application behavior tests in `tests/Feature/`. Name test files `*Test.php`. Cover changed behavior and relevant failure cases, and run the configured suite before requesting review. Never run setup or migrations against the developer's database as a test. Pint provides PHP formatting (`php vendor/bin/pint --test`).

## Commit & Pull Request Guidelines

There is no Git history from which to infer commit conventions. Use concise, imperative subjects such as `Add user registration validation`. Keep changes focused. Pull requests should explain the purpose, summarize validation, link relevant issues, and include screenshots for visible interface changes.

## Security & Configuration

Keep credentials out of version control. Provide placeholder configuration in `.env.example`, ignore `.env`, and never commit application secrets or production data.
