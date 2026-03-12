<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Uredi stavku usluge - izvjestaj {{ $dailyReport->report_date?->format('d.m.Y') }} / {{ $dailyReport->location?->name }}
            </h2>
            <a href="{{ route('daily-reports.show', $dailyReport) }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Nazad na izvjestaj
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('daily-reports.items.update', [$dailyReport, $item]) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="patient_name" value="Pacijent (pisanje + sugestije)" />
                        <div class="relative">
                            <x-text-input
                                id="patient_name"
                                name="patient_name"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('patient_name', $item->patient?->full_name ?? $item->patient_full_name)"
                                placeholder="Upisite ime pacijenta..."
                                autocomplete="off"
                                required
                            />
                            <input type="hidden" id="patient_id" name="patient_id" value="{{ old('patient_id', (int) $item->patient_id) }}">
                            <div id="patient_suggestions_edit" class="absolute z-30 mt-1 hidden max-h-56 w-full overflow-y-auto rounded-md border border-gray-200 bg-white shadow-lg"></div>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('patient_name')" />
                        <x-input-error class="mt-2" :messages="$errors->get('patient_id')" />
                    </div>

                    <div class="flex items-center gap-3 rounded-md border border-gray-200 bg-gray-50 px-3 py-2">
                        <input id="is_new_patient" name="is_new_patient" type="checkbox" value="1" @checked((bool) old('is_new_patient', $item->is_new_patient)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <label for="is_new_patient" class="text-sm text-gray-700">Novi pacijent</label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label for="service_id" value="Usluga" />
                            <select id="service_id" name="service_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Odaberi uslugu</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}" data-price="{{ (float) $service->base_price }}" @selected((int) old('service_id', $item->service_id) === $service->id)>
                                        {{ $service->name }} ({{ number_format((float) $service->base_price, 2, ',', '.') }} KM)
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('service_id')" />
                        </div>
                        <div>
                            <x-input-label for="doctor_id" value="Doktor / saradnik" />
                            <select id="doctor_id" name="doctor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Bez odabira</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected((int) old('doctor_id', (int) $item->doctor_id) === $doctor->id)>
                                        {{ $doctor->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('doctor_id')" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <x-input-label for="item_price" value="Cijena stavke" />
                            <x-text-input id="item_price" name="item_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('item_price', (float) $item->item_price)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('item_price')" />
                        </div>
                        <div>
                            <x-input-label for="payment_status" value="Status placanja" />
                            <select id="payment_status" name="payment_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach (['neplaceno', 'placeno', 'djelimicno_placeno'] as $statusOption)
                                    <option value="{{ $statusOption }}" @selected(old('payment_status', $item->payment_status) === $statusOption)>
                                        {{ strtoupper($statusOption) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('payment_status')" />
                        </div>
                        <div>
                            <x-input-label for="paid_amount" value="Placeni iznos" />
                            <x-text-input id="paid_amount" name="paid_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('paid_amount', (float) $item->paid_amount)" />
                            <x-input-error class="mt-2" :messages="$errors->get('paid_amount')" />
                        </div>
                    </div>

                    <div id="payment_method_wrap">
                        <x-input-label for="payment_method" value="Nacin placanja" />
                        <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Odaberi nacin</option>
                            @foreach ($paymentMethods as $method)
                                @php
                                    $normalized = \Illuminate\Support\Str::of($method->name)->lower()->replace('č', 'c')->replace('ć', 'c')->replace('ž', 'z')->replace('š', 's')->replace('đ', 'dj')->replace(' ', '_');
                                @endphp
                                <option value="{{ $normalized }}" @selected(old('payment_method', $item->payment_method) === (string) $normalized)>
                                    {{ $method->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
                    </div>

                    <div id="unpaid_reason_wrap">
                        <x-input-label for="unpaid_reason" value="Razlog neplacanja" />
                        <textarea id="unpaid_reason" name="unpaid_reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('unpaid_reason', $item->unpaid_reason) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('unpaid_reason')" />
                    </div>

                    <div>
                        <x-input-label for="notes" value="Napomena" />
                        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $item->notes) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>Sacuvaj stavku</x-primary-button>
                        <a href="{{ route('daily-reports.show', $dailyReport) }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Nazad
                        </a>
                    </div>
                </form>
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
            const patientNameInput = document.getElementById('patient_name');
            const patientIdInput = document.getElementById('patient_id');
            const patientSuggestions = document.getElementById('patient_suggestions_edit');
            const patientsIndex = @json(
                $patients->map(fn ($patient) => [
                    'id' => (int) $patient->id,
                    'name' => (string) $patient->full_name,
                ])->values()
            );

            let highlightedPatientIndex = -1;
            let currentPatientMatches = [];

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

            if (paymentStatusSelect) {
                paymentStatusSelect.addEventListener('change', togglePaymentBlocks);
                togglePaymentBlocks();
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
        });
    </script>
</x-app-layout>
