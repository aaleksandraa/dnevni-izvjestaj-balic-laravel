<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Pacijenti
            </h2>
            <a href="{{ route('patients.create') }}" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                Novi pacijent
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <form method="GET" action="{{ route('patients.index') }}" class="grid gap-4 md:grid-cols-7">
                    <div class="md:col-span-2">
                        <x-input-label for="q" value="Pretraga pacijenta" />
                        <x-text-input id="q" name="q" type="text" class="mt-1 block w-full" :value="$search" placeholder="Ime i prezime..." />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status pacijenta" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Svi</option>
                            <option value="active" @selected($status === 'active')>Aktivni</option>
                            <option value="inactive" @selected($status === 'inactive')>Neaktivni</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="location_id" value="Lokacija" />
                        <select id="location_id" name="location_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0">Sve lokacije</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected($locationId === $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="date_from" value="Datum od" />
                        <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="$dateFrom" />
                    </div>
                    <div>
                        <x-input-label for="date_to" value="Datum do" />
                        <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="$dateTo" />
                    </div>
                    <div class="flex items-end gap-3">
                        <x-primary-button>Filtriraj</x-primary-button>
                        <a href="{{ route('patients.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reset</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Pacijent</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kontakt</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Broj pregleda</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Ukupno / Naplaceno / Dug</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Akcije</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($patients as $patient)
                                <tr>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $patient->full_name }}</p>
                                        <p class="text-xs text-gray-500">
                                            @if ($patient->date_of_birth)
                                                Rodjen: {{ $patient->date_of_birth->format('d.m.Y') }}
                                            @else
                                                Datum rodjenja: -
                                            @endif
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <p>{{ $patient->phone ?: '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $patient->email ?: '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($patient->is_active)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Aktivan</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-700">Neaktivan</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ (int) ($patient->exams_count ?? 0) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <p>{{ number_format((float) ($patient->total_amount ?? 0), 2, ',', '.') }} KM</p>
                                        <p class="text-emerald-700">{{ number_format((float) ($patient->paid_amount ?? 0), 2, ',', '.') }} KM</p>
                                        <p class="text-red-700">{{ number_format((float) ($patient->remaining_amount ?? 0), 2, ',', '.') }} KM</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('patients.show', array_filter([
                                                'patient' => $patient,
                                                'location_id' => $locationId > 0 ? $locationId : null,
                                                'date_from' => $dateFrom !== '' ? $dateFrom : null,
                                                'date_to' => $dateTo !== '' ? $dateTo : null,
                                            ])) }}" class="inline-flex items-center rounded-md border border-indigo-300 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">
                                                Karton
                                            </a>
                                            <a href="{{ route('patients.edit', $patient) }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                                Uredi
                                            </a>
                                            @if ($patient->is_active)
                                                <form method="POST" action="{{ route('patients.destroy', $patient) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center rounded-md border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50" onclick="return confirm('Potvrdi deaktivaciju pacijenta?')">
                                                        Deaktiviraj
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                        Nema pacijenata za prikaz.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $patients->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
