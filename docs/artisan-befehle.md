# Artisan-Befehle dieser Installation

Stand: 2026-10-08, lokale Umgebung. Aufruf jeweils mit `php artisan BEFEHL`; Details und Optionen mit `php artisan help BEFEHL`. Diese Liste dokumentiert verfügbare Befehle und führt sie nicht aus. Installations-, Lösch-, Migrations- und Regelerzeugungsbefehle nur für den jeweiligen Zweck einsetzen. Insbesondere darf `brain:generate-rules` die vorhandene `AGENTS.md` nicht überschreiben.

[Zur Installationsübersicht](installationsuebersicht.md)

| Befehl | Beschreibung |
|---|---|
| `setup:admin` | Administrator nach Bestätigung anlegen oder bestehendem Konto Filament-/Runner-Rechte geben; seit 2026-10-11. Gesamtes Setup separat mit `composer setup` beziehungsweise `php setup.php`. |
| `_complete` | Internal command to provide shell completion suggestions |
| `about` | Display basic information about your application |
| `clear-compiled` | Remove the compiled class file |
| `completion` | Dump the shell completion script |
| `db` | Start a new database CLI session |
| `dev` | Run the dev processes |
| `docs` | Access the Laravel documentation |
| `down` | Put the application into maintenance / demo mode |
| `env` | Display the current framework environment |
| `help` | Display help for a command |
| `inspire` | Display an inspiring quote |
| `invoke-serialized-closure` | Invoke the given serialized closure |
| `list` | List commands |
| `migrate` | Run the database migrations |
| `optimize` | Cache framework bootstrap, configuration, and metadata to increase performance |
| `pail` | Tails the application logs |
| `reload` | Reload running services |
| `serve` | Serve the application on the PHP development server |
| `test` | Run the application tests |
| `tinker` | Interact with your application |
| `up` | Bring the application out of maintenance mode |
| `app:backup` | SQLite vorbereiten und das Projekt mit Kopia sichern |
| `app:create-user` | Benutzer mit verdeckter Passworteingabe anlegen |
| `auth:clear-resets` | Flush expired password reset tokens |
| `brain:export-context` | Export a deterministic AI context snapshot from the scanned graph |
| `brain:generate-rules` | Generate AI assistant context files (CLAUDE.md, .cursorrules, copilot-instructions, etc.) from the scanned graph |
| `brain:scan` | Analyze this Laravel project and open the interactive graph viewer |
| `cache:clear` | Flush the application cache |
| `cache:forget` | Remove an item from the cache |
| `cache:prune-stale-tags` | Prune stale cache tags from the cache (Redis only) |
| `channel:list` | List all registered private broadcast channels |
| `config:cache` | Create a cache file for faster configuration loading |
| `config:clear` | Remove the configuration cache file |
| `config:publish` | Publish configuration files to your application |
| `config:show` | Display all of the values for a given configuration file or key |
| `db:monitor` | Monitor the number of connections on the specified database |
| `db:seed` | Seed the database with records |
| `db:show` | Display information about the given database |
| `db:table` | Display information about the given database table |
| `db:wipe` | Drop all tables, views, and types |
| `debugbar:clear` | Clear the Debugbar Storage |
| `debugbar:find` | List the Debugbar Storage |
| `debugbar:get` | Get a Debugbar request from the Storage |
| `debugbar:install-skill` | Install the Debugbar debugging skill for AI coding agents |
| `debugbar:queries` | Shows Debugbar Queries from a specific request |
| `dev:list` | List the registered dev processes |
| `env:decrypt` | Decrypt an environment file |
| `env:encrypt` | Encrypt an environment file |
| `event:cache` | Discover and cache the application's events and listeners |
| `event:clear` | Clear all cached events and listeners |
| `event:generate` | Generate the missing events and listeners based on registration |
| `event:list` | List the application's events and listeners |
| `filament:about` | Display basic information about Filament packages that are installed |
| `filament:assets` | Set up Filament assets |
| `filament:cache-components` | Cache all components |
| `filament:check-translations` | Check for missing and removed translations |
| `filament:clear-cached-components` | Clear all cached components |
| `filament:install` | Install Filament |
| `filament:optimize` | Cache components and Blade icons to increase performance |
| `filament:optimize-clear` | Remove the cached components and Blade icons |
| `filament:upgrade` | Upgrade Filament to the latest version |
| `fortify:install` | Install all of the Fortify resources |
| `icons:cache` | Discover icon sets and generate a manifest file |
| `icons:clear` | Remove the blade icons manifest file |
| `install:api` | Create an API routes file and install Laravel Sanctum or Laravel Passport |
| `install:broadcasting` | Create a broadcasting channel routes file |
| `key:generate` | Set the application key |
| `lang:publish` | Publish all language files that are available for customization |
| `livewire:attribute` | Create a new Livewire attribute class |
| `livewire:config` | Publish Livewire config file |
| `livewire:configure-s3-upload-cleanup` | Configure temporary file upload s3 directory to automatically cleanup files older than 24hrs |
| `livewire:convert` | Convert a Livewire component between single-file and multi-file formats |
| `livewire:form` | Create a new Livewire form class |
| `livewire:layout` | Create a new app layout file |
| `livewire:make` | Create a new Livewire component |
| `livewire:publish` | Publish Livewire configuration |
| `livewire:stubs` | Publish Livewire stubs |
| `make:cache-table` | Create a migration for the cache database table |
| `make:cast` | Create a new custom Eloquent cast class |
| `make:channel` | Create a new channel class |
| `make:class` | Create a new class |
| `make:command` | Create a new Artisan command |
| `make:component` | Create a new view component class |
| `make:config` | Create a new configuration file |
| `make:controller` | Create a new controller class |
| `make:enum` | Create a new enum |
| `make:event` | Create a new event class |
| `make:exception` | Create a new custom exception class |
| `make:factory` | Create a new model factory |
| `make:filament-cluster` | Create a new Filament cluster class |
| `make:filament-exporter` | Create a new Filament exporter class |
| `make:filament-form` | Create a new Filament form schema class |
| `make:filament-form-field` | Create a new form field class and view |
| `make:filament-importer` | Create a new Filament importer class |
| `make:filament-infolist-entry` | Create a new infolist entry class and view |
| `make:filament-issue` | Generates a link to the Filament issue page and pre-fills the version numbers. |
| `make:filament-livewire-form` | Create a new Livewire component containing a Filament form |
| `make:filament-livewire-schema` | Create a new Livewire component containing a Filament schema |
| `make:filament-livewire-table` | Create a new Livewire component containing a Filament table |
| `make:filament-page` | Create a new Filament page class and view |
| `make:filament-panel` | Create a new Filament panel |
| `make:filament-relation-manager` | Create a new Filament relation manager class for a resource |
| `make:filament-resource` | Create a new Filament resource class and default page classes |
| `make:filament-rich-content-custom-block` | Create a new rich editor custom block class and view |
| `make:filament-schema` | Create a new Filament schema class |
| `make:filament-schema-component` | Create a new schema component class and view |
| `make:filament-table` | Create a new Filament table class |
| `make:filament-table-column` | Create a new table column class and cell view |
| `make:filament-theme` | Create a new Filament panel theme |
| `make:filament-user` | Create a new Filament user |
| `make:filament-widget` | Create a new Filament widget class |
| `make:interface` | Create a new interface |
| `make:job` | Create a new job class |
| `make:job-middleware` | Create a new job middleware class |
| `make:listener` | Create a new event listener class |
| `make:livewire` | Create a new Livewire component |
| `make:mail` | Create a new email class |
| `make:middleware` | Create a new HTTP middleware class |
| `make:migration` | Create a new migration file |
| `make:model` | Create a new Eloquent model class |
| `make:notification` | Create a new notification class |
| `make:notifications-table` | Create a migration for the notifications table |
| `make:observer` | Create a new observer class |
| `make:policy` | Create a new policy class |
| `make:provider` | Create a new service provider class |
| `make:queue-batches-table` | Create a migration for the batches database table |
| `make:queue-failed-table` | Create a migration for the failed queue jobs database table |
| `make:queue-table` | Create a migration for the queue jobs database table |
| `make:request` | Create a new form request class |
| `make:resource` | Create a new resource |
| `make:rule` | Create a new validation rule |
| `make:scope` | Create a new scope class |
| `make:seeder` | Create a new seeder class |
| `make:session-table` | Create a migration for the session database table |
| `make:test` | Create a new test class |
| `make:trait` | Create a new trait |
| `make:view` | Create a new view |
| `media-library:clean` | Clean deprecated conversions and files without related model |
| `media-library:clear` | Delete all items in a media collection |
| `media-library:regenerate` | Regenerate the derived images of media |
| `migrate:fresh` | Drop all tables and re-run all migrations |
| `migrate:install` | Create the migration repository |
| `migrate:refresh` | Reset and re-run all migrations |
| `migrate:reset` | Rollback all database migrations |
| `migrate:rollback` | Rollback the last database migration |
| `migrate:status` | Show the status of each migration |
| `model:prune` | Prune models that are no longer needed |
| `model:show` | Show information about an Eloquent model |
| `optimize:clear` | Remove the cached bootstrap files |
| `package:discover` | Rebuild the cached package manifest |
| `permission:assign-role` | Assign a role to a user |
| `permission:cache-reset` | Reset the permission cache |
| `permission:create-permission` | Create a permission |
| `permission:create-role` | Create a role |
| `permission:setup-teams` | Setup the teams feature by generating the associated migration. |
| `permission:show` | Show a table of roles and permissions per guard |
| `queue:clear` | Delete all of the jobs from the specified queues |
| `queue:failed` | List all of the failed queue jobs |
| `queue:flush` | Flush the failed queue jobs |
| `queue:forget` | Delete a failed queue job |
| `queue:listen` | Listen to a given queue |
| `queue:monitor` | Monitor the size of the specified queues |
| `queue:pause` | Pause job processing for a specific queue |
| `queue:prune-batches` | Prune stale entries from the batches database |
| `queue:prune-failed` | Prune stale entries from the failed jobs table |
| `queue:restart` | Restart queue worker daemons after their current job |
| `queue:resume` | Resume job processing for a paused queue |
| `queue:retry` | Retry a failed queue job |
| `queue:retry-batch` | Retry the failed jobs for a batch |
| `queue:work` | Start processing jobs on the queue as a daemon |
| `route:cache` | Create a route cache file for faster route registration |
| `route:clear` | Remove the route cache file |
| `route:list` | List all registered routes |
| `schedule:clear-cache` | Delete the cached mutex files created by scheduler |
| `schedule:finish` | Handle the completion of a scheduled command |
| `schedule:interrupt` | Interrupt the current schedule run |
| `schedule:list` | List all scheduled tasks |
| `schedule:pause` | Pause the scheduler |
| `schedule:resume` | Resume the schedule |
| `schedule:run` | Run the scheduled commands |
| `schedule:test` | Run a scheduled command |
| `schedule:work` | Start the schedule worker |
| `schema:dump` | Dump the given database schema |
| `storage:link` | Create the symbolic links configured for the application |
| `storage:unlink` | Delete existing symbolic links configured for the application |
| `stub:publish` | Publish all stubs that are available for customization |
| `telescope:clear` | Delete all Telescope data from storage |
| `telescope:install` | Install all of the Telescope resources |
| `telescope:list` | List Telescope entries |
| `telescope:pause` | Pause all Telescope watchers |
| `telescope:prune` | Prune stale entries from the Telescope database |
| `telescope:publish` | Publish all of the Telescope resources |
| `telescope:resume` | Unpause all Telescope watchers |
| `telescope:show` | Show a Telescope entry with full batch context |
| `vendor:publish` | Publish any publishable assets from vendor packages |
| `view:cache` | Compile all of the application's Blade templates |
| `view:clear` | Clear all compiled view files |
