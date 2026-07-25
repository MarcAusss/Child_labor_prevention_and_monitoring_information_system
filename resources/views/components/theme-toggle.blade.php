@props(['compact' => false])

<div
    x-data
    class="theme-switcher"
    aria-label="Color theme"
>
    <button
        type="button"
        class="theme-toggle-button"
        @click="$store.theme.cycle()"
        :title="$store.theme.label"
        :aria-label="$store.theme.label"
    >
        <span class="theme-toggle-icon" x-show="$store.theme.mode === 'light'" x-cloak>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <circle cx="12" cy="12" r="4" stroke-width="1.8"/>
                <path stroke-linecap="round" stroke-width="1.8" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
            </svg>
        </span>

        <span class="theme-toggle-icon" x-show="$store.theme.mode === 'dark'" x-cloak>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/>
            </svg>
        </span>

        <span class="theme-toggle-icon" x-show="$store.theme.mode === 'system'" x-cloak>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <rect x="3" y="4" width="18" height="13" rx="2" stroke-width="1.8"/>
                <path stroke-linecap="round" stroke-width="1.8" d="M8 21h8m-4-4v4"/>
            </svg>
        </span>

        @unless($compact)
            <span class="hidden text-xs font-bold sm:inline" x-text="$store.theme.shortLabel"></span>
        @endunless
    </button>
</div>
