@php
    $isEdit = isset($patient);
    $activeValue = old('is_active', $patient->is_active ?? true);
@endphp

<div class="space-y-6">
    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="full_name" value="Ime i prezime" />
            <x-text-input id="full_name" name="full_name" type="text" class="mt-1 block w-full" :value="old('full_name', $patient->full_name ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
        </div>
        <div>
            <x-input-label for="date_of_birth" value="Datum rodjenja" />
            <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', $patient?->date_of_birth?->toDateString())" />
            <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
        </div>
        <div>
            <x-input-label for="phone" value="Telefon" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $patient->phone ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $patient->email ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Napomena" />
        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $patient->notes ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>

    <div class="flex items-center gap-3">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) $activeValue) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <label for="is_active" class="text-sm text-gray-700">Pacijent aktivan</label>
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isEdit ? 'Sacuvaj izmjene' : 'Kreiraj pacijenta' }}</x-primary-button>
        <a href="{{ route('patients.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Nazad na listu
        </a>
    </div>
</div>
