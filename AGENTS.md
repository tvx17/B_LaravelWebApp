# Repository Guidelines

## Repository Status

This directory is currently empty and has no Git metadata, application files, or dependency manifests. Its name suggests a Laravel web application boilerplate. The conventions below are recommendations for initial setup; update this guide when the actual structure and tooling are established.

## Project Structure & Module Organization

When initializing Laravel, use its conventional layout: `app/` for application code, `routes/` for HTTP and console routes, `resources/` for views and frontend assets, and `public/` for the web entry point. Keep configuration in `config/`, database migrations and factories in `database/`, and automated tests in `tests/`. Avoid committing generated dependencies in `vendor/` or `node_modules/`.

## Build, Test, and Development Commands

No build or development commands are configured yet. Once the relevant manifests and Laravel entry point exist, typical commands are:

- `composer install`: install PHP dependencies from the lockfile.
- `php artisan serve`: start the local application server.
- `php artisan migrate`: apply migrations to the configured database; verify the target environment first.
- `php artisan test`: run the application test suite.
- `npm install` and `npm run dev`: install frontend dependencies and start development assets, if those scripts are defined.
- `npm run build`: produce frontend assets, if configured.

## Coding Style & Naming Conventions

For initial PHP code, use four-space indentation, PascalCase class names, and camelCase method and variable names. Match namespaces to directory paths. Use descriptive migration names and keep controllers focused on request handling. Follow committed formatter settings once available; no formatter or linter is currently configured.

## Testing Guidelines

No testing framework or coverage threshold is established. With Laravel's test tooling, place isolated tests in `tests/Unit/` and application behavior tests in `tests/Feature/`. Name test files `*Test.php`. Cover changed behavior and relevant failure cases, and run the configured suite before requesting review.

## Commit & Pull Request Guidelines

There is no Git history from which to infer commit conventions. Use concise, imperative subjects such as `Add user registration validation`. Keep changes focused. Pull requests should explain the purpose, summarize validation, link relevant issues, and include screenshots for visible interface changes.

## Security & Configuration

Keep credentials out of version control. Provide placeholder configuration in `.env.example`, ignore `.env`, and never commit application secrets or production data.
