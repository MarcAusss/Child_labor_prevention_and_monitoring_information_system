@props(['active'])

@php
$classes = ($active ?? false)
    ? 'block w-full rounded-xl bg-sky-50 px-4 py-3 text-start text-sm font-extrabold text-sky-800 ring-1 ring-sky-200'
    : 'block w-full rounded-xl px-4 py-3 text-start text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
