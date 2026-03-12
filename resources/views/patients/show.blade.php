<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Karton pacijenta: {{ $patient->full_name }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('patients.edit', $patient) }}" class="inline-flex items-center rounded-md border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                    Uredi pacijenta
                </a>
                <a href="{{ route('patients.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Nazad na listu
                </a>
            </div>
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
                <form method="GET" action="{{ route('patients.show', $patient) }}" class="grid gap-4 md:grid-cols-5">
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
                    <div class="flex items-end gap-3 md:col-span-2">
                        <x-primary-button>Filtriraj karton</x-primary-button>
                        <a href="{{ route('patients.show', $patient) }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Broj pregleda</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $summary['exams_count'] }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Ukupan promet</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($summary['total_amount'], 2, ',', '.') }} KM</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-emerald-700">Naplaceno</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-800">{{ number_format($summary['paid_amount'], 2, ',', '.') }} KM</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-red-700">Dug</p>
                    <p class="mt-2 text-2xl font-semibold text-red-800">{{ number_format($summary['remaining_amount'], 2, ',', '.') }} KM</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Placanja po nacinu</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nacin placanja</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Stavki</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Naplaceno</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($paymentByMethod as $row)
                                    <tr>
                                        <td class="px-6 py-3 text-sm text-gray-800">{{ $row['method'] }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700">{{ $row['items_count'] }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700">{{ number_format($row['paid_amount'], 2, ',', '.') }} KM</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-6 text-center text-sm text-gray-500">Nema naplacenih stavki.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Nenaplacene stavke</h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 px-4 py-3">
                            <span>Potpuno neplaceno</span>
                            <strong>{{ $summary['fully_unpaid_count'] }} / {{ number_format($summary['fully_unpaid_amount'], 2, ',', '.') }} KM</strong>
                        </div>
                        <div class="flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 px-4 py-3">
                            <span>Djelimicno placeno (preostalo)</span>
                            <strong>{{ $summary['partially_paid_count'] }} / {{ number_format($summary['partially_paid_amount'], 2, ',', '.') }} KM</strong>
                        </div>
                        <div class="flex items-center justify-between rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                            <span>Ukupan dug</span>
                            <strong>{{ number_format($summary['remaining_amount'], 2, ',', '.') }} KM</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Svi pregledi i stavke</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Datum</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Lokacija</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Usluga</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Doktor</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Placanje</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Cijena</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Naplaceno</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Dug</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-800">{{ $item->dailyReport?->report_date?->format('d.m.Y') ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->dailyReport?->location?->name ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->service?->name ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->doctor?->full_name ?: '-' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-700">
                                        <div>{{ strtoupper((string) $item->payment_status) }}</div>
                                        <div class="text-gray-500">{{ $item->payment_method ?: '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $item->item_price, 2, ',', '.') }} KM</td>
                                    <td class="px-4 py-3 text-sm text-emerald-700">{{ number_format((float) $item->paid_amount, 2, ',', '.') }} KM</td>
                                    <td class="px-4 py-3 text-sm text-red-700">{{ number_format((float) $item->remaining_amount, 2, ',', '.') }} KM</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">Nema stavki za odabrani filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
