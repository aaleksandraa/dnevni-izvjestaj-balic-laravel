@php
    $isSubmitted = $dailyReport->status === 'podnesen';
    $isLocked = $dailyReport->status === 'zakljucan' || $dailyReport->locked_at !== null;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dnevni izvjestaj - {{ \Illuminate\Support\Carbon::parse($dailyReport->report_date)->format('d.m.Y') }} / {{ $dailyReport->location?->name }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('daily-reports.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Nazad na listu
                </a>
                @if ($isSubmitted && auth()->user()?->hasAnyRole(['glavni_admin', 'administrator_klinike']))
                    <form method="POST" action="{{ route('daily-reports.reopen', $dailyReport) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-md border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                            Vrati u rad
                        </button>
                    </form>
                @endif
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

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Promet usluga</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($summary['services_amount'], 2, ',', '.') }} KM</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $summary['services_count'] }} stavki</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Naplaceno / Dug</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($summary['paid_amount'], 2, ',', '.') }} / {{ number_format($summary['remaining_amount'], 2, ',', '.') }} KM</p>
                    <p class="mt-1 text-xs text-gray-500">Automatski obracun dugovanja</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Nalazi + ukupno</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($summary['findings_amount'], 2, ',', '.') }} / {{ number_format($summary['grand_total'], 2, ',', '.') }} KM</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $summary['findings_count'] }} nalaza</p>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Zaglavlje izvjestaja</h3>
                <form method="POST" action="{{ route('daily-reports.update', $dailyReport) }}" class="mt-5 grid gap-6 md:grid-cols-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="report_date" value="Datum" />
                        <x-text-input id="report_date" name="report_date" type="date" class="mt-1 block w-full" :value="old('report_date', $dailyReport->report_date->toDateString())" :disabled="$isLocked" />
                    </div>
                    <div>
                        <x-input-label for="header_location_id" value="Lokacija" />
                        <select id="header_location_id" name="location_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>
                            @foreach ($locations as $locationOption)
                                <option value="{{ $locationOption->id }}" @selected((int) old('location_id', $dailyReport->location_id) === $locationOption->id)>
                                    {{ $locationOption->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        @if (auth()->user()?->hasAnyRole(['glavni_admin', 'administrator_klinike']))
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>
                                @foreach (['u_radu', 'podnesen', 'zakljucan'] as $option)
                                    <option value="{{ $option }}" @selected(old('status', $dailyReport->status) === $option)>{{ strtoupper($option) }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="status" value="{{ $dailyReport->status }}">
                            <p class="mt-2 text-sm font-semibold text-gray-700">{{ strtoupper($dailyReport->status) }}</p>
                        @endif
                    </div>
                    <div class="md:col-span-4">
                        <x-input-label for="notes" value="Napomena" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>{{ old('notes', $dailyReport->notes) }}</textarea>
                    </div>
                    <div class="md:col-span-4">
                        <x-primary-button :disabled="$isLocked">Sacuvaj zaglavlje</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Dodaj stavku usluge</h3>
                    <form method="POST" action="{{ route('daily-reports.items.store', $dailyReport) }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="patient_id" value="Pacijent" />
                            <select id="patient_id" name="patient_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>
                                <option value="">Odaberi pacijenta</option>
                                @foreach ($patients as $patientOption)
                                    <option value="{{ $patientOption->id }}" @selected((int) old('patient_id', 0) === $patientOption->id)>
                                        {{ $patientOption->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Ne vidis pacijenta? Dodaj ga kroz modul
                                <a href="{{ route('patients.create') }}" target="_blank" class="font-semibold text-indigo-600 hover:underline">Pacijenti</a>.
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('patient_id')" />
                        </div>
                        <div class="flex items-center gap-3 rounded-md border border-gray-200 bg-gray-50 px-3 py-2">
                            <input id="is_new_patient" name="is_new_patient" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked((bool) old('is_new_patient', false)) @disabled($isLocked)>
                            <label for="is_new_patient" class="text-sm text-gray-700">Novi pacijent</label>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <x-input-label for="service_id" value="Usluga" />
                                <select id="service_id" name="service_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>
                                    <option value="">Odaberi uslugu</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" data-price="{{ (float) $service->base_price }}" @selected((int) old('service_id', 0) === $service->id)>
                                            {{ $service->name }} ({{ number_format((float) $service->base_price, 2, ',', '.') }} KM)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="doctor_id" value="Doktor / saradnik" />
                                <select id="doctor_id" name="doctor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>
                                    <option value="">Bez odabira</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" @selected((int) old('doctor_id', 0) === $doctor->id)>
                                            {{ $doctor->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <x-input-label for="item_price" value="Cijena stavke" />
                                <x-text-input id="item_price" name="item_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('item_price')" :disabled="$isLocked" />
                            </div>
                            <div>
                                <x-input-label for="payment_status" value="Status placanja" />
                                <select id="payment_status" name="payment_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>
                                    @foreach (['neplaceno', 'placeno', 'djelimicno_placeno'] as $statusOption)
                                        <option value="{{ $statusOption }}" @selected(old('payment_status', 'neplaceno') === $statusOption)>
                                            {{ strtoupper($statusOption) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="paid_amount" value="Placeni iznos" />
                                <x-text-input id="paid_amount" name="paid_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('paid_amount')" :disabled="$isLocked" />
                            </div>
                        </div>

                        <div id="payment_method_wrap">
                            <x-input-label for="payment_method" value="Nacin placanja" />
                            <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>
                                <option value="">Odaberi nacin</option>
                                @foreach ($paymentMethods as $method)
                                    @php
                                        $normalized = \Illuminate\Support\Str::of($method->name)->lower()->replace('č', 'c')->replace('ć', 'c')->replace('ž', 'z')->replace('š', 's')->replace('đ', 'dj')->replace(' ', '_');
                                    @endphp
                                    <option value="{{ $normalized }}" @selected(old('payment_method') === (string) $normalized)>
                                        {{ $method->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="unpaid_reason_wrap">
                            <x-input-label for="unpaid_reason" value="Razlog neplacanja" />
                            <textarea id="unpaid_reason" name="unpaid_reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>{{ old('unpaid_reason') }}</textarea>
                        </div>

                        <div>
                            <x-input-label for="item_notes" value="Napomena" />
                            <textarea id="item_notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>{{ old('notes') }}</textarea>
                        </div>

                        <x-primary-button :disabled="$isLocked">Dodaj stavku usluge</x-primary-button>
                    </form>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Dodaj stavku nalaza</h3>
                    <form method="POST" action="{{ route('daily-reports.finding-items.store', $dailyReport) }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="finding_id" value="Nalaz" />
                            <select id="finding_id" name="finding_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>
                                <option value="">Odaberi nalaz</option>
                                @foreach ($findings as $finding)
                                    <option value="{{ $finding->id }}" data-price="{{ (float) ($finding->unit_price ?? 0) }}" @selected((int) old('finding_id', 0) === $finding->id)>
                                        {{ $finding->name }} ({{ $finding->unit_price !== null ? number_format((float) $finding->unit_price, 2, ',', '.') . ' KM' : '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <x-input-label for="quantity" value="Kolicina" />
                                <x-text-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full" :value="old('quantity', 1)" :disabled="$isLocked" />
                            </div>
                            <div>
                                <x-input-label for="unit_price" value="Jedinicna cijena" />
                                <x-text-input id="unit_price" name="unit_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('unit_price')" :disabled="$isLocked" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="finding_notes" value="Napomena" />
                            <textarea id="finding_notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>{{ old('notes') }}</textarea>
                        </div>
                        <x-primary-button :disabled="$isLocked">Dodaj stavku nalaza</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Stavke usluga</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Pacijent</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Novi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Usluga</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Doktor</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Placanje</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Cijena</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Naplaceno</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Dug</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Akcije</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($dailyReport->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->patient?->full_name ?? $item->patient_full_name }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-700">
                                        @if ($item->is_new_patient)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 font-semibold text-emerald-700">DA</span>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->service?->name ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->doctor?->full_name ?: '-' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-700">
                                        <div>{{ strtoupper($item->payment_status) }}</div>
                                        <div class="text-gray-500">{{ $item->payment_method ?: '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $item->item_price, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $item->paid_amount, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-700">{{ number_format((float) $item->remaining_amount, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('daily-reports.items.edit', [$dailyReport, $item]) }}" class="inline-flex items-center rounded-md border border-indigo-300 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-50 @if($isLocked) pointer-events-none opacity-50 @endif">
                                                Uredi
                                            </a>
                                            <form method="POST" action="{{ route('daily-reports.items.destroy', [$dailyReport, $item]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-md border border-red-300 px-3 py-1 text-xs font-semibold text-red-700 hover:bg-red-50" onclick="return confirm('Ukloniti ovu stavku?')" @disabled($isLocked)>
                                                    Obrisi
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">Nema unesenih stavki usluga.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Stavke nalaza</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nalaz</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kolicina</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jedinicna</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Ukupno</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Akcije</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($dailyReport->findingItems as $findingItem)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $findingItem->finding?->name ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $findingItem->quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $findingItem->unit_price, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-700">{{ number_format((float) $findingItem->total_price, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('daily-reports.finding-items.destroy', [$dailyReport, $findingItem]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-md border border-red-300 px-3 py-1 text-xs font-semibold text-red-700 hover:bg-red-50" onclick="return confirm('Ukloniti stavku nalaza?')" @disabled($isLocked)>
                                                Obrisi
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Nema unesenih nalaza.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Podnosenje izvjestaja</h3>
                <form method="POST" action="{{ route('daily-reports.submit', $dailyReport) }}" class="mt-4 space-y-4">
                    @csrf
                    @if (auth()->user()?->can_change_submitter)
                        <div>
                            <x-input-label for="submitted_by_user_id" value="Podnosilac izvjestaja" />
                            <select id="submitted_by_user_id" name="submitted_by_user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>
                                @foreach ($possibleSubmitters as $submitter)
                                    <option value="{{ $submitter->id }}" @selected((int) old('submitted_by_user_id', auth()->id()) === $submitter->id)>
                                        {{ $submitter->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-3">
                        <x-primary-button :disabled="$isLocked">Podnesi danasnji izvjestaj</x-primary-button>
                        <span class="text-sm text-gray-500">
                            Trenutni status: <strong>{{ strtoupper($dailyReport->status) }}</strong>
                            @if ($dailyReport->submitted_at)
                                (podnesen: {{ $dailyReport->submitted_at->format('d.m.Y H:i') }})
                            @endif
                        </span>
                    </div>
                </form>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Danasnja rekapitulacija</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Automatski pregled za datum {{ $dailyReport->report_date?->format('d.m.Y') }}.
                </p>

                <div class="mt-5 grid gap-4 md:grid-cols-5">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Broj pregleda danas</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $todayBreakdown['total_items_count'] }}</p>
                    </div>
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-blue-700">Novi pacijenti danas</p>
                        <p class="mt-2 text-2xl font-semibold text-blue-800">{{ $todayBreakdown['new_patients_count'] }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Promet danas</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($todayBreakdown['total_amount'], 2, ',', '.') }} KM</p>
                    </div>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-emerald-700">Naplaceno</p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-800">{{ number_format($todayBreakdown['paid_amount'], 2, ',', '.') }} KM</p>
                    </div>
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-red-700">Nenaplaceno (dug)</p>
                        <p class="mt-2 text-2xl font-semibold text-red-800">{{ number_format($todayBreakdown['remaining_amount'], 2, ',', '.') }} KM</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 xl:grid-cols-3">
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                            <h4 class="text-sm font-semibold text-gray-800">Pregledi po uslugama</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Usluga</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Broj</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Iznos</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($todayBreakdown['by_service'] as $row)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-800">{{ $row['name'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $row['count'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($row['amount'], 2, ',', '.') }} KM</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">Nema stavki usluga.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                            <h4 class="text-sm font-semibold text-gray-800">Pregledi po doktorima</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Doktor</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Broj</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Iznos</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($todayBreakdown['by_doctor'] as $row)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-800">{{ $row['name'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $row['count'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($row['amount'], 2, ',', '.') }} KM</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">Nema stavki usluga.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                            <h4 class="text-sm font-semibold text-gray-800">Naplaceno po nacinu</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nacin</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Broj</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Naplaceno</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($todayBreakdown['by_payment_method'] as $row)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-800">{{ $row['method_label'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ $row['count'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($row['paid_amount'], 2, ',', '.') }} KM</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">Nema naplacenih stavki.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <h4 class="text-sm font-semibold text-gray-800">Stanje nenaplacenih stavki</h4>
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Potpuno neplaceno</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $todayBreakdown['fully_unpaid_count'] }} stavki / {{ number_format($todayBreakdown['fully_unpaid_amount'], 2, ',', '.') }} KM
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Djelimicno placeno</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $todayBreakdown['partially_paid_count'] }} stavki / preostalo {{ number_format($todayBreakdown['partially_paid_remaining_amount'], 2, ',', '.') }} KM
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Ukupan preostali dug</p>
                            <p class="mt-1 text-sm font-semibold text-red-700">
                                {{ number_format($todayBreakdown['remaining_amount'], 2, ',', '.') }} KM
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const serviceSelect = document.getElementById('service_id');
            const itemPriceInput = document.getElementById('item_price');
            const paidAmountInput = document.getElementById('paid_amount');
            const paymentStatusSelect = document.getElementById('payment_status');
            const paymentMethodWrap = document.getElementById('payment_method_wrap');
            const unpaidReasonWrap = document.getElementById('unpaid_reason_wrap');
            const findingSelect = document.getElementById('finding_id');
            const unitPriceInput = document.getElementById('unit_price');

            const togglePaymentBlocks = () => {
                const status = paymentStatusSelect?.value || 'neplaceno';
                if (paymentMethodWrap) {
                    paymentMethodWrap.style.display = status === 'neplaceno' ? 'none' : 'block';
                }
                if (unpaidReasonWrap) {
                    unpaidReasonWrap.style.display = status === 'neplaceno' ? 'block' : 'none';
                }
                if (status === 'placeno' && itemPriceInput && paidAmountInput && (!paidAmountInput.value || Number(paidAmountInput.value) === 0)) {
                    paidAmountInput.value = itemPriceInput.value || '0';
                }
            };

            if (serviceSelect && itemPriceInput) {
                serviceSelect.addEventListener('change', () => {
                    const selected = serviceSelect.selectedOptions[0];
                    if (!selected) {
                        return;
                    }
                    const price = selected.getAttribute('data-price');
                    if (price !== null) {
                        itemPriceInput.value = price;
                        if (paymentStatusSelect?.value === 'placeno' && paidAmountInput) {
                            paidAmountInput.value = price;
                        }
                    }
                });
            }

            if (findingSelect && unitPriceInput) {
                findingSelect.addEventListener('change', () => {
                    const selected = findingSelect.selectedOptions[0];
                    if (!selected) {
                        return;
                    }
                    const price = selected.getAttribute('data-price');
                    if (price !== null) {
                        unitPriceInput.value = price;
                    }
                });
            }

            if (paymentStatusSelect) {
                paymentStatusSelect.addEventListener('change', togglePaymentBlocks);
                togglePaymentBlocks();
            }
        });
    </script>
</x-app-layout>
