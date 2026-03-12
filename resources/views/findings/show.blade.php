<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalji nalaza
            </h2>
            <a href="{{ route('findings.edit', $finding) }}" class="inline-flex items-center rounded-md border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                Uredi
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <dl class="divide-y divide-gray-100">
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Naziv</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $finding->name }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Kategorija</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $finding->category?->name ?: '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Povezana usluga</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $finding->service?->name ?: '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Jedinicna cijena</dt>
                        <dd class="col-span-2 text-sm text-gray-900">
                            {{ $finding->unit_price !== null ? number_format((float) $finding->unit_price, 2, ',', '.').' KM' : '-' }}
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Status</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $finding->is_active ? 'Aktivan' : 'Neaktivan' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Napomena</dt>
                        <dd class="col-span-2 whitespace-pre-wrap text-sm text-gray-900">{{ $finding->notes ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
