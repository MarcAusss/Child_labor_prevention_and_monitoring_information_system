<x-workspace-shell title="Import Child Laborers" subtitle="Validate and import draft child profiles from Excel or CSV.">
    <div class="space-y-6">
        @if(session('success'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700"><ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div><h2 class="text-xl font-black text-slate-900">Upload Spreadsheet</h2><p class="mt-1 text-sm text-slate-500">Imported profiles are always saved as Draft and duplicates are skipped.</p></div>
                <a href="{{ route('child-laborers.import.template') }}" class="rounded-xl border border-sky-300 bg-sky-50 px-4 py-2.5 text-sm font-bold text-sky-700 hover:bg-sky-100">Download Template</a>
            </div>
            <form method="POST" action="{{ route('child-laborers.import.validate') }}" enctype="multipart/form-data" class="mt-6 grid gap-5 lg:grid-cols-3">@csrf
                <div class="lg:col-span-2"><label class="block text-sm font-bold text-slate-700">Excel or CSV file</label><input type="file" name="file" accept=".xlsx,.xls,.csv" required class="mt-2 block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"></div>
                @unless(auth()->user()->isProfilingOfficer())<div><label class="block text-sm font-bold text-slate-700">Assign imported profiles to</label><select name="assigned_to" class="mt-2 block w-full rounded-xl border-slate-300"><option value="">Unassigned</option>@foreach($profilingOfficers as $officer)<option value="{{ $officer->id }}">{{ $officer->name }}</option>@endforeach</select></div>@endunless
                <div class="lg:col-span-3 flex justify-end"><button class="rounded-xl bg-sky-600 px-5 py-3 text-sm font-black text-white hover:bg-sky-700">Upload and Validate</button></div>
            </form>
        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-black text-slate-900">Import History</h2></div>
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">File</th><th class="px-5 py-3">Uploaded by</th><th class="px-5 py-3">Results</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Date</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($imports as $import)<tr><td class="px-5 py-4 font-bold text-slate-800">{{ $import->original_filename }}</td><td class="px-5 py-4">{{ $import->uploader?->name }}</td><td class="px-5 py-4">{{ $import->imported_rows }} imported · {{ $import->duplicate_rows }} duplicates · {{ $import->failed_rows }} failed</td><td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold">{{ $import->status }}</span></td><td class="px-5 py-4">{{ $import->created_at->format('M d, Y g:i A') }}</td><td class="px-5 py-4 text-right">@if($import->failed_rows || $import->duplicate_rows)<a href="{{ route('child-laborers.import.errors',$import) }}" class="font-bold text-red-600">Error CSV</a>@endif</td></tr>@empty<tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No spreadsheet imports yet.</td></tr>@endforelse</tbody></table></div>
            <div class="border-t border-slate-200 px-5 py-4">{{ $imports->links() }}</div>
        </section>
    </div>
</x-workspace-shell>
