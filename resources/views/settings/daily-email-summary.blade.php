<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Podesavanja dnevnog email izvjestaja
            </h2>
            <a href="{{ route('settings.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Nazad na podesavanja
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

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <p class="font-semibold">Provjeri unos:</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-600">
                    Oznacite stavke koje zelite da se prikazuju u dnevnom email rezimeu nakon podnosenja izvjestaja.
                </p>
            </div>

            <form method="POST" action="{{ route('settings.daily-email-summary.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Značajne usluge</h3>
                    <p class="mt-1 text-sm text-gray-500">Izabrane usluge ce se prikazati pojedinacno i kao zbir "Značajni pregledi".</p>

                    <div class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @forelse ($services as $service)
                            <label class="flex items-center gap-3 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    name="service_ids[]"
                                    value="{{ $service->id }}"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    @checked(in_array($service->id, old('service_ids', $configuration['service_ids']), true))
                                >
                                <span>{{ $service->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">Nema aktivnih usluga.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Saradnici</h3>
                    <p class="mt-1 text-sm text-gray-500">Oznaceni saradnici ce biti prikazani pojedinacno i sabrano.</p>

                    <div class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @forelse ($collaborators as $collaborator)
                            <label class="flex items-center gap-3 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    name="collaborator_ids[]"
                                    value="{{ $collaborator->id }}"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    @checked(in_array($collaborator->id, old('collaborator_ids', $configuration['collaborator_ids']), true))
                                >
                                <span>{{ $collaborator->full_name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">Nema aktivnih saradnika.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Glavni doktori</h3>
                    <p class="mt-1 text-sm text-gray-500">Oznaceni doktori ce biti prikazani pojedinacno i sabrano.</p>

                    <div class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @forelse ($leadDoctors as $doctor)
                            <label class="flex items-center gap-3 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    name="lead_doctor_ids[]"
                                    value="{{ $doctor->id }}"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    @checked(in_array($doctor->id, old('lead_doctor_ids', $configuration['lead_doctor_ids']), true))
                                >
                                <span>{{ $doctor->full_name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">Nema aktivnih doktora.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <label class="flex items-center gap-3 text-sm text-gray-700">
                        <input
                            type="checkbox"
                            name="include_new_patients"
                            value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked((bool) old('include_new_patients', $configuration['include_new_patients']))
                        >
                        <span>Prikazi broj novih pacijenata (is_new_patient)</span>
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Sacuvaj podesavanja</x-primary-button>
                    <a href="{{ route('report-email-settings.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Upravljanje primaocima
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
