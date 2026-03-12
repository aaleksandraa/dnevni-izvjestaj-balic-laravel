<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalji email primaoca
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('report-email-settings.edit', $setting) }}" class="inline-flex items-center rounded-md border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                    Uredi
                </a>
                <a href="{{ route('report-email-settings.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Nazad na listu
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <dl class="grid gap-6 md:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $setting->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Tip izvjestaja</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $reportTypes[$setting->report_type] ?? strtoupper($setting->report_type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $setting->is_active ? 'Aktivan' : 'Neaktivan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Kreiran</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $setting->created_at?->format('d.m.Y H:i') ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Zadnja izmjena</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $setting->updated_at?->format('d.m.Y H:i') ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
