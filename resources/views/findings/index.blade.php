<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nalazi
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('finding-categories.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Kategorije nalaza
                </a>
                <a href="{{ route('findings.create') }}" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Novi nalaz
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
                <form method="GET" action="{{ route('findings.index') }}" class="grid gap-4 md:grid-cols-5">
                    <div class="md:col-span-2">
                        <x-input-label for="q" value="Pretraga" />
                        <x-text-input id="q" name="q" type="text" class="mt-1 block w-full" :value="$search" placeholder="Naziv ili napomena..." />
                    </div>
                    <div>
                        <x-input-label for="finding_category_id" value="Kategorija" />
                        <select id="finding_category_id" name="finding_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0">Sve kategorije</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($categoryId === $category->id)>
                                    {{ $category->name }}
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
                        <a href="{{ route('findings.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
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
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Naziv</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kategorija</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Povezana usluga</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Cijena</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Akcije</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($findings as $finding)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $finding->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $finding->category?->name ?: '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $finding->service?->name ?: '-' }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-700">
                                        {{ $finding->unit_price !== null ? number_format((float) $finding->unit_price, 2, ',', '.').' KM' : '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($finding->is_active)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Aktivan</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-700">Neaktivan</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('findings.show', $finding) }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Prikaz</a>
                                            <a href="{{ route('findings.edit', $finding) }}" class="inline-flex items-center rounded-md border border-indigo-300 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">Uredi</a>
                                            @if ($finding->is_active)
                                                <form method="POST" action="{{ route('findings.destroy', $finding) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center rounded-md border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50" onclick="return confirm('Potvrdi deaktivaciju nalaza?')">
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
                                        Nema nalaza za prikaz.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $findings->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
