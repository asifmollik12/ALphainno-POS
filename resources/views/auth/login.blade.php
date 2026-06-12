@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-white">Alphainno POS</h1>
            <p class="text-slate-400 mt-1 text-sm">Sign in to manage products and sales</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm text-slate-300 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-lg bg-slate-950 border border-slate-700 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label for="password" class="block text-sm text-slate-300 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                       class="w-full rounded-lg bg-slate-950 border border-slate-700 px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-400">
                <input type="checkbox" name="remember" class="rounded border-slate-600">
                Remember me
            </label>

            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-medium py-2.5 rounded-lg transition">
                Sign in
            </button>
        </form>
    </div>
</div>
@endsection
