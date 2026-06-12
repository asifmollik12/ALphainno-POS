<div class="inline-block text-left" x-data="rowActionsDropdown()">
    <button type="button" x-ref="trigger" @click="toggle()"
            class="p-1.5 rounded hover:bg-slate-100 text-slate-500 focus:outline-none focus:ring-2 focus:ring-ai-purple/30"
            aria-label="Actions" aria-haspopup="true" :aria-expanded="open">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
    </button>
    <template x-teleport="body">
        <div x-ref="menu" x-show="open" x-cloak @click.outside="close()" @keydown.escape.window="close()"
             class="fixed z-[200] min-w-[10rem] bg-white rounded-lg border border-slate-200 shadow-lg py-1 text-left text-sm">
            {{ $slot }}
        </div>
    </template>
</div>
