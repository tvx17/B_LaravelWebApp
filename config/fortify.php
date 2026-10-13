<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'lowercase_usernames' => false,
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],
    'auth_middleware' => 'auth',
    'views' => true,
    'limiters' => ['login' => 'fortify-login'],
    'redirects' => ['logout' => '/login'],
    // Weitere Funktionen nach Festlegung der Konten- und Sicherheitsanforderungen aktivieren.
    'features' => [Features::emailVerification()],
];
