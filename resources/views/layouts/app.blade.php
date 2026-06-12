<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'POS') — Alphainno</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
    @auth
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('pos.index') }}" class="font-semibold text-white tracking-tight">Alphainno POS</a>
            <nav class="flex items-center gap-1 text-sm">
                <a href="{{ route('pos.index') }}"
                   class="px-3 py-1.5 rounded-lg {{ request()->routeIs('pos.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    Sell
                </a>
                <a href="{{ route('products.index') }}"
                   class="px-3 py-1.5 rounded-lg {{ request()->routeIs('products.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    Products
                </a>
                <a href="{{ route('sales.index') }}"
                   class="px-3 py-1.5 rounded-lg {{ request()->routeIs('sales.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    Sales
                </a>
                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        Logout
                    </button>
                </form>
            </nav>
        </div>
    </header>
    @endauth

    <main class="max-w-6xl mx-auto px-4 py-6">
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-emerald-900/50 border border-emerald-700 text-emerald-100 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="mb-4 rounded-lg bg-red-900/40 border border-red-700 text-red-100 px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
