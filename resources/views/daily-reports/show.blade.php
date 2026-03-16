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
                            <div class="flex items-center justify-between gap-3">
                                <x-input-label for="patient_name" value="Pacijent (pisanje + sugestije)" />
                                <label for="is_new_patient" class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input id="is_new_patient" name="is_new_patient" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked((bool) old('is_new_patient', false)) @disabled($isLocked)>
                                    <span>Novi pacijent</span>
                                </label>
                            </div>
                            <div class="relative">
                                <x-text-input
                                    id="patient_name"
                                    name="patient_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('patient_name', '')"
                                    placeholder="Upisite ime pacijenta..."
                                    autocomplete="off"
                                    :disabled="$isLocked"
                                />
                                <input type="hidden" id="patient_id" name="patient_id" value="{{ old('patient_id', '') }}">
                                <div id="patient_suggestions" class="absolute z-30 mt-1 hidden max-h-56 w-full overflow-y-auto rounded-md border border-gray-200 bg-white shadow-lg"></div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Ako odaberete predlozenog pacijenta, bice vezan postojeci zapis.
                                Ako unesete novo ime koje ne postoji, sistem ce automatski kreirati novog pacijenta.
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('patient_name')" />
                            <x-input-error class="mt-2" :messages="$errors->get('patient_id')" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
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
                            <div>
                                @php
                                    $selectedServiceId = (int) old('service_id', 0);
                                    $selectedServiceName = old('service_name');
                                    if ($selectedServiceName === null && $selectedServiceId > 0) {
                                        $selectedServiceName = optional($services->firstWhere('id', $selectedServiceId))->name ?? '';
                                    }
                                @endphp
                                <x-input-label for="service_name" value="Usluga (pisanje + sugestije)" />
                                <div class="relative">
                                    <x-text-input
                                        id="service_name"
                                        name="service_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        :value="$selectedServiceName"
                                        placeholder="Upisite naziv usluge..."
                                        autocomplete="off"
                                        :disabled="$isLocked"
                                    />
                                    <input type="hidden" id="service_id" name="service_id" value="{{ old('service_id', '') }}">
                                    <div id="service_suggestions" class="absolute z-30 mt-1 hidden max-h-56 w-full overflow-y-auto rounded-md border border-gray-200 bg-white shadow-lg"></div>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('service_id')" />
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
                            <x-input-label for="finding_patient_name" value="Pacijent (opcionalno)" />
                            <div class="relative">
                                <x-text-input
                                    id="finding_patient_name"
                                    name="finding_patient_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('finding_patient_name', '')"
                                    placeholder="Upisite ime pacijenta ili ostavite prazno za ukupno..."
                                    autocomplete="off"
                                    :disabled="$isLocked"
                                />
                                <input type="hidden" id="finding_patient_id" name="finding_patient_id" value="{{ old('finding_patient_id', '') }}">
                                <div id="finding_patient_suggestions" class="absolute z-30 mt-1 hidden max-h-56 w-full overflow-y-auto rounded-md border border-gray-200 bg-white shadow-lg"></div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Ako ostavite prazno, nalaz se evidentira kao ukupni unos.
                                Ako unesete ime koje ne postoji, sistem ce kreirati novog pacijenta.
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('finding_patient_name')" />
                            <x-input-error class="mt-2" :messages="$errors->get('finding_patient_id')" />
                        </div>
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
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <x-input-label for="finding_payment_status" value="Status placanja" />
                                <select id="finding_payment_status" name="finding_payment_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>
                                    @foreach (['neplaceno', 'placeno', 'djelimicno_placeno'] as $statusOption)
                                        <option value="{{ $statusOption }}" @selected(old('finding_payment_status', 'neplaceno') === $statusOption)>
                                            {{ strtoupper($statusOption) }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('finding_payment_status')" />
                            </div>
                            <div>
                                <x-input-label for="finding_paid_amount" value="Placeni iznos" />
                                <x-text-input id="finding_paid_amount" name="finding_paid_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('finding_paid_amount')" :disabled="$isLocked" />
                                <x-input-error class="mt-2" :messages="$errors->get('finding_paid_amount')" />
                            </div>
                            <div id="finding_payment_method_wrap">
                                <x-input-label for="finding_payment_method" value="Nacin placanja" />
                                <select id="finding_payment_method" name="finding_payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>
                                    <option value="">Odaberi nacin</option>
                                    @foreach ($paymentMethods as $method)
                                        @php
                                            $normalized = \Illuminate\Support\Str::of($method->name)->lower()->replace('Ä', 'c')->replace('Ä‡', 'c')->replace('Å¾', 'z')->replace('Å¡', 's')->replace('Ä‘', 'dj')->replace(' ', '_');
                                        @endphp
                                        <option value="{{ $normalized }}" @selected(old('finding_payment_method') === (string) $normalized)>
                                            {{ $method->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('finding_payment_method')" />
                            </div>
                        </div>
                        <div id="finding_unpaid_reason_wrap">
                            <x-input-label for="finding_unpaid_reason" value="Razlog neplacanja" />
                            <textarea id="finding_unpaid_reason" name="finding_unpaid_reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isLocked)>{{ old('finding_unpaid_reason') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('finding_unpaid_reason')" />
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
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Pacijent</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nalaz</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kolicina</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Placanje</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jedinicna</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Ukupno</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Naplaceno</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Dug</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Akcije</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($dailyReport->findingItems as $findingItem)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $findingItem->patient?->full_name ?? 'Ukupno' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $findingItem->finding?->name ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $findingItem->quantity }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-700">
                                        <div>{{ strtoupper((string) $findingItem->payment_status) }}</div>
                                        <div class="text-gray-500">{{ $findingItem->payment_method ?: '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $findingItem->unit_price, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-700">{{ number_format((float) $findingItem->total_price, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $findingItem->paid_amount, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-700">{{ number_format((float) $findingItem->remaining_amount, 2, ',', '.') }}</td>
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
                                    <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">Nema unesenih nalaza.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Danasnja rekapitulacija</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Automatski pregled za datum {{ $dailyReport->report_date?->format('d.m.Y') }}.
                </p>

                <div class="mt-5 grid gap-4 md:grid-cols-6">
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
                        <p class="mt-1 text-xs text-gray-500">Ukljucuje usluge + nalaze</p>
                    </div>
                    <div class="rounded-lg border border-violet-200 bg-violet-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-violet-700">Nalazi danas</p>
                        <p class="mt-2 text-2xl font-semibold text-violet-800">{{ number_format($todayBreakdown['findings_amount'], 2, ',', '.') }} KM</p>
                        <p class="mt-1 text-xs text-violet-700">{{ $todayBreakdown['findings_count'] }} stavki</p>
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
        </div>
    </div>

    @php
        $patientsIndex = $patients->map(fn ($patient) => [
            'id' => (int) $patient->id,
            'name' => (string) $patient->full_name,
        ])->values();

        $servicesIndex = $services->map(fn ($service) => [
            'id' => (int) $service->id,
            'name' => (string) $service->name,
            'price' => (float) $service->base_price,
        ])->values();
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const serviceNameInput = document.getElementById('service_name');
            const serviceIdInput = document.getElementById('service_id');
            const serviceSuggestions = document.getElementById('service_suggestions');
            const itemPriceInput = document.getElementById('item_price');
            const paidAmountInput = document.getElementById('paid_amount');
            const paymentStatusSelect = document.getElementById('payment_status');
            const paymentMethodWrap = document.getElementById('payment_method_wrap');
            const unpaidReasonWrap = document.getElementById('unpaid_reason_wrap');
            const findingSelect = document.getElementById('finding_id');
            const findingQuantityInput = document.getElementById('quantity');
            const unitPriceInput = document.getElementById('unit_price');
            const findingPaymentStatusSelect = document.getElementById('finding_payment_status');
            const findingPaidAmountInput = document.getElementById('finding_paid_amount');
            const findingPaymentMethodWrap = document.getElementById('finding_payment_method_wrap');
            const findingPaymentMethodSelect = document.getElementById('finding_payment_method');
            const findingUnpaidReasonWrap = document.getElementById('finding_unpaid_reason_wrap');
            const patientNameInput = document.getElementById('patient_name');
            const patientIdInput = document.getElementById('patient_id');
            const patientSuggestions = document.getElementById('patient_suggestions');
            const findingPatientNameInput = document.getElementById('finding_patient_name');
            const findingPatientIdInput = document.getElementById('finding_patient_id');
            const findingPatientSuggestions = document.getElementById('finding_patient_suggestions');
            const patientsIndex = @json($patientsIndex);
            const servicesIndex = @json($servicesIndex);

            let highlightedPatientIndex = -1;
            let currentPatientMatches = [];
            let highlightedFindingPatientIndex = -1;
            let currentFindingPatientMatches = [];
            let highlightedServiceIndex = -1;
            let currentServiceMatches = [];

            const normalizeText = (value) => {
                let normalized = String(value || '').toLowerCase();
                try {
                    normalized = normalized.normalize('NFD');
                } catch (error) {
                    // ignore normalize errors in older browsers
                }
                return normalized
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/đ/g, 'dj')
                    .replace(/[^a-z0-9\s]/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();
            };

            const patientLookup = patientsIndex.map((patient) => {
                const normalizedName = normalizeText(patient.name);
                return {
                    ...patient,
                    normalizedName,
                    tokens: normalizedName.split(' ').filter(Boolean),
                };
            });
            const escapeHtml = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const resolveExactPatientId = (name) => {
                const normalizedInput = normalizeText(name);
                const exactMatch = patientLookup.find((patient) => patient.normalizedName === normalizedInput);
                return exactMatch ? String(exactMatch.id) : '';
            };

            const patientScore = (patient, normalizedQuery, queryTokens) => {
                if (!normalizedQuery) {
                    return 0;
                }

                let score = 0;
                if (patient.normalizedName === normalizedQuery) {
                    score += 1000;
                }
                if (patient.normalizedName.startsWith(normalizedQuery)) {
                    score += 700;
                }
                if (patient.normalizedName.includes(normalizedQuery)) {
                    score += 500;
                }

                if (queryTokens.length > 0 && queryTokens.every((token) => patient.normalizedName.includes(token))) {
                    score += 250;
                }

                queryTokens.forEach((token) => {
                    if (patient.tokens.some((part) => part.startsWith(token))) {
                        score += 120;
                    } else if (patient.normalizedName.includes(token)) {
                        score += 70;
                    }
                });

                return score;
            };

            const renderPatientSuggestions = () => {
                if (!patientSuggestions || !patientNameInput || !patientIdInput) {
                    return;
                }

                const rawQuery = patientNameInput.value.trim();
                const normalizedQuery = normalizeText(rawQuery);
                patientIdInput.value = resolveExactPatientId(rawQuery);

                if (normalizedQuery.length < 2) {
                    patientSuggestions.classList.add('hidden');
                    patientSuggestions.innerHTML = '';
                    currentPatientMatches = [];
                    highlightedPatientIndex = -1;
                    return;
                }

                const queryTokens = normalizedQuery.split(' ').filter(Boolean);
                const matches = patientLookup
                    .map((patient) => ({
                        patient,
                        score: patientScore(patient, normalizedQuery, queryTokens),
                    }))
                    .filter((row) => row.score > 0)
                    .sort((a, b) => {
                        if (b.score !== a.score) {
                            return b.score - a.score;
                        }
                        return a.patient.name.localeCompare(b.patient.name, 'bs');
                    })
                    .slice(0, 8)
                    .map((row) => row.patient);

                currentPatientMatches = matches;
                highlightedPatientIndex = -1;

                if (matches.length === 0) {
                    patientSuggestions.innerHTML = '<div class="px-3 py-2 text-xs text-gray-500">Nema podudaranja. Unos ce kreirati novog pacijenta.</div>';
                    patientSuggestions.classList.remove('hidden');
                    return;
                }

                patientSuggestions.innerHTML = matches.map((patient, index) => {
                    const safeName = escapeHtml(patient.name);
                    return `<button type="button" class="js-patient-suggestion flex w-full items-center justify-between border-t border-gray-100 px-3 py-2 text-left text-sm hover:bg-indigo-50" data-index="${index}"><span>${safeName}</span><span class="text-xs text-gray-400">postojeci</span></button>`;
                }).join('');

                patientSuggestions.classList.remove('hidden');
            };

            const hidePatientSuggestions = () => {
                if (!patientSuggestions) {
                    return;
                }
                patientSuggestions.classList.add('hidden');
                highlightedPatientIndex = -1;
            };

            const applyPatientSelection = (patient) => {
                if (!patientNameInput || !patientIdInput) {
                    return;
                }

                patientNameInput.value = patient.name;
                patientIdInput.value = String(patient.id);
                hidePatientSuggestions();
            };

            const highlightSuggestion = () => {
                if (!patientSuggestions) {
                    return;
                }

                const rows = Array.from(patientSuggestions.querySelectorAll('.js-patient-suggestion'));
                rows.forEach((row, index) => {
                    row.classList.toggle('bg-indigo-100', index === highlightedPatientIndex);
                });
            };

            const renderFindingPatientSuggestions = () => {
                if (!findingPatientSuggestions || !findingPatientNameInput || !findingPatientIdInput) {
                    return;
                }

                const rawQuery = findingPatientNameInput.value.trim();
                const normalizedQuery = normalizeText(rawQuery);
                findingPatientIdInput.value = resolveExactPatientId(rawQuery);

                if (normalizedQuery.length < 2) {
                    findingPatientSuggestions.classList.add('hidden');
                    findingPatientSuggestions.innerHTML = '';
                    currentFindingPatientMatches = [];
                    highlightedFindingPatientIndex = -1;
                    return;
                }

                const queryTokens = normalizedQuery.split(' ').filter(Boolean);
                const matches = patientLookup
                    .map((patient) => ({
                        patient,
                        score: patientScore(patient, normalizedQuery, queryTokens),
                    }))
                    .filter((row) => row.score > 0)
                    .sort((a, b) => {
                        if (b.score !== a.score) {
                            return b.score - a.score;
                        }
                        return a.patient.name.localeCompare(b.patient.name, 'bs');
                    })
                    .slice(0, 8)
                    .map((row) => row.patient);

                currentFindingPatientMatches = matches;
                highlightedFindingPatientIndex = -1;

                if (matches.length === 0) {
                    findingPatientSuggestions.innerHTML = '<div class="px-3 py-2 text-xs text-gray-500">Nema podudaranja. Unos ce kreirati novog pacijenta.</div>';
                    findingPatientSuggestions.classList.remove('hidden');
                    return;
                }

                findingPatientSuggestions.innerHTML = matches.map((patient, index) => {
                    const safeName = escapeHtml(patient.name);
                    return `<button type="button" class="js-finding-patient-suggestion flex w-full items-center justify-between border-t border-gray-100 px-3 py-2 text-left text-sm hover:bg-indigo-50" data-index="${index}"><span>${safeName}</span><span class="text-xs text-gray-400">postojeci</span></button>`;
                }).join('');

                findingPatientSuggestions.classList.remove('hidden');
            };

            const hideFindingPatientSuggestions = () => {
                if (!findingPatientSuggestions) {
                    return;
                }
                findingPatientSuggestions.classList.add('hidden');
                highlightedFindingPatientIndex = -1;
            };

            const applyFindingPatientSelection = (patient) => {
                if (!findingPatientNameInput || !findingPatientIdInput) {
                    return;
                }

                findingPatientNameInput.value = patient.name;
                findingPatientIdInput.value = String(patient.id);
                hideFindingPatientSuggestions();
            };

            const highlightFindingPatientSuggestion = () => {
                if (!findingPatientSuggestions) {
                    return;
                }

                const rows = Array.from(findingPatientSuggestions.querySelectorAll('.js-finding-patient-suggestion'));
                rows.forEach((row, index) => {
                    row.classList.toggle('bg-indigo-100', index === highlightedFindingPatientIndex);
                });
            };

            const serviceLookup = servicesIndex.map((service) => {
                const normalizedName = normalizeText(service.name);
                return {
                    ...service,
                    normalizedName,
                    tokens: normalizedName.split(' ').filter(Boolean),
                };
            });

            const resolveExactService = (name) => {
                const normalizedInput = normalizeText(name);
                return serviceLookup.find((service) => service.normalizedName === normalizedInput) || null;
            };

            const serviceScore = (service, normalizedQuery, queryTokens) => {
                if (!normalizedQuery) {
                    return 0;
                }

                let score = 0;
                if (service.normalizedName === normalizedQuery) {
                    score += 1000;
                }
                if (service.normalizedName.startsWith(normalizedQuery)) {
                    score += 700;
                }
                if (service.normalizedName.includes(normalizedQuery)) {
                    score += 500;
                }

                if (queryTokens.length > 0 && queryTokens.every((token) => service.normalizedName.includes(token))) {
                    score += 250;
                }

                queryTokens.forEach((token) => {
                    if (service.tokens.some((part) => part.startsWith(token))) {
                        score += 120;
                    } else if (service.normalizedName.includes(token)) {
                        score += 70;
                    }
                });

                return score;
            };

            const setServicePrice = (price) => {
                if (!itemPriceInput) {
                    return;
                }

                itemPriceInput.value = String(price);
                if (paymentStatusSelect?.value === 'placeno' && paidAmountInput) {
                    paidAmountInput.value = String(price);
                }
            };

            const hideServiceSuggestions = () => {
                if (!serviceSuggestions) {
                    return;
                }
                serviceSuggestions.classList.add('hidden');
                highlightedServiceIndex = -1;
            };

            const applyServiceSelection = (service) => {
                if (!serviceNameInput || !serviceIdInput) {
                    return;
                }

                serviceNameInput.value = service.name;
                serviceIdInput.value = String(service.id);
                setServicePrice(service.price);
                hideServiceSuggestions();
            };

            const highlightServiceSuggestion = () => {
                if (!serviceSuggestions) {
                    return;
                }

                const rows = Array.from(serviceSuggestions.querySelectorAll('.js-service-suggestion'));
                rows.forEach((row, index) => {
                    row.classList.toggle('bg-indigo-100', index === highlightedServiceIndex);
                });
            };

            const renderServiceSuggestions = () => {
                if (!serviceNameInput || !serviceIdInput || !serviceSuggestions) {
                    return;
                }

                const rawQuery = serviceNameInput.value.trim();
                const normalizedQuery = normalizeText(rawQuery);
                const exactService = resolveExactService(rawQuery);
                serviceIdInput.value = exactService ? String(exactService.id) : '';

                if (exactService) {
                    setServicePrice(exactService.price);
                }

                if (normalizedQuery.length < 2) {
                    serviceSuggestions.classList.add('hidden');
                    serviceSuggestions.innerHTML = '';
                    currentServiceMatches = [];
                    highlightedServiceIndex = -1;
                    return;
                }

                const queryTokens = normalizedQuery.split(' ').filter(Boolean);
                const matches = serviceLookup
                    .map((service) => ({
                        service,
                        score: serviceScore(service, normalizedQuery, queryTokens),
                    }))
                    .filter((row) => row.score > 0)
                    .sort((a, b) => {
                        if (b.score !== a.score) {
                            return b.score - a.score;
                        }
                        return a.service.name.localeCompare(b.service.name, 'bs');
                    })
                    .slice(0, 8)
                    .map((row) => row.service);

                currentServiceMatches = matches;
                highlightedServiceIndex = -1;

                if (matches.length === 0) {
                    serviceSuggestions.innerHTML = '<div class="px-3 py-2 text-xs text-gray-500">Nema podudaranja za uslugu.</div>';
                    serviceSuggestions.classList.remove('hidden');
                    return;
                }

                serviceSuggestions.innerHTML = matches.map((service, index) => {
                    const safeName = escapeHtml(service.name);
                    const safePrice = Number(service.price || 0).toLocaleString('bs-BA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    return `<button type="button" class="js-service-suggestion flex w-full items-center justify-between border-t border-gray-100 px-3 py-2 text-left text-sm hover:bg-indigo-50" data-index="${index}"><span>${safeName}</span><span class="text-xs text-gray-500">${safePrice} KM</span></button>`;
                }).join('');

                serviceSuggestions.classList.remove('hidden');
            };

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

            const resolveFindingTotal = () => {
                const quantity = Number(findingQuantityInput?.value || 0);
                const unitPrice = Number(unitPriceInput?.value || 0);
                const normalizedQuantity = Number.isFinite(quantity) && quantity > 0 ? quantity : 0;
                const normalizedUnitPrice = Number.isFinite(unitPrice) && unitPrice > 0 ? unitPrice : 0;
                return Number((normalizedQuantity * normalizedUnitPrice).toFixed(2));
            };

            const syncFindingPaidForFullyPaid = () => {
                if (findingPaymentStatusSelect?.value !== 'placeno' || !findingPaidAmountInput) {
                    return;
                }

                const findingTotal = resolveFindingTotal();
                if (!findingPaidAmountInput.value || Number(findingPaidAmountInput.value) === 0) {
                    findingPaidAmountInput.value = String(findingTotal);
                }
            };

            const toggleFindingPaymentBlocks = () => {
                const status = findingPaymentStatusSelect?.value || 'neplaceno';
                if (findingPaymentMethodWrap) {
                    findingPaymentMethodWrap.style.display = status === 'neplaceno' ? 'none' : 'block';
                }
                if (findingUnpaidReasonWrap) {
                    findingUnpaidReasonWrap.style.display = status === 'neplaceno' ? 'block' : 'none';
                }

                if (status === 'neplaceno' && findingPaymentMethodSelect) {
                    findingPaymentMethodSelect.value = '';
                }

                syncFindingPaidForFullyPaid();
            };

            if (serviceNameInput && serviceIdInput && serviceSuggestions) {
                serviceNameInput.addEventListener('input', renderServiceSuggestions);
                serviceNameInput.addEventListener('focus', renderServiceSuggestions);
                serviceNameInput.addEventListener('blur', () => {
                    setTimeout(() => {
                        const exactService = resolveExactService(serviceNameInput.value);
                        serviceIdInput.value = exactService ? String(exactService.id) : '';
                        if (exactService) {
                            setServicePrice(exactService.price);
                        }
                        hideServiceSuggestions();
                    }, 120);
                });
                serviceNameInput.addEventListener('keydown', (event) => {
                    if (serviceSuggestions.classList.contains('hidden') || currentServiceMatches.length === 0) {
                        return;
                    }

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        highlightedServiceIndex = (highlightedServiceIndex + 1) % currentServiceMatches.length;
                        highlightServiceSuggestion();
                        return;
                    }

                    if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        highlightedServiceIndex = highlightedServiceIndex <= 0
                            ? currentServiceMatches.length - 1
                            : highlightedServiceIndex - 1;
                        highlightServiceSuggestion();
                        return;
                    }

                    if (event.key === 'Enter' && highlightedServiceIndex >= 0) {
                        event.preventDefault();
                        applyServiceSelection(currentServiceMatches[highlightedServiceIndex]);
                    }
                });

                serviceSuggestions.addEventListener('mousedown', (event) => {
                    const target = event.target.closest('.js-service-suggestion');
                    if (!target) {
                        return;
                    }

                    event.preventDefault();
                    const selectedIndex = Number(target.dataset.index ?? -1);
                    const selectedService = currentServiceMatches[selectedIndex];
                    if (!selectedService) {
                        return;
                    }
                    applyServiceSelection(selectedService);
                });

                renderServiceSuggestions();
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
                        syncFindingPaidForFullyPaid();
                    }
                });
            }

            if (findingQuantityInput) {
                findingQuantityInput.addEventListener('input', syncFindingPaidForFullyPaid);
            }

            if (unitPriceInput) {
                unitPriceInput.addEventListener('input', syncFindingPaidForFullyPaid);
            }

            if (findingPaymentStatusSelect) {
                findingPaymentStatusSelect.addEventListener('change', toggleFindingPaymentBlocks);
                toggleFindingPaymentBlocks();
            }

            if (patientNameInput && patientIdInput && patientSuggestions) {
                patientNameInput.addEventListener('input', renderPatientSuggestions);
                patientNameInput.addEventListener('focus', renderPatientSuggestions);
                patientNameInput.addEventListener('blur', () => {
                    setTimeout(() => {
                        patientIdInput.value = resolveExactPatientId(patientNameInput.value);
                        hidePatientSuggestions();
                    }, 120);
                });
                patientNameInput.addEventListener('keydown', (event) => {
                    if (patientSuggestions.classList.contains('hidden') || currentPatientMatches.length === 0) {
                        return;
                    }

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        highlightedPatientIndex = (highlightedPatientIndex + 1) % currentPatientMatches.length;
                        highlightSuggestion();
                        return;
                    }

                    if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        highlightedPatientIndex = highlightedPatientIndex <= 0
                            ? currentPatientMatches.length - 1
                            : highlightedPatientIndex - 1;
                        highlightSuggestion();
                        return;
                    }

                    if (event.key === 'Enter' && highlightedPatientIndex >= 0) {
                        event.preventDefault();
                        applyPatientSelection(currentPatientMatches[highlightedPatientIndex]);
                    }
                });

                patientSuggestions.addEventListener('mousedown', (event) => {
                    const target = event.target.closest('.js-patient-suggestion');
                    if (!target) {
                        return;
                    }

                    event.preventDefault();
                    const selectedIndex = Number(target.dataset.index ?? -1);
                    const selectedPatient = currentPatientMatches[selectedIndex];
                    if (!selectedPatient) {
                        return;
                    }
                    applyPatientSelection(selectedPatient);
                });

                renderPatientSuggestions();
            }

            if (findingPatientNameInput && findingPatientIdInput && findingPatientSuggestions) {
                findingPatientNameInput.addEventListener('input', renderFindingPatientSuggestions);
                findingPatientNameInput.addEventListener('focus', renderFindingPatientSuggestions);
                findingPatientNameInput.addEventListener('blur', () => {
                    setTimeout(() => {
                        findingPatientIdInput.value = resolveExactPatientId(findingPatientNameInput.value);
                        hideFindingPatientSuggestions();
                    }, 120);
                });
                findingPatientNameInput.addEventListener('keydown', (event) => {
                    if (findingPatientSuggestions.classList.contains('hidden') || currentFindingPatientMatches.length === 0) {
                        return;
                    }

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        highlightedFindingPatientIndex = (highlightedFindingPatientIndex + 1) % currentFindingPatientMatches.length;
                        highlightFindingPatientSuggestion();
                        return;
                    }

                    if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        highlightedFindingPatientIndex = highlightedFindingPatientIndex <= 0
                            ? currentFindingPatientMatches.length - 1
                            : highlightedFindingPatientIndex - 1;
                        highlightFindingPatientSuggestion();
                        return;
                    }

                    if (event.key === 'Enter' && highlightedFindingPatientIndex >= 0) {
                        event.preventDefault();
                        applyFindingPatientSelection(currentFindingPatientMatches[highlightedFindingPatientIndex]);
                    }
                });

                findingPatientSuggestions.addEventListener('mousedown', (event) => {
                    const target = event.target.closest('.js-finding-patient-suggestion');
                    if (!target) {
                        return;
                    }

                    event.preventDefault();
                    const selectedIndex = Number(target.dataset.index ?? -1);
                    const selectedPatient = currentFindingPatientMatches[selectedIndex];
                    if (!selectedPatient) {
                        return;
                    }
                    applyFindingPatientSelection(selectedPatient);
                });

                renderFindingPatientSuggestions();
            }

            if (paymentStatusSelect) {
                paymentStatusSelect.addEventListener('change', togglePaymentBlocks);
                togglePaymentBlocks();
            }
        });
    </script>
</x-app-layout>
