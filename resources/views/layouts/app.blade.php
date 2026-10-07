<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Laravel Web App')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-5xl items-center justify-between gap-4 p-5" aria-label="Hauptnavigation">
            <a href="{{ url('/') }}" class="font-semibold">Laravel Web App</a>
            @auth
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}">Übersicht</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="cursor-pointer text-blue-700">Abmelden</button>
                    </form>
                </div>
            @endauth
        </nav>
    </header>
    <main class="mx-auto max-w-5xl p-6">@yield('content')</main>
</body>
</html>
