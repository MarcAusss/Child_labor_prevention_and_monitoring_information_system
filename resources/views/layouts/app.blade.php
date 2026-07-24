<x-workspace-shell
    title="{{ config('app.name', 'CLPMIS') }}"
    subtitle="Secure child labor prevention and monitoring workspace"
>
    @isset($header)
        <section class="clpmis-page-header">
            <div class="relative">
                {{ $header }}
            </div>
        </section>
    @endisset

    <div class="clpmis-content">
        {{ $slot }}
    </div>
</x-workspace-shell>
