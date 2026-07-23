@props(['title', 'subtitle' => null, 'badge' => null])

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold leading-tight text-slate-800">
                    {{ $title }}
                </h2>

                @if ($subtitle)
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            @if ($badge)
                <span
                    class="inline-flex w-fit items-center rounded-full bg-sky-100 px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-sky-700 ring-1 ring-sky-200">
                    {{ $badge }}
                </span>
            @endif
        </div>
    </x-slot>

    <div class="min-h-[calc(100vh-130px)] bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </div>
</x-app-layout>
