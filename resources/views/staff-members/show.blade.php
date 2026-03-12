<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalji clana tima
            </h2>
            <a href="{{ route('staff-members.edit', $staffMember) }}" class="inline-flex items-center rounded-md border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                Uredi
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <dl class="divide-y divide-gray-100">
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Ime i prezime</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $staffMember->full_name }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Uloga</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ str_replace('_', ' ', ucfirst($staffMember->role_type)) }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Titula</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $staffMember->title ?: '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Specijalizacija</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $staffMember->specialty ?: '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Email</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $staffMember->email ?: '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Telefon</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $staffMember->phone ?: '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Interna sifra</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $staffMember->internal_code ?: '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Lokacije</dt>
                        <dd class="col-span-2 text-sm text-gray-900">
                            {{ $staffMember->locations->pluck('name')->join(', ') ?: '-' }}
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Status</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $staffMember->is_active ? 'Aktivan' : 'Neaktivan' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
