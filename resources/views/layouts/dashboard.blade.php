<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Alphainno POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
    @stack('head')
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="w-64 bg-[#0f172a] text-slate-200 flex flex-col fixed inset-y-0 left-0 z-30">
        <div class="px-5 py-5 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <div class="font-bold text-white text-sm">{{ $shopSetting->company_name ?? 'Alphainno POS' }}</div>
                    <div class="text-[11px] text-slate-500 uppercase tracking-wider">Point of Sale</div>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto sidebar-scroll py-3 text-[11px] font-semibold tracking-wide">
            @foreach ($posMenu as $item)
                @if (isset($item['route']))
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-5 py-2.5 {{ request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route']) ? 'bg-slate-800/80 text-white border-r-2 border-cyan-400' : 'text-slate-400 hover:text-white hover:bg-slate-800/40' }}">
                        @include('partials.menu-icon', ['icon' => $item['icon']])
                        <span>{{ strtoupper($item['label']) }}</span>
                    </a>
                @else
                    @php
                        $childRoutes = collect($item['children'])->pluck('route')->all();
                        $open = collect($childRoutes)->contains(fn ($r) => request()->routeIs(str_replace('.index', '.*', $r)) || request()->routeIs($r));
                    @endphp
                    <div x-data="{ open: {{ $open ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between gap-3 px-5 py-2.5 text-slate-400 hover:text-white hover:bg-slate-800/40">
                            <span class="flex items-center gap-3">
                                @include('partials.menu-icon', ['icon' => $item['icon']])
                                <span>{{ strtoupper($item['label']) }}</span>
                            </span>
                            <svg class="w-3 h-3 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="pb-1">
                            @foreach ($item['children'] as $child)
                                <a href="{{ route($child['route']) }}"
                                   class="block pl-14 pr-5 py-2 {{ request()->routeIs($child['route']) || request()->routeIs(str_replace('.index', '.*', $child['route'])) ? 'text-cyan-400' : 'text-slate-500 hover:text-white' }}">
                                    {{ strtoupper($child['label']) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>
    </aside>

    {{-- Main --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        <header class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center gap-2">
                <a href="{{ route('purchases.create') }}" class="px-3 py-1.5 rounded-full bg-violet-600 hover:bg-violet-500 text-white text-xs font-medium">Create Purchase</a>
                <a href="{{ route('transactions.create') }}" class="px-3 py-1.5 rounded-full bg-blue-600 hover:bg-blue-500 text-white text-xs font-medium">Create Transaction</a>
                <a href="{{ route('pos.index') }}" class="px-3 py-1.5 rounded-full bg-violet-600 hover:bg-violet-500 text-white text-xs font-medium inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    POS
                </a>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-600">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-500" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-6">
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
            @endif
            @if (isset($errors) && $errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
