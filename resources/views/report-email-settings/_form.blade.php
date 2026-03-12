@php
    $isEdit = isset($setting);
    $activeValue = old('is_active', $setting->is_active ?? true);
@endphp

<div class="space-y-6">
    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="report_type" value="Tip izvjestaja" />
            <select id="report_type" name="report_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Odaberi tip</option>
                @foreach ($reportTypes as $reportTypeValue => $reportTypeLabel)
                    <option value="{{ $reportTypeValue }}" @selected(old('report_type', $setting->report_type ?? '') === $reportTypeValue)>
                        {{ $reportTypeLabel }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('report_type')" />
        </div>

        <div>
            <x-input-label for="email" value="Email adresa" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $setting->email ?? '')" required />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
    </div>

    <div class="flex items-center gap-3">
        <input
            id="is_active"
            name="is_active"
            type="checkbox"
            value="1"
            @checked((bool) $activeValue)
            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
        >
        <label for="is_active" class="text-sm text-gray-700">Primaoc je aktivan</label>
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isEdit ? 'Sacuvaj izmjene' : 'Dodaj primaoca' }}</x-primary-button>
        <a href="{{ route('report-email-settings.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Nazad na listu
        </a>
    </div>
</div>
