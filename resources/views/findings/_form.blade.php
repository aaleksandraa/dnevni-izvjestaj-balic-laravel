@php
    $isEdit = isset($finding);
    $activeValue = old('is_active', $finding->is_active ?? true);
@endphp

<div class="space-y-6">
    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="finding_category_id" value="Kategorija nalaza" />
            <select id="finding_category_id" name="finding_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Bez kategorije</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('finding_category_id', $finding->finding_category_id ?? 0) === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('finding_category_id')" />
        </div>

        <div>
            <x-input-label for="service_id" value="Povezana usluga" />
            <select id="service_id" name="service_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Bez povezane usluge</option>
                @foreach ($services as $serviceOption)
                    <option value="{{ $serviceOption->id }}" @selected((int) old('service_id', $finding->service_id ?? 0) === $serviceOption->id)>
                        {{ $serviceOption->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('service_id')" />
        </div>

        <div>
            <x-input-label for="name" value="Naziv nalaza" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $finding->name ?? '')" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="unit_price" value="Jedinicna cijena (KM)" />
            <x-text-input id="unit_price" name="unit_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('unit_price', isset($finding) && $finding->unit_price !== null ? (float) $finding->unit_price : '')" />
            <x-input-error class="mt-2" :messages="$errors->get('unit_price')" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Napomena" />
        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $finding->notes ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>

    <div class="flex items-center gap-3">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) $activeValue) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <label for="is_active" class="text-sm text-gray-700">Aktivan nalaz</label>
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isEdit ? 'Sacuvaj izmjene' : 'Dodaj nalaz' }}</x-primary-button>
        <a href="{{ route('findings.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Nazad na listu
        </a>
    </div>
</div>
