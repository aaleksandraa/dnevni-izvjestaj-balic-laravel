@php
    $isEdit = isset($managedUser);
    $activeValue = old('is_active', $managedUser->is_active ?? true);
    $canSubmitValue = old('can_submit_report', $managedUser->can_submit_report ?? true);
    $canChangeSubmitterValue = old('can_change_submitter', $managedUser->can_change_submitter ?? false);
    $selectedLocationIds = collect(old('location_ids', $managedUser?->locations?->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<div class="space-y-6">
    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="name" value="Ime i prezime" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $managedUser->name ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $managedUser->email ?? '')" required />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="role" value="Uloga" />
            <select id="role" name="role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Odaberi ulogu</option>
                @foreach ($roles as $roleOption)
                    <option value="{{ $roleOption }}" @selected(old('role', $managedUser->role ?? '') === $roleOption)>
                        {{ str_replace('_', ' ', ucfirst($roleOption)) }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('role')" />
        </div>

        <div>
            <x-input-label for="phone" value="Telefon" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $managedUser->phone ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="password" :value="$isEdit ? 'Nova lozinka (opcionalno)' : 'Lozinka'" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="!$isEdit" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Potvrda lozinke" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="!$isEdit" />
            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
        </div>
    </div>

    <div>
        <x-input-label value="Pristup lokacijama" />
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

    <div class="grid gap-4 md:grid-cols-3">
        <label class="inline-flex items-center gap-3 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700">
            <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) $activeValue) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span>Korisnik aktivan</span>
        </label>
        <label class="inline-flex items-center gap-3 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700">
            <input id="can_submit_report" name="can_submit_report" type="checkbox" value="1" @checked((bool) $canSubmitValue) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span>Moze podnositi izvjestaj</span>
        </label>
        <label class="inline-flex items-center gap-3 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700">
            <input id="can_change_submitter" name="can_change_submitter" type="checkbox" value="1" @checked((bool) $canChangeSubmitterValue) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span>Moze mijenjati podnosioca</span>
        </label>
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isEdit ? 'Sacuvaj izmjene' : 'Kreiraj korisnika' }}</x-primary-button>
        <a href="{{ route('users.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Nazad na listu
        </a>
    </div>
</div>
