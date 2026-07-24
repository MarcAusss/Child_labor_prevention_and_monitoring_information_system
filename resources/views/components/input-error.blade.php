@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'space-y-1 text-sm font-semibold text-red-700']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-red-500"></span>
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif
