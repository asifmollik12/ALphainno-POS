<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">{{ $title }}</h1>
        @isset($subtitle)<p class="text-slate-500 text-sm mt-1">{{ $subtitle }}</p>@endisset
    </div>
    @isset($actionUrl)
        <a href="{{ $actionUrl }}" class="px-4 py-2 rounded-lg bg-ai-purple hover:bg-violet-500 text-white text-sm font-medium">{{ $actionLabel ?? '+ Add' }}</a>
    @endisset
</div>
