<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Dobrodosli u Reports IVF</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Autentikacija je aktivna. Prvi spreman modul iz Faze 1 je upravljanje lokacijama.
                </p>
            </div>

            @if (auth()->user()?->hasAnyRole(['glavni_admin', 'administrator_klinike']))
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Brze akcije</h4>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a
                            href="{{ route('locations.index') }}"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                        >
                            Otvori modul Lokacije
                        </a>
                        <a
                            href="{{ route('locations.create') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Dodaj novu lokaciju
                        </a>
                        <a
                            href="{{ route('services.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Otvori modul Usluge
                        </a>
                        <a
                            href="{{ route('findings.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Otvori modul Nalazi
                        </a>
                        <a
                            href="{{ route('staff-members.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Otvori Medicinski tim
                        </a>
                        <a
                            href="{{ route('daily-reports.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Otvori Dnevne izvjestaje
                        </a>
                        <a
                            href="{{ route('users.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Otvori Korisnike
                        </a>
                        <a
                            href="{{ route('report-email-settings.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Otvori Email primaoce
                        </a>
                        <a
                            href="{{ route('audit-logs.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Otvori Audit log
                        </a>
                        <a
                            href="{{ route('patients.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Otvori Pacijente
                        </a>
                    </div>
                </div>
            @endif

            @if (auth()->user()?->hasRole('medicinska_sestra'))
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Brza akcija sestre</h4>
                    <div class="mt-4">
                        <a
                            href="{{ route('daily-reports.index') }}"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                        >
                            Otvori dnevne izvjestaje
                        </a>
                        <a
                            href="{{ route('patients.index') }}"
                            class="ml-2 inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Otvori pacijente
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
