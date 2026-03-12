@php
    $isEdit = isset($staffMember);
    $activeValue = old('is_active', $staffMember->is_active ?? true);
    $selectedLocationIds = collect(old('location_ids', $staffMember?->locations?->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<div class="space-y-6">
    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="full_name" value="Ime i prezime" />
            <x-text-input id="full_name" name="full_name" type="text" class="mt-1 block w-full" :value="old('full_name', $staffMember->full_name ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
        </div>

        <div>
            <x-input-label for="role_type" value="Uloga" />
            <select id="role_type" name="role_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Odaberi ulogu</option>
                @foreach ($roleOptions as $option)
                    <option value="{{ $option }}" @selected(old('role_type', $staffMember->role_type ?? '') === $option)>
                        {{ str_replace('_', ' ', ucfirst($option)) }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('role_type')" />
        </div>

        <div>
            <x-input-label for="title" value="Titula" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $staffMember->title ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('title')" />
        </div>

        <div>
            <x-input-label for="specialty" value="Specijalizacija" />
            <x-text-input id="specialty" name="specialty" type="text" class="mt-1 block w-full" :value="old('specialty', $staffMember->specialty ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('specialty')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $staffMember->email ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="phone" value="Telefon" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $staffMember->phone ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="internal_code" value="Interna sifra" />
            <x-text-input id="internal_code" name="internal_code" type="text" class="mt-1 block w-full" :value="old('internal_code', $staffMember->internal_code ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('internal_code')" />
        </div>
    </div>

    <div>
        <x-input-label value="Lokacije" />
        <div class="mt-2 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($locations as $location)
                <label class="inline-flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700">
                    <input
                        type="checkbox"
                        name="location_ids[]"
                        value="{{ $location->id }}"
                        @checked(in_array($location->id, $selectedLocationIds, true))
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    >
                    <span>{{ $location->name }}</span>
                </label>
            @empty
                <p class="text-sm text-gray-500">Nema dostupnih lokacija.</p>
            @endforelse
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('location_ids')" />
        <x-input-error class="mt-2" :messages="$errors->get('location_ids.*')" />
    </div>

    <div class="flex items-center gap-3">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) $activeValue) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <label for="is_active" class="text-sm text-gray-700">Aktivan clan tima</label>
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isEdit ? 'Sacuvaj izmjene' : 'Dodaj clana tima' }}</x-primary-button>
        <a href="{{ route('staff-members.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Nazad na listu
        </a>
    </div>
</div>
