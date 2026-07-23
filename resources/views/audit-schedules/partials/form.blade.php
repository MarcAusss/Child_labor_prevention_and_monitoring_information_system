@php
    $record = $auditSchedule ?? null;
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label
            for="assigned_to"
            class="block text-sm font-semibold text-slate-700"
        >
            Assigned Admin
            <span class="text-red-600">*</span>
        </label>

        <select
            id="assigned_to"
            name="assigned_to"
            required
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >
            <option value="">
                Select Admin or Super Admin
            </option>

            @foreach (
                $eligibleAdministrators as $administrator
            )
                <option
                    value="{{ $administrator->id }}"
                    @selected(
                        (int) old(
                            'assigned_to',
                            $record?->assigned_to
                        ) === $administrator->id
                    )
                >
                    {{ $administrator->name }}
                    — {{ $administrator->email }}
                </option>
            @endforeach
        </select>

        @error('assigned_to')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="scheduled_at"
            class="block text-sm font-semibold text-slate-700"
        >
            Scheduled Date and Time
            <span class="text-red-600">*</span>
        </label>

        <input
            id="scheduled_at"
            name="scheduled_at"
            type="datetime-local"
            value="{{ old(
                'scheduled_at',
                $record?->scheduled_at
                    ?->format('Y-m-d\TH:i')
            ) }}"
            required
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >

        @error('scheduled_at')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label
            for="location"
            class="block text-sm font-semibold text-slate-700"
        >
            Audit Location
        </label>

        <input
            id="location"
            name="location"
            type="text"
            value="{{ old(
                'location',
                $record?->location
            ) }}"
            placeholder="Office, residence, workplace, barangay, or meeting location"
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >

        @error('location')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    @if ($record)
        <div class="md:col-span-2">
            <label
                for="status"
                class="block text-sm font-semibold text-slate-700"
            >
                Schedule Status
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
                @foreach ($statuses as $status)
                    <option
                        value="{{ $status }}"
                        @selected(
                            old(
                                'status',
                                $record->status
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
    @else
        <input
            type="hidden"
            name="status"
            value="Scheduled"
        >
    @endif

    <div class="md:col-span-2">
        <label
            for="remarks"
            class="block text-sm font-semibold text-slate-700"
        >
            Remarks
        </label>

        <textarea
            id="remarks"
            name="remarks"
            rows="5"
            placeholder="Audit scope, preparation instructions, cancellation reason, or other notes."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'remarks',
            $record?->remarks
        ) }}</textarea>

        <p class="mt-2 text-xs text-slate-500">
            Remarks are required when cancelling the schedule.
        </p>

        @error('remarks')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>