<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Email primaoci izvjestaja
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('settings.daily-email-summary.edit') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Podesi sadrzaj dnevnog emaila
                </a>
                <a href="{{ route('report-email-settings.create') }}" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Novi primaoc
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
                <form method="GET" action="{{ route('report-email-settings.index') }}" class="grid gap-4 md:grid-cols-5">
                    <div class="md:col-span-2">
                        <x-input-label for="q" value="Pretraga" />
                        <x-text-input id="q" name="q" type="text" class="mt-1 block w-full" :value="$search" placeholder="Email adresa..." />
                    </div>
                    <div>
                        <x-input-label for="report_type" value="Tip izvjestaja" />
                        <select id="report_type" name="report_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Svi</option>
                            @foreach ($reportTypes as $reportTypeValue => $reportTypeLabel)
                                <option value="{{ $reportTypeValue }}" @selected($reportType === $reportTypeValue)>
                                    {{ $reportTypeLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Svi</option>
                            <option value="active" @selected($status === 'active')>Aktivni</option>
                            <option value="inactive" @selected($status === 'inactive')>Neaktivni</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-3">
                        <x-primary-button>Filtriraj</x-primary-button>
                        <a href="{{ route('report-email-settings.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
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
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tip</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Azurirano</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Akcije</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($settings as $setting)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $setting->email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $reportTypes[$setting->report_type] ?? strtoupper($setting->report_type) }}</td>
                                    <td class="px-6 py-4">
                                        @if ($setting->is_active)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Aktivan</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-700">Neaktivan</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $setting->updated_at?->format('d.m.Y H:i') ?: '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('report-email-settings.show', $setting) }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Prikaz</a>
                                            <a href="{{ route('report-email-settings.edit', $setting) }}" class="inline-flex items-center rounded-md border border-indigo-300 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">Uredi</a>
                                            @if ($setting->is_active)
                                                <form method="POST" action="{{ route('report-email-settings.destroy', $setting) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center rounded-md border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50" onclick="return confirm('Potvrdi deaktivaciju primaoca?')">
                                                        Deaktiviraj
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                        Nema email primalaca za prikaz.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $settings->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
