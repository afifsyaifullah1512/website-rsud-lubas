<div class="relative w-full sm:w-72" x-data="{ open: false }" @click.outside="open = false">
    <label for="news-search-input" class="sr-only">Cari berita</label>
    <input
        id="news-search-input"
        type="search"
        autocomplete="off"
        wire:model.live.debounce.350ms="q"
        @focus="open = true"
        @input="open = true"
        minlength="{{ \App\Livewire\NewsSearch::MIN_CHARS }}"
        placeholder="Cari berita..."
        class="w-full border-slate-300 rounded-md text-sm pr-9 focus:border-brand-500 focus:ring-brand-500"
    >
    <span class="absolute inset-y-0 right-2 flex items-center text-slate-400">
        <svg wire:loading wire:target="q" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <svg wire:loading.remove wire:target="q" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"></path>
        </svg>
    </span>

    @if ($isSearching)
        <div
            x-show="open"
            x-transition
            x-cloak
            class="absolute z-30 mt-1 w-full sm:w-96 right-0 rounded-md border border-slate-200 bg-white shadow-lg overflow-hidden"
        >
            @forelse ($results as $item)
                <a
                    href="{{ route('berita.show', $item->slug) }}"
                    class="flex gap-3 px-3 py-2.5 hover:bg-slate-50 border-b border-slate-100 last:border-b-0"
                >
                    @if ($item->cover_image)
                        <img
                            loading="lazy"
                            src="{{ \Illuminate\Support\Str::startsWith($item->cover_image, 'http') ? $item->cover_image : asset('storage/'.$item->cover_image) }}"
                            alt=""
                            class="h-12 w-16 flex-none rounded object-cover bg-slate-100"
                        >
                    @endif
                    <span class="min-w-0">
                        @if ($item->category)
                            <span class="block text-[11px] font-medium text-brand-700">{{ $item->category->name }}</span>
                        @endif
                        <span class="block text-sm font-medium text-slate-900 line-clamp-2">{{ $item->title }}</span>
                    </span>
                </a>
            @empty
                <p class="px-3 py-4 text-sm text-slate-500">Tidak ada berita yang cocok dengan "{{ $q }}".</p>
            @endforelse
        </div>
    @endif
</div>
