@php
    $isEdit = isset($service);
    $activeValue = old('is_active', $service->is_active ?? true);
@endphp

<div class="space-y-6">
    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="service_category_id" value="Kategorija usluge" />
            <select
                id="service_category_id"
                name="service_category_id"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">Odaberi kategoriju</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('service_category_id', $service->service_category_id ?? 0) === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('service_category_id')" />
        </div>

        <div>
            <x-input-label for="name" value="Naziv usluge" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $service->name ?? '')" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="code" value="Sifra usluge" />
            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $service->code ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('code')" />
        </div>

        <div>
            <x-input-label for="base_price" value="Osnovna cijena (KM)" />
            <x-text-input id="base_price" name="base_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('base_price', isset($service) ? (float) $service->base_price : '0.00')" required />
            <x-input-error class="mt-2" :messages="$errors->get('base_price')" />
        </div>

        <div>
            <x-input-label for="sort_order" value="Redoslijed prikaza" />
            <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-full" :value="old('sort_order', $service->sort_order ?? 0)" />
            <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
        </div>
    </div>

    <div>
        <x-input-label for="description" value="Opis" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $service->description ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="flex items-center gap-3">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) $activeValue) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <label for="is_active" class="text-sm text-gray-700">Aktivna usluga</label>
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isEdit ? 'Sacuvaj izmjene' : 'Dodaj uslugu' }}</x-primary-button>
        <a href="{{ route('services.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Nazad na listu
        </a>
    </div>
</div>
