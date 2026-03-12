<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalji kategorije nalaza
            </h2>
            <a href="{{ route('finding-categories.edit', $findingCategory) }}" class="inline-flex items-center rounded-md border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                Uredi
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <dl class="divide-y divide-gray-100">
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Naziv</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $findingCategory->name }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Status</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $findingCategory->is_active ? 'Aktivna' : 'Neaktivna' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Redoslijed</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $findingCategory->sort_order }}</dd>
                    </div>
                </dl>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Nalazi u kategoriji</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($findingCategory->findings as $finding)
                        <div class="flex items-center justify-between px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $finding->name }}</p>
                                <p class="text-xs text-gray-500">{{ $finding->service?->name ?: 'Bez povezane usluge' }}</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-700">
                                {{ $finding->unit_price !== null ? number_format((float) $finding->unit_price, 2, ',', '.').' KM' : '-' }}
                            </span>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-sm text-gray-500">
                            Nema nalaza u ovoj kategoriji.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
