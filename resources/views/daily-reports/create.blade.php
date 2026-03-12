<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Novi dnevni izvjestaj
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('daily-reports.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label for="report_date" value="Datum izvjestaja" />
                            <x-text-input id="report_date" name="report_date" type="date" class="mt-1 block w-full" :value="old('report_date', $defaultDate)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('report_date')" />
                        </div>

                        <div>
                            <x-input-label for="location_id" value="Lokacija" />
                            <select id="location_id" name="location_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Odaberi lokaciju</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" @selected((int) old('location_id', 0) === $location->id)>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('location_id')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Napomena zaglavlja" />
                        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>Kreiraj izvjestaj</x-primary-button>
                        <a href="{{ route('daily-reports.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Nazad
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
