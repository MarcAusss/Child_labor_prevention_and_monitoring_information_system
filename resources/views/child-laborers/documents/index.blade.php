<x-dashboard-shell
    title="Document Management"
    subtitle="{{ $childLaborer->profile_number }} — {{ $childLaborer->full_name }}"
    badge="{{ $childLaborer->status }}"
>
    @if (session('success'))
        <div
            class="rounded-xl border border-emerald-200
                   bg-emerald-50 p-4 text-sm font-semibold
                   text-emerald-700"
        >
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="rounded-xl border border-red-200
                   bg-red-50 p-5"
        >
            <h2 class="font-bold text-red-800">
                Please correct the following:
            </h2>

            <ul
                class="mt-3 list-inside list-disc
                       space-y-1 text-sm text-red-700"
            >
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div
        class="rounded-2xl border border-amber-200
               bg-amber-50 px-5 py-4 text-sm
               leading-6 text-amber-800"
    >
        Documents are stored in private application storage.
        A document can only be downloaded through an authorized
        CLPMIS account.
    </div>

    @can('uploadDocuments', $childLaborer)
        <section
            class="rounded-3xl border border-slate-200
                   bg-white p-6 shadow-sm sm:p-8"
        >
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-xl font-bold text-slate-800">
                    Upload Document
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Supported files: PDF, JPG, JPEG, PNG, DOC,
                    DOCX, XLS, and XLSX. Maximum size is 10 MB.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'child-laborers.documents.store',
                    $childLaborer
                ) }}"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label
                            for="document_type"
                            class="block text-sm font-semibold
                                   text-slate-700"
                        >
                            Document Type
                            <span class="text-red-600">*</span>
                        </label>

                        <select
                            id="document_type"
                            name="document_type"
                            required
                            class="mt-2 block w-full rounded-xl
                                   border-slate-300
                                   focus:border-sky-500
                                   focus:ring-sky-500"
                        >
                            <option value="">
                                Select document type
                            </option>

                            @foreach (
                                $documentTypes as $documentType
                            )
                                <option
                                    value="{{ $documentType }}"
                                    @selected(
                                        old('document_type')
                                            === $documentType
                                    )
                                >
                                    {{ $documentType }}
                                </option>
                            @endforeach
                        </select>

                        @error('document_type')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="document"
                            class="block text-sm font-semibold
                                   text-slate-700"
                        >
                            Select File
                            <span class="text-red-600">*</span>
                        </label>

                        <input
                            id="document"
                            name="document"
                            type="file"
                            required
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                            class="mt-2 block w-full rounded-xl
                                   border border-slate-300
                                   bg-white p-2.5 text-sm"
                        >

                        @error('document')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label
                            for="description"
                            class="block text-sm font-semibold
                                   text-slate-700"
                        >
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="Describe the document and its relevance to the child laborer profile."
                            class="mt-2 block w-full rounded-xl
                                   border-slate-300
                                   focus:border-sky-500
                                   focus:ring-sky-500"
                        >{{ old('description') }}</textarea>

                        @error('description')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <input
                            type="hidden"
                            name="is_confidential"
                            value="0"
                        >

                        <label
                            class="flex cursor-pointer items-start
                                   gap-3 rounded-2xl border
                                   border-amber-200 bg-amber-50
                                   p-4"
                        >
                            <input
                                name="is_confidential"
                                type="checkbox"
                                value="1"
                                @checked(
                                    old(
                                        'is_confidential',
                                        false
                                    )
                                )
                                class="mt-1 rounded
                                       border-amber-300
                                       text-amber-600
                                       focus:ring-amber-500"
                            >

                            <span>
                                <span
                                    class="block font-bold
                                           text-amber-900"
                                >
                                    Confidential Document
                                </span>

                                <span
                                    class="mt-1 block text-sm
                                           text-amber-700"
                                >
                                    Viewer accounts will not see
                                    or download this document.
                                </span>
                            </span>
                        </label>
                    </div>
                </div>

                <div
                    class="mt-8 flex justify-end
                           border-t border-slate-200 pt-6"
                >
                    <button
                        type="submit"
                        class="rounded-xl bg-sky-600
                               px-6 py-3 text-sm font-bold
                               text-white hover:bg-sky-700"
                    >
                        Upload Document
                    </button>
                </div>
            </form>
        </section>
    @endcan

    {{-- Filters --}}
    <section
        class="rounded-3xl border border-slate-200
               bg-white p-5 shadow-sm"
    >
        <form
            method="GET"
            class="grid gap-4
                   md:grid-cols-[1fr_1fr_auto]"
        >
            <div>
                <label
                    for="search"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Search Documents
                </label>

                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ $search }}"
                    placeholder="File name or description"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300
                           focus:border-sky-500
                           focus:ring-sky-500"
                >
            </div>

            <div>
                <label
                    for="type"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Document Type
                </label>

                <select
                    id="type"
                    name="type"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300
                           focus:border-sky-500
                           focus:ring-sky-500"
                >
                    <option value="">
                        All document types
                    </option>

                    @foreach (
                        $documentTypes as $documentType
                    )
                        <option
                            value="{{ $documentType }}"
                            @selected(
                                $selectedType
                                    === $documentType
                            )
                        >
                            {{ $documentType }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button
                    type="submit"
                    class="rounded-xl bg-sky-600
                           px-5 py-3 text-sm font-bold
                           text-white"
                >
                    Filter
                </button>

                <a
                    href="{{ route(
                        'child-laborers.documents.index',
                        $childLaborer
                    ) }}"
                    class="rounded-xl border border-slate-300
                           px-5 py-3 text-sm font-bold
                           text-slate-600"
                >
                    Reset
                </a>
            </div>
        </form>
    </section>

    {{-- Document list --}}
    <section
        class="overflow-hidden rounded-3xl
               border border-slate-200 bg-white
               shadow-sm"
    >
        <div
            class="flex flex-col gap-4 border-b
                   border-slate-200 p-6 sm:flex-row
                   sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    Profile Documents
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $documents->total() }}
                    active document(s)
                </p>
            </div>

            <a
                href="{{ route(
                    'child-laborers.show',
                    $childLaborer
                ) }}"
                class="rounded-xl border border-slate-300
                       px-4 py-2 text-center text-sm
                       font-bold text-slate-600"
            >
                Back to Profile
            </a>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse ($documents as $document)
                <article class="p-6">
                    <div
                        class="flex flex-col gap-5
                               lg:flex-row lg:items-start
                               lg:justify-between"
                    >
                        <div
                            class="flex min-w-0
                                   items-start gap-4"
                        >
                            <div
                                class="flex h-14 w-14 shrink-0
                                       items-center justify-center
                                       rounded-2xl bg-sky-100
                                       text-xs font-black
                                       text-sky-700"
                            >
                                {{ $document
                                    ->display_extension }}
                            </div>

                            <div class="min-w-0">
                                <div
                                    class="flex flex-wrap
                                           items-center gap-2"
                                >
                                    <h3
                                        class="break-all text-base
                                               font-bold
                                               text-slate-800"
                                    >
                                        {{ $document
                                            ->original_name }}
                                    </h3>

                                    @if (
                                        $document
                                            ->is_confidential
                                    )
                                        <span
                                            class="rounded-full
                                                   bg-amber-100
                                                   px-3 py-1
                                                   text-[10px]
                                                   font-bold
                                                   uppercase
                                                   text-amber-700"
                                        >
                                            Confidential
                                        </span>
                                    @endif
                                </div>

                                <p
                                    class="mt-2 text-sm
                                           font-semibold
                                           text-slate-600"
                                >
                                    {{ $document
                                        ->document_type }}
                                </p>

                                @if ($document->description)
                                    <p
                                        class="mt-2 text-sm
                                               leading-6
                                               text-slate-500"
                                    >
                                        {{ $document
                                            ->description }}
                                    </p>
                                @endif

                                <div
                                    class="mt-3 flex flex-wrap
                                           gap-x-5 gap-y-1
                                           text-xs
                                           text-slate-400"
                                >
                                    <span>
                                        {{ $document
                                            ->formatted_file_size }}
                                    </span>

                                    <span>
                                        Uploaded
                                        {{ $document
                                            ->uploaded_at
                                            ->format(
                                                'F d, Y h:i A'
                                            ) }}
                                    </span>

                                    <span>
                                        By
                                        {{ $document
                                            ->uploader?->name
                                            ?? 'Unknown user' }}
                                    </span>

                                    <span>
                                        {{ number_format(
                                            $document
                                                ->download_count
                                        ) }}
                                        download(s)
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex shrink-0
                                   flex-wrap gap-2"
                        >
                            <a
                                href="{{ route(
                                    'child-laborers.documents.download',
                                    [
                                        $childLaborer,
                                        $document,
                                    ]
                                ) }}"
                                class="rounded-xl bg-sky-600
                                       px-4 py-2.5
                                       text-xs font-bold
                                       text-white
                                       hover:bg-sky-700"
                            >
                                Download
                            </a>

                            @can(
                                'deleteDocument',
                                [
                                    $childLaborer,
                                    $document,
                                ]
                            )
                                <form
                                    method="POST"
                                    action="{{ route(
                                        'child-laborers.documents.destroy',
                                        [
                                            $childLaborer,
                                            $document,
                                        ]
                                    ) }}"
                                    onsubmit="return confirm('Remove this document from the active profile history?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="rounded-xl
                                               bg-red-50 px-4
                                               py-2.5 text-xs
                                               font-bold
                                               text-red-700
                                               hover:bg-red-100"
                                    >
                                        Remove
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </article>
            @empty
                <div
                    class="px-6 py-14 text-center
                           text-sm text-slate-500"
                >
                    No document matches the selected filters.
                </div>
            @endforelse
        </div>

        @if ($documents->hasPages())
            <div class="border-t border-slate-200 p-5">
                {{ $documents->links() }}
            </div>
        @endif
    </section>
</x-dashboard-shell>