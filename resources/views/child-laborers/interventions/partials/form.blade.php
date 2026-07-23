@php
    $record = $intervention ?? null;
@endphp

<div class="grid gap-6 md:grid-cols-2">
    {{-- Intervention type --}}
    <div>
        <label
            for="intervention_type"
            class="block text-sm font-semibold text-slate-700"
        >
            Intervention Type
            <span class="text-red-600">*</span>
        </label>

        <select
            id="intervention_type"
            name="intervention_type"
            required
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >
            <option value="">
                Select intervention type
            </option>

            @foreach (
                $interventionTypes
                as $interventionType
            )
                <option
                    value="{{ $interventionType }}"
                    @selected(
                        old(
                            'intervention_type',
                            $record?->intervention_type
                        ) === $interventionType
                    )
                >
                    {{ $interventionType }}
                </option>
            @endforeach
        </select>

        @error('intervention_type')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Status --}}
    <div>
        <label
            for="status"
            class="block text-sm font-semibold text-slate-700"
        >
            Status
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
                            $record?->status
                                ?? 'Pending'
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

    {{-- Provider --}}
    <div class="md:col-span-2">
        <label
            for="provider"
            class="block text-sm font-semibold text-slate-700"
        >
            Intervention Provider
            <span class="text-red-600">*</span>
        </label>

        <input
            id="provider"
            name="provider"
            type="text"
            value="{{ old(
                'provider',
                $record?->provider
            ) }}"
            placeholder="Government agency, LGU, NGO, school, employer, organization, or individual"
            required
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >

        @error('provider')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Description --}}
    <div class="md:col-span-2">
        <label
            for="description"
            class="block text-sm font-semibold text-slate-700"
        >
            Assistance Description
            <span class="text-red-600">*</span>
        </label>

        <textarea
            id="description"
            name="description"
            rows="5"
            required
            placeholder="Describe the assistance, service, referral, training, benefit, or support provided."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'description',
            $record?->description
        ) }}</textarea>

        @error('description')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Date provided --}}
    <div>
        <label
            for="date_provided"
            class="block text-sm font-semibold text-slate-700"
        >
            Date Provided
        </label>

        <input
            id="date_provided"
            name="date_provided"
            type="date"
            value="{{ old(
                'date_provided',
                $record?->date_provided
                    ?->format('Y-m-d')
            ) }}"
            max="{{ now()->format('Y-m-d') }}"
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >

        <p class="mt-2 text-xs text-slate-500">
            This may remain blank while the intervention is
            Pending.
        </p>

        @error('date_provided')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Date completed --}}
    <div>
        <label
            for="date_completed"
            class="block text-sm font-semibold text-slate-700"
        >
            Date Completed
        </label>

        <input
            id="date_completed"
            name="date_completed"
            type="date"
            value="{{ old(
                'date_completed',
                $record?->date_completed
                    ?->format('Y-m-d')
            ) }}"
            max="{{ now()->format('Y-m-d') }}"
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >

        <p class="mt-2 text-xs text-slate-500">
            Required when the status is Completed.
        </p>

        @error('date_completed')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Monetary value --}}
    <div class="md:col-span-2">
        <label
            for="amount"
            class="block text-sm font-semibold text-slate-700"
        >
            Monetary or Estimated Assistance Value
        </label>

        <div class="relative mt-2">
            <span
                class="pointer-events-none absolute
                       inset-y-0 left-0 flex items-center
                       pl-4 text-slate-500"
            >
                ₱
            </span>

            <input
                id="amount"
                name="amount"
                type="number"
                min="0"
                step="0.01"
                value="{{ old(
                    'amount',
                    $record?->amount
                ) }}"
                placeholder="0.00"
                class="block w-full rounded-xl
                       border-slate-300 pl-9
                       focus:border-sky-500
                       focus:ring-sky-500"
            >
        </div>

        <p class="mt-2 text-xs text-slate-500">
            This records the assistance value only. The system
            does not process or release payments.
        </p>

        @error('amount')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Remarks --}}
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
            rows="4"
            placeholder="Add follow-up details, outcomes, issues, conditions, or other relevant information."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'remarks',
            $record?->remarks
        ) }}</textarea>

        @error('remarks')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>