@extends('layouts.app')
@section('title', 'Anmelden')
@section('content')
    <section class="mx-auto mt-12 max-w-md rounded-xl bg-white p-8 shadow-sm">
        <h1 class="mb-6 text-2xl font-semibold">Anmelden</h1>
        @if ($errors->any())
            <div role="alert" class="mb-4 rounded bg-red-50 p-3 text-red-800">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-1 block">E-Mail-Adresse</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="w-full rounded border border-slate-300 p-3">
            </div>
            <div>
                <label for="password" class="mb-1 block">Passwort</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded border border-slate-300 p-3">
            </div>
            <button type="submit" class="w-full cursor-pointer rounded bg-blue-700 p-3 font-medium text-white">Anmelden</button>
        </form>
    </section>
@endsection
