<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-xs font-extrabold uppercase tracking-[0.12em] text-slate-700 shadow-sm hover:border-sky-300 hover:bg-sky-50 hover:text-sky-800 focus:outline-none focus:ring-4 focus:ring-sky-100 disabled:opacity-50'
]) }}>
    {{ $slot }}
</button>
