<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalji lokacije
            </h2>
            <a
                href="{{ route('locations.edit', $location) }}"
                class="inline-flex items-center rounded-md border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50"
            >
                Uredi lokaciju
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <dl class="divide-y divide-gray-100">
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Naziv</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $location->name }}</dd>
                    </div>

                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Adresa</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $location->address ?: '-' }}</dd>
                    </div>

                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Grad</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $location->city ?: '-' }}</dd>
                    </div>

                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Telefon</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $location->phone ?: '-' }}</dd>
                    </div>

                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Email</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $location->email ?: '-' }}</dd>
                    </div>

                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Status</dt>
                        <dd class="col-span-2">
                            @if ($location->is_active)
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    Aktivna
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                    Neaktivna
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Napomena</dt>
                        <dd class="col-span-2 whitespace-pre-wrap text-sm text-gray-900">{{ $location->notes ?: '-' }}</dd>
                    </div>
                </dl>

                <div class="border-t border-gray-100 px-6 py-4">
                    <a
                        href="{{ route('locations.index') }}"
                        class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Nazad na listu
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
