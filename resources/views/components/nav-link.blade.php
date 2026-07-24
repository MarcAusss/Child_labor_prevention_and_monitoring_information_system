@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center rounded-xl bg-sky-50 px-4 py-2.5 text-sm font-extrabold text-sky-800 ring-1 ring-sky-200'
    : 'inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
