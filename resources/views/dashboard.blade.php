@extends('layouts.app')
@section('title', 'Übersicht')
@section('content')
    <h1 class="mb-4 text-2xl font-semibold">Willkommen, {{ auth()->user()->name }}</h1>
    @can('access-admin')
        <a href="{{ route('filament.admin.pages.dashboard') }}" class="mb-4 inline-block rounded bg-blue-700 px-5 py-3 text-white">Admin-Panel öffnen</a>
    @endcan
    @if (config('artisan-runner-ui.enabled') && app()->environment(config('artisan-runner-ui.environments', [])))
        @can('run-artisan-runner-ui')
            <a href="{{ route('artisan-runner-ui.index') }}" class="inline-block rounded bg-blue-700 px-5 py-3 text-white">Artisan Runner UI öffnen</a>
        @else
            <p>Du bist angemeldet. Für die Artisan Runner UI ist eine zusätzliche Berechtigung erforderlich.</p>
        @endcan
    @else
        <p>Du bist angemeldet.</p>
    @endif
@endsection
