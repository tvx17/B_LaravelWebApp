@extends('layouts.app')
@section('title', 'E-Mail bestätigen')
@section('content')
    <section class="mx-auto mt-12 max-w-md rounded-xl bg-white p-8 shadow-sm">
        <h1 class="mb-6 text-2xl font-semibold">E-Mail bestätigen</h1>
        <p class="mb-4">Bitte bestätige deine E-Mail-Adresse über den Link in der Bestätigungsmail, bevor du die Anwendung verwendest.</p>
        @if (session('status') === 'verification-link-sent')
            <p role="status" class="mb-4 rounded bg-green-50 p-3 text-green-800">Ein neuer Bestätigungslink wurde angefordert. Prüfe dein Postfach.</p>
        @endif
        @if ($errors->any())
            <p role="alert" class="mb-4 text-red-800">{{ $errors->first() }}</p>
        @endif
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="w-full cursor-pointer rounded bg-blue-700 p-3 font-medium text-white">Bestätigungsmail erneut anfordern</button>
        </form>
    </section>
@endsection
