<x-workspace-shell
    title="Backup and Recovery"
    subtitle="Create, verify, download, retain, and securely restore CLPMIS data."
>
    @php
        $formatBytes = function (
            int $bytes
        ): string {
            if ($bytes <= 0) {
                return '0 B';
            }

            $units = [
                'B',
                'KB',
                'MB',
                'GB',
                'TB',
            ];

            $power = min(
                (int) floor(
                    log($bytes, 1024)
                ),
                count($units) - 1
            );

            return number_format(
                $bytes / (1024 ** $power),
                $power === 0 ? 0 : 2
            ).' '.$units[$power];
        };

        $statusClasses = [
            'completed' =>
                'border-emerald-200 bg-emerald-50 text-emerald-700',

            'running' =>
                'border-sky-200 bg-sky-50 text-sky-700',

            'pending' =>
                'border-slate-200 bg-slate-50 text-slate-700',

            'failed' =>
                'border-red-200 bg-red-50 text-red-700',

            'pruned' =>
                'border-amber-200 bg-amber-50 text-amber-700',
        ];
    @endphp

    @if (session('success'))
        <div
            class="rounded-2xl border
                   border-emerald-200
                   bg-emerald-50 p-4
                   text-sm font-bold
                   text-emerald-700"
        >
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('backup'))
        <div
            class="rounded-2xl border
                   border-red-200 bg-red-50
                   p-4 text-sm font-bold
                   text-red-700"
        >
            {{ $errors->first('backup') }}
        </div>
    @endif

    <section
        class="relative overflow-hidden
               rounded-[28px]
               bg-gradient-to-br
               from-slate-950 via-slate-900
               to-sky-950 p-6 text-white
               shadow-2xl sm:p-8"
    >
        <div
            class="absolute -right-20 -top-20
                   h-64 w-64 rounded-full
                   bg-sky-400/10 blur-3xl"
        ></div>

        <div
            class="relative flex flex-col gap-6
                   xl:flex-row xl:items-end
                   xl:justify-between"
        >
            <div class="max-w-3xl">
                <p
                    class="text-xs font-black
                           uppercase tracking-[0.22em]
                           text-sky-300"
                >
                    Protected System Data
                </p>

                <h2
                    class="mt-3 text-3xl font-black
                           tracking-tight"
                >
                    Complete database and
                    private-file backups
                </h2>

                <p
                    class="mt-3 text-sm leading-7
                           text-slate-300"
                >
                    Each archive contains a MySQL dump,
                    private uploaded documents, profile
                    photographs, and a machine-readable
                    manifest. Completed archives are
                    verified using SHA-256.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'backups.store'
                ) }}"
                onsubmit="return confirm(
                    'Create a complete CLPMIS backup now?'
                )"
            >
                @csrf

                <button
                    type="submit"
                    class="rounded-xl bg-white
                           px-6 py-3 text-sm
                           font-black text-slate-900
                           shadow-lg transition
                           hover:bg-sky-50"
                >
                    Create Backup Now
                </button>
            </form>
        </div>
    </section>

    <section
        class="grid gap-4 sm:grid-cols-2
               xl:grid-cols-4"
    >
        <article
            class="rounded-3xl border
                   border-emerald-200
                   bg-emerald-50 p-5"
        >
            <p
                class="text-xs font-black
                       uppercase tracking-wide
                       text-emerald-600"
            >
                Completed Backups
            </p>

            <p
                class="mt-3 text-4xl font-black
                       text-emerald-700"
            >
                {{ number_format(
                    $summary['completed']
                ) }}
            </p>
        </article>

        <article
            class="rounded-3xl border
                   border-red-200 bg-red-50
                   p-5"
        >
            <p
                class="text-xs font-black
                       uppercase tracking-wide
                       text-red-600"
            >
                Failed Runs
            </p>

            <p
                class="mt-3 text-4xl font-black
                       text-red-700"
            >
                {{ number_format(
                    $summary['failed']
                ) }}
            </p>
        </article>

        <article
            class="rounded-3xl border
                   border-sky-200 bg-sky-50
                   p-5"
        >
            <p
                class="text-xs font-black
                       uppercase tracking-wide
                       text-sky-600"
            >
                Stored Size
            </p>

            <p
                class="mt-3 text-3xl font-black
                       text-sky-700"
            >
                {{ $formatBytes(
                    $summary['stored_size']
                ) }}
            </p>
        </article>

        <article
            class="rounded-3xl border
                   border-violet-200
                   bg-violet-50 p-5"
        >
            <p
                class="text-xs font-black
                       uppercase tracking-wide
                       text-violet-600"
            >
                Last Completed
            </p>

            <p
                class="mt-3 text-lg font-black
                       text-violet-700"
            >
                {{ $summary['last_completed']
                    ?->completed_at
                    ?->format(
                        'M d, Y h:i A'
                    )
                    ?? 'No backup yet' }}
            </p>
        </article>
    </section>

    <section
        class="overflow-hidden rounded-3xl
               border border-slate-200
               bg-white shadow-sm"
    >
        <div
            class="border-b border-slate-200
                   px-6 py-5"
        >
            <h2
                class="text-xl font-black
                       text-slate-900"
            >
                Backup History
            </h2>

            <p
                class="mt-1 text-sm
                       text-slate-500"
            >
                Restore is intentionally available
                only through the command line.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table
                class="min-w-[1200px] w-full
                       divide-y divide-slate-200"
            >
                <thead class="bg-slate-50">
                    <tr>
                        @foreach ([
                            'Backup',
                            'Created By',
                            'Status',
                            'Size',
                            'Verification',
                            'Created',
                            'Actions',
                        ] as $heading)
                            <th
                                class="px-5 py-4
                                       text-left text-xs
                                       font-black uppercase
                                       tracking-wide
                                       text-slate-500"
                            >
                                {{ $heading }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody
                    class="divide-y
                           divide-slate-100"
                >
                    @forelse (
                        $backups as $backup
                    )
                        <tr
                            class="hover:bg-slate-50"
                        >
                            <td
                                class="px-5 py-4
                                       align-top"
                            >
                                <p
                                    class="font-black
                                           text-slate-800"
                                >
                                    {{ $backup->file_name
                                        ?: 'Backup run #'
                                            .$backup->id }}
                                </p>

                                @if (
                                    $backup
                                        ->checksum_sha256
                                )
                                    <p
                                        class="mt-1 max-w-sm
                                               truncate font-mono
                                               text-[10px]
                                               text-slate-400"
                                        title="{{ $backup
                                            ->checksum_sha256 }}"
                                    >
                                        SHA-256:
                                        {{ $backup
                                            ->checksum_sha256 }}
                                    </p>
                                @endif

                                @if (
                                    $backup
                                        ->error_message
                                )
                                    <p
                                        class="mt-2 max-w-md
                                               text-xs leading-5
                                               text-red-600"
                                    >
                                        {{ $backup
                                            ->error_message }}
                                    </p>
                                @endif
                            </td>

                            <td
                                class="px-5 py-4
                                       align-top text-sm
                                       text-slate-600"
                            >
                                {{ $backup
                                    ->creator?->name
                                    ?? 'System scheduler' }}
                            </td>

                            <td
                                class="px-5 py-4
                                       align-top"
                            >
                                <span
                                    class="inline-flex
                                           rounded-full border
                                           px-3 py-1
                                           text-[10px]
                                           font-black uppercase
                                           {{ $statusClasses[
                                                $backup->status
                                            ] ?? $statusClasses[
                                                'pending'
                                            ] }}"
                                >
                                    {{ $backup->status }}
                                </span>
                            </td>

                            <td
                                class="px-5 py-4
                                       align-top text-sm
                                       font-bold
                                       text-slate-700"
                            >
                                {{ $backup
                                    ->formatted_size }}
                            </td>

                            <td
                                class="px-5 py-4
                                       align-top text-sm
                                       text-slate-600"
                            >
                                @if (
                                    $backup
                                        ->verified_at
                                )
                                    <span
                                        class="font-bold
                                               text-emerald-700"
                                    >
                                        Verified
                                    </span>

                                    <p
                                        class="mt-1 text-xs
                                               text-slate-400"
                                    >
                                        {{ $backup
                                            ->verified_at
                                            ->format(
                                                'M d, Y h:i A'
                                            ) }}
                                    </p>
                                @else
                                    Not verified
                                @endif
                            </td>

                            <td
                                class="px-5 py-4
                                       align-top text-sm
                                       text-slate-600"
                            >
                                {{ $backup
                                    ->created_at
                                    ->format(
                                        'M d, Y h:i A'
                                    ) }}
                            </td>

                            <td
                                class="px-5 py-4
                                       align-top"
                            >
                                <div
                                    class="flex flex-wrap
                                           gap-2"
                                >
                                    @if (
                                        $backup
                                            ->isDownloadable()
                                    )
                                        <a
                                            href="{{ route(
                                                'backups.download',
                                                $backup
                                            ) }}"
                                            class="rounded-xl
                                                   bg-sky-600
                                                   px-3 py-2
                                                   text-xs
                                                   font-black
                                                   text-white"
                                        >
                                            Download
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'backups.verify',
                                                $backup
                                            ) }}"
                                        >
                                            @csrf
                                            @method('PUT')

                                            <button
                                                type="submit"
                                                class="rounded-xl
                                                       border
                                                       border-emerald-300
                                                       px-3 py-2
                                                       text-xs
                                                       font-black
                                                       text-emerald-700"
                                            >
                                                Verify
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'backups.destroy',
                                                $backup
                                            ) }}"
                                            onsubmit="return confirm(
                                                'Delete this stored backup archive?'
                                            )"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="rounded-xl
                                                       border
                                                       border-red-300
                                                       px-3 py-2
                                                       text-xs
                                                       font-black
                                                       text-red-700"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    @else
                                        <span
                                            class="text-xs
                                                   text-slate-400"
                                        >
                                            No stored archive
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="7"
                                class="px-6 py-16
                                       text-center
                                       text-sm
                                       text-slate-500"
                            >
                                No backup has been created.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($backups->hasPages())
            <div
                class="border-t
                       border-slate-200 p-5"
            >
                {{ $backups->links() }}
            </div>
        @endif
    </section>

    <section
        class="grid gap-6 xl:grid-cols-2"
    >
        <article
            class="rounded-3xl border
                   border-amber-200
                   bg-amber-50 p-6"
        >
            <h2
                class="text-lg font-black
                       text-amber-900"
            >
                Database restoration
            </h2>

            <p
                class="mt-2 text-sm
                       leading-7 text-amber-800"
            >
                Restoration is restricted to the
                server command line to prevent an
                accidental web-triggered database
                replacement.
            </p>

            <pre
                class="mt-4 overflow-x-auto
                       rounded-2xl bg-slate-950
                       p-4 text-sm
                       text-amber-200"
            ><code>php artisan clpmis:backup:restore BACKUP_ID</code></pre>
        </article>

        <article
            class="rounded-3xl border
                   border-sky-200 bg-sky-50
                   p-6"
        >
            <h2
                class="text-lg font-black
                       text-sky-900"
            >
                Automatic backups
            </h2>

            <p
                class="mt-2 text-sm
                       leading-7 text-sky-800"
            >
                Enable scheduling only after the
                server runs Laravel's scheduler
                every minute.
            </p>

            <pre
                class="mt-4 overflow-x-auto
                       rounded-2xl bg-slate-950
                       p-4 text-sm
                       text-sky-200"
            ><code>php artisan schedule:work</code></pre>
        </article>
    </section>
</x-workspace-shell>
