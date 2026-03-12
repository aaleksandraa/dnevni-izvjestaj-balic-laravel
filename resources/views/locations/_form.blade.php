@php
    $isEdit = isset($location);
    $activeValue = old('is_active', $location->is_active ?? true);
@endphp

<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Naziv lokacije" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-1 block w-full"
            :value="old('name', $location->name ?? '')"
            required
            autofocus
        />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="address" value="Adresa" />
            <x-text-input
                id="address"
                name="address"
                type="text"
                class="mt-1 block w-full"
                :value="old('address', $location->address ?? '')"
            />
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div>
            <x-input-label for="city" value="Grad" />
            <x-text-input
                id="city"
                name="city"
                type="text"
                class="mt-1 block w-full"
                :value="old('city', $location->city ?? '')"
            />
            <x-input-error class="mt-2" :messages="$errors->get('city')" />
        </div>

        <div>
            <x-input-label for="phone" value="Telefon" />
            <x-text-input
                id="phone"
                name="phone"
                type="text"
                class="mt-1 block w-full"
                :value="old('phone', $location->phone ?? '')"
            />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full"
                :value="old('email', $location->email ?? '')"
            />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Napomena" />
        <textarea
            id="notes"
            name="notes"
            rows="4"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >{{ old('notes', $location->notes ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
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
        <label for="is_active" class="text-sm text-gray-700">Aktivna lokacija</label>
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isEdit ? 'Sacuvaj izmjene' : 'Dodaj lokaciju' }}</x-primary-button>
        <a href="{{ route('locations.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Nazad na listu
        </a>
    </div>
</div>
