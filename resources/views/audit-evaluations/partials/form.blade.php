@php
    $record = $auditEvaluation ?? null;
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label
            for="evaluation_date"
            class="block text-sm font-semibold text-slate-700"
        >
            Evaluation Date
            <span class="text-red-600">*</span>
        </label>

        <input
            id="evaluation_date"
            name="evaluation_date"
            type="date"
            value="{{ old(
                'evaluation_date',
                $record?->evaluation_date
                    ?->format('Y-m-d')
                    ?? now()->format('Y-m-d')
            ) }}"
            max="{{ now()->format('Y-m-d') }}"
            required
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >

        @error('evaluation_date')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="status"
            class="block text-sm font-semibold text-slate-700"
        >
            Evaluation Status
            <span class="text-red-600">*</span>
        </label>

        <select
            id="status"
            name="status"
            required
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >
            @foreach (
                $evaluationStatuses as $status
            )
                <option
                    value="{{ $status }}"
                    @selected(
                        old(
                            'status',
                            $record?->status
                                ?? 'Draft'
                        ) === $status
                    )
                >
                    {{ $status }}
                </option>
            @endforeach
        </select>

        @error('status')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label
            for="findings"
            class="block text-sm font-semibold text-slate-700"
        >
            Audit Findings
        </label>

        <textarea
            id="findings"
            name="findings"
            rows="8"
            placeholder="Record verified observations, compliance issues, risks, missing information, intervention gaps, and other findings."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'findings',
            $record?->findings
        ) }}</textarea>

        @error('findings')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label
            for="recommendations"
            class="block text-sm font-semibold text-slate-700"
        >
            Recommendations
        </label>

        <textarea
            id="recommendations"
            name="recommendations"
            rows="8"
            placeholder="Record required corrective actions, referrals, follow-up activities, monitoring requirements, and recommended interventions."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'recommendations',
            $record?->recommendations
        ) }}</textarea>

        @error('recommendations')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div
        class="md:col-span-2 rounded-2xl
               border border-amber-200 bg-amber-50 p-4"
    >
        <p class="font-bold text-amber-900">
            Finalization Notice
        </p>

        <p class="mt-1 text-sm leading-6 text-amber-700">
            Finalizing the evaluation will automatically complete
            the audit schedule. A finalized evaluation cannot be
            edited.
        </p>
    </div>
</div>