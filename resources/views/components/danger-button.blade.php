<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-transparent bg-red-700 px-5 py-2.5 text-xs font-extrabold uppercase tracking-[0.12em] text-white shadow-sm hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-200 disabled:opacity-50'
]) }}>
    {{ $slot }}
</button>
