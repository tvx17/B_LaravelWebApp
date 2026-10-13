<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;

class CreateUser extends Command
{
    protected $signature = 'app:create-user {email} {--name= : Anzeigename} {--runner : Zugriff auf Artisan Runner UI erteilen} {--admin : Zugriff auf Filament erteilen}';

    protected $description = 'Benutzer mit verdeckter Passworteingabe anlegen';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Dieser Befehl benötigt eine interaktive Passworteingabe.');

            return self::FAILURE;
        }

        $data = [
            'email' => $this->argument('email'),
            'name' => $this->option('name') ?: $this->ask('Name'),
            'password' => $this->secret('Passwort (mindestens 12 Zeichen)'),
            'password_confirmation' => $this->secret('Passwort wiederholen'),
        ];
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Password::min(12)],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = DB::transaction(function () use ($data): User {
            $user = User::create(collect($data)->only(['name', 'email', 'password'])->all());
            if ($this->option('runner')) {
                $user->givePermissionTo(Permission::findOrCreate('run-artisan-runner-ui', 'web'));
            }
            if ($this->option('admin')) {
                $user->givePermissionTo(Permission::findOrCreate('access-admin', 'web'));
            }

            return $user;
        });
        event(new Registered($user));
        $this->info('Benutzer angelegt.');

        return self::SUCCESS;
    }
}
