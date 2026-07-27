<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-slate-900">Child Laborer Spreadsheet Import</h2></x-slot>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{ session('success') }}</div>@endif
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Upload official workbook</h3>
            <p class="mt-1 text-sm text-slate-600">Expected layout: one worksheet, 129 columns, one child laborer per row.</p>
            <form method="POST" action="{{ route('admin.child-labor-imports.upload') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <input type="file" name="spreadsheet" accept=".xlsx,.xls" required class="block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                @error('spreadsheet')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <button class="rounded-xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Analyze spreadsheet</button>
            </form>
        </section>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4"><h3 class="font-semibold text-slate-900">Import history</h3></div>
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-6 py-3">File</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Rows</th><th class="px-6 py-3">Uploaded by</th><th class="px-6 py-3">Date</th></tr></thead>
                <tbody class="divide-y divide-slate-100">@forelse($batches as $batch)<tr><td class="px-6 py-4"><a class="font-medium text-sky-700 hover:underline" href="{{ route('admin.child-labor-imports.show',$batch) }}">{{ $batch->original_filename }}</a></td><td class="px-6 py-4">{{ ucfirst($batch->status) }}</td><td class="px-6 py-4">{{ $batch->total_rows }}</td><td class="px-6 py-4">{{ $batch->uploader->name ?? 'Unknown' }}</td><td class="px-6 py-4">{{ $batch->created_at->format('M d, Y h:i A') }}</td></tr>@empty<tr><td colspan="5" class="px-6 py-10 text-center text-slate-500">No imports yet.</td></tr>@endforelse</tbody>
            </table></div><div class="p-4">{{ $batches->links() }}</div>
        </section>
    </div>
</x-app-layout>
