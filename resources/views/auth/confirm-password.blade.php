@extends('layouts.app')
@section('title', 'Passwort bestätigen')
@section('content')
    <section class="mx-auto mt-12 max-w-md rounded-xl bg-white p-8 shadow-sm">
        <h1 class="mb-6 text-2xl font-semibold">Passwort bestätigen</h1>
        @if ($errors->any())
            <div role="alert" class="mb-4 rounded bg-red-50 p-3 text-red-800">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('password.confirm.store') }}" class="space-y-5">
            @csrf
            <label for="password" class="block">Passwort</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required autofocus class="w-full rounded border border-slate-300 p-3">
            <button type="submit" class="w-full cursor-pointer rounded bg-blue-700 p-3 font-medium text-white">Bestätigen</button>
        </form>
    </section>
@endsection
