<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Audit log
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <form method="GET" action="{{ route('audit-logs.index') }}" class="grid gap-4 md:grid-cols-6">
                    <div class="md:col-span-2">
                        <x-input-label for="q" value="Pretraga" />
                        <x-text-input id="q" name="q" type="text" class="mt-1 block w-full" :value="$search" placeholder="Opis, akcija, entitet, ID..." />
                    </div>

                    <div>
                        <x-input-label for="entity_type" value="Entitet" />
                        <select id="entity_type" name="entity_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Svi</option>
                            @foreach ($entityTypes as $entityTypeOption)
                                <option value="{{ $entityTypeOption }}" @selected($entityType === $entityTypeOption)>
                                    {{ $entityTypeOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="action" value="Akcija" />
                        <select id="action" name="action" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Sve</option>
                            @foreach ($actions as $actionOption)
                                <option value="{{ $actionOption }}" @selected($action === $actionOption)>
                                    {{ $actionOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="user_id" value="Korisnik" />
                        <select id="user_id" name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0">Svi</option>
                            @foreach ($users as $userOption)
                                <option value="{{ $userOption->id }}" @selected($userId === $userOption->id)>
                                    {{ $userOption->name }} ({{ $userOption->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-3">
                        <x-primary-button>Filtriraj</x-primary-button>
                        <a href="{{ route('audit-logs.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Reset
                        </a>
                    </div>

                    <div>
                        <x-input-label for="date_from" value="Datum od" />
                        <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="$dateFrom" />
                    </div>

                    <div>
                        <x-input-label for="date_to" value="Datum do" />
                        <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="$dateTo" />
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Vrijeme</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Korisnik</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Entitet</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Akcija</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Opis</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Detalji</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="px-6 py-4 text-xs text-gray-700 whitespace-nowrap">{{ $log->created_at?->format('d.m.Y H:i:s') ?: '-' }}</td>
                                    <td class="px-6 py-4 text-xs text-gray-700">
                                        {{ $log->user?->name ?: 'Sistem' }}
                                        @if ($log->user?->email)
                                            <div class="text-gray-500">{{ $log->user->email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-700">
                                        <div>{{ $log->entity_type }}</div>
                                        <div class="text-gray-500">ID: {{ $log->entity_id ?: '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <span class="inline-flex rounded-full bg-indigo-100 px-2.5 py-1 font-semibold text-indigo-700">{{ $log->action }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-700">{{ $log->description ?: '-' }}</td>
                                    <td class="px-6 py-4 text-xs text-gray-700">
                                        @if ($log->old_values || $log->new_values)
                                            <details>
                                                <summary class="cursor-pointer text-indigo-700">Prikazi</summary>
                                                <div class="mt-2 space-y-2">
                                                    @if ($log->old_values)
                                                        <div>
                                                            <p class="font-semibold text-gray-600">Stare vrijednosti</p>
                                                            <pre class="mt-1 max-w-md overflow-x-auto rounded bg-gray-50 p-2 text-[11px] leading-tight">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </div>
                                                    @endif
                                                    @if ($log->new_values)
                                                        <div>
                                                            <p class="font-semibold text-gray-600">Nove vrijednosti</p>
                                                            <pre class="mt-1 max-w-md overflow-x-auto rounded bg-gray-50 p-2 text-[11px] leading-tight">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </div>
                                                    @endif
                                                </div>
                                            </details>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                        Nema audit log zapisa za prikaz.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
