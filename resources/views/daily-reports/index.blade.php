<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dnevni izvjestaji
            </h2>
            <a href="{{ route('daily-reports.create') }}" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                Novi dnevni izvjestaj
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
                <form method="GET" action="{{ route('daily-reports.index') }}" class="grid gap-4 md:grid-cols-6">
                    <div>
                        <x-input-label for="date_from" value="Datum od" />
                        <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="$dateFrom" />
                    </div>
                    <div>
                        <x-input-label for="date_to" value="Datum do" />
                        <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="$dateTo" />
                    </div>
                    <div>
                        <x-input-label for="location_id" value="Lokacija" />
                        <select id="location_id" name="location_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0">Sve lokacije</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected($locationId === $location->id)>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Svi</option>
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}" @selected($status === $option)>
                                    {{ strtoupper($option) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-3 md:col-span-2">
                        <x-primary-button>Filtriraj</x-primary-button>
                        <a href="{{ route('daily-reports.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Datum</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Lokacija</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Stavke</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Podnosilac</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Akcije</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($reports as $report)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ \Illuminate\Support\Carbon::parse($report->report_date)->format('d.m.Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $report->location?->name ?: '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ strtoupper($report->status) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $report->items_count }} usluga / {{ $report->finding_items_count }} nalaza</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $report->submittedBy?->name ?: '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('daily-reports.show', $report) }}" class="inline-flex items-center rounded-md border border-indigo-300 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">
                                                Otvori
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                        Nema dnevnih izvjestaja.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
