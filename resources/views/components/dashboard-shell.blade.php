@props(['title', 'subtitle' => null, 'badge' => null])

<x-workspace-shell :title="$title" :subtitle="$subtitle">
    <section class="clpmis-page-header">
        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="clpmis-eyebrow">CLPMIS Workspace</p>
                <h1 class="clpmis-title">{{ $title }}</h1>

                @if ($subtitle)
                    <p class="clpmis-subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            @if ($badge)
                <span class="clpmis-badge">{{ $badge }}</span>
            @endif
        </div>
    </section>

    <div class="clpmis-content">
        {{ $slot }}
    </div>
</x-workspace-shell>
