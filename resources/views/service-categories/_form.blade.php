@php
    $isEdit = isset($serviceCategory);
    $activeValue = old('is_active', $serviceCategory->is_active ?? true);
@endphp

<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Naziv kategorije usluge" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-1 block w-full"
            :value="old('name', $serviceCategory->name ?? '')"
            required
            autofocus
        />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="sort_order" value="Redoslijed prikaza" />
        <x-text-input
            id="sort_order"
            name="sort_order"
            type="number"
            min="0"
            class="mt-1 block w-full"
            :value="old('sort_order', $serviceCategory->sort_order ?? 0)"
        />
        <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
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
        <label for="is_active" class="text-sm text-gray-700">Aktivna kategorija</label>
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isEdit ? 'Sacuvaj izmjene' : 'Dodaj kategoriju' }}</x-primary-button>
        <a href="{{ route('service-categories.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Nazad na listu
        </a>
    </div>
</div>
