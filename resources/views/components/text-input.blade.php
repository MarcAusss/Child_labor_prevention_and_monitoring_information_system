@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'min-h-11 rounded-xl border-slate-300 bg-white text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-sky-400 focus:ring-sky-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500'
    ]) }}
>
