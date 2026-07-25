<script>
    (() => {
        const stored = localStorage.getItem('clpmis-theme') || 'system';
        const dark = stored === 'dark'
            || (stored === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.dataset.theme = stored;
    })();
</script>
