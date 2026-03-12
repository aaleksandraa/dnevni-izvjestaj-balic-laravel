<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalji korisnika
            </h2>
            <a href="{{ route('users.edit', $managedUser) }}" class="inline-flex items-center rounded-md border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
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
                        <dd class="col-span-2 text-sm text-gray-900">{{ $managedUser->name }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Email</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $managedUser->email }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Telefon</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $managedUser->phone ?: '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Uloga</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ str_replace('_', ' ', ucfirst($managedUser->role)) }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Dozvole</dt>
                        <dd class="col-span-2 text-sm text-gray-900">
                            <p>Podnosenje izvjestaja: {{ $managedUser->can_submit_report ? 'DA' : 'NE' }}</p>
                            <p>Mijenjanje podnosioca: {{ $managedUser->can_change_submitter ? 'DA' : 'NE' }}</p>
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Lokacije</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $managedUser->locations->pluck('name')->join(', ') ?: '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-6 py-4">
                        <dt class="text-sm font-semibold text-gray-600">Status</dt>
                        <dd class="col-span-2 text-sm text-gray-900">{{ $managedUser->is_active ? 'Aktivan' : 'Neaktivan' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
