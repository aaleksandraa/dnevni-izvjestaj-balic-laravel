<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Karton pacijenta: {{ $patient->full_name }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('patients.edit', $patient) }}" class="inline-flex items-center rounded-md border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                    Uredi pacijenta
                </a>
                <a href="{{ route('patients.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Nazad na listu
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
                <form method="GET" action="{{ route('patients.show', $patient) }}" class="grid gap-4 md:grid-cols-5">
                    <div>
                        <x-input-label for="filter_location_id" value="Lokacija" />
                        <select id="filter_location_id" name="location_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0">Sve lokacije</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected($locationId === $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="date_from" value="Datum od" />
                        <x-text-input id="date_from" name="date_from" type="text" class="mt-1 block w-full" :value="$dateFrom" placeholder="dd.mm.gggg" inputmode="numeric" />
                    </div>
                    <div>
                        <x-input-label for="date_to" value="Datum do" />
                        <x-text-input id="date_to" name="date_to" type="text" class="mt-1 block w-full" :value="$dateTo" placeholder="dd.mm.gggg" inputmode="numeric" />
                    </div>
                    <div class="flex items-end gap-3 md:col-span-2">
                        <x-primary-button>Filtriraj karton</x-primary-button>
                        <a href="{{ route('patients.show', $patient) }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Dodaj placanje</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Stavka se automatski upisuje u danasnji dnevni izvjestaj odabrane lokacije.
                </p>

                <form method="POST" action="{{ route('patients.payments.store', $patient) }}" class="mt-5 space-y-4">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-4">
                        <div>
                            <x-input-label for="report_date" value="Datum izvjestaja" />
                            <x-text-input id="report_date" name="report_date" type="text" class="mt-1 block w-full bg-gray-50" :value="old('report_date', $todayDateDisplay)" readonly />
                            <x-input-error class="mt-2" :messages="$errors->get('report_date')" />
                        </div>
                        <div>
                            <x-input-label for="payment_location_id" value="Lokacija" />
                            <select id="payment_location_id" name="location_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Odaberi lokaciju</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" @selected((int) old('location_id', $locationId > 0 ? $locationId : 0) === $location->id)>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('location_id')" />
                        </div>
                        <div>
                            <x-input-label for="patient_service_id" value="Usluga" />
                            <select id="patient_service_id" name="service_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Odaberi uslugu</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}" data-price="{{ (float) $service->base_price }}" @selected((int) old('service_id', 0) === $service->id)>
                                        {{ $service->name }} ({{ number_format((float) $service->base_price, 2, ',', '.') }} KM)
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('service_id')" />
                        </div>
                        <div>
                            <x-input-label for="patient_doctor_id" value="Doktor / saradnik" />
                            <select id="patient_doctor_id" name="doctor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Bez odabira</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected((int) old('doctor_id', 0) === $doctor->id)>
                                        {{ $doctor->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('doctor_id')" />
                        </div>
                        <div class="flex items-center gap-3 rounded-md border border-gray-200 bg-gray-50 px-3 py-2">
                            <input id="patient_is_new_patient" name="is_new_patient" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked((bool) old('is_new_patient', false))>
                            <label for="patient_is_new_patient" class="text-sm text-gray-700">Novi pacijent</label>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <x-input-label for="patient_item_price" value="Cijena stavke" />
                            <x-text-input id="patient_item_price" name="item_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('item_price')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('item_price')" />
                        </div>
                        <div>
                            <x-input-label for="patient_payment_status" value="Status placanja" />
                            <select id="patient_payment_status" name="payment_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach (['neplaceno', 'placeno', 'djelimicno_placeno'] as $statusOption)
                                    <option value="{{ $statusOption }}" @selected(old('payment_status', 'neplaceno') === $statusOption)>
                                        {{ strtoupper($statusOption) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('payment_status')" />
                        </div>
                        <div>
                            <x-input-label for="patient_paid_amount" value="Placeni iznos" />
                            <x-text-input id="patient_paid_amount" name="paid_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('paid_amount')" />
                            <x-input-error class="mt-2" :messages="$errors->get('paid_amount')" />
                        </div>
                    </div>

                    <div id="patient_payment_method_wrap">
                        <x-input-label for="patient_payment_method" value="Nacin placanja" />
                        <select id="patient_payment_method" name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Odaberi nacin</option>
                            @foreach ($paymentMethods as $method)
                                @php
                                    $normalized = (string) \Illuminate\Support\Str::of($method->name)->lower()->ascii()->replace(' ', '_');
                                @endphp
                                <option value="{{ $normalized }}" @selected(old('payment_method') === (string) $normalized)>
                                    {{ $method->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
                    </div>

                    <div id="patient_unpaid_reason_wrap">
                        <x-input-label for="patient_unpaid_reason" value="Razlog neplacanja" />
                        <textarea id="patient_unpaid_reason" name="unpaid_reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('unpaid_reason') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('unpaid_reason')" />
                    </div>

                    <div>
                        <x-input-label for="patient_notes" value="Napomena" />
                        <textarea id="patient_notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <x-primary-button>Dodaj placanje u danasnji izvjestaj</x-primary-button>
                </form>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Broj pregleda</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $summary['exams_count'] }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Ukupan promet</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($summary['total_amount'], 2, ',', '.') }} KM</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-emerald-700">Naplaceno</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-800">{{ number_format($summary['paid_amount'], 2, ',', '.') }} KM</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-wide text-red-700">Dug</p>
                    <p class="mt-2 text-2xl font-semibold text-red-800">{{ number_format($summary['remaining_amount'], 2, ',', '.') }} KM</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Placanja po nacinu</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nacin placanja</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Stavki</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Naplaceno</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($paymentByMethod as $row)
                                    <tr>
                                        <td class="px-6 py-3 text-sm text-gray-800">{{ $row['method'] }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700">{{ $row['items_count'] }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700">{{ number_format($row['paid_amount'], 2, ',', '.') }} KM</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-6 text-center text-sm text-gray-500">Nema naplacenih stavki.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Nenaplacene stavke</h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 px-4 py-3">
                            <span>Potpuno neplaceno</span>
                            <strong>{{ $summary['fully_unpaid_count'] }} / {{ number_format($summary['fully_unpaid_amount'], 2, ',', '.') }} KM</strong>
                        </div>
                        <div class="flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 px-4 py-3">
                            <span>Djelimicno placeno (preostalo)</span>
                            <strong>{{ $summary['partially_paid_count'] }} / {{ number_format($summary['partially_paid_amount'], 2, ',', '.') }} KM</strong>
                        </div>
                        <div class="flex items-center justify-between rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                            <span>Ukupan dug</span>
                            <strong>{{ number_format($summary['remaining_amount'], 2, ',', '.') }} KM</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Svi pregledi i stavke</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Datum</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Lokacija</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Usluga</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Doktor</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Placanje</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Cijena</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Naplaceno</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Dug</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-800">{{ $item->dailyReport?->report_date?->format('d.m.Y') ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->dailyReport?->location?->name ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->service?->name ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->doctor?->full_name ?: '-' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-700">
                                        <div>{{ strtoupper((string) $item->payment_status) }}</div>
                                        <div class="text-gray-500">{{ $item->payment_method ?: '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $item->item_price, 2, ',', '.') }} KM</td>
                                    <td class="px-4 py-3 text-sm text-emerald-700">{{ number_format((float) $item->paid_amount, 2, ',', '.') }} KM</td>
                                    <td class="px-4 py-3 text-sm text-red-700">{{ number_format((float) $item->remaining_amount, 2, ',', '.') }} KM</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">Nema stavki za odabrani filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const serviceSelect = document.getElementById('patient_service_id');
            const itemPriceInput = document.getElementById('patient_item_price');
            const paidAmountInput = document.getElementById('patient_paid_amount');
            const paymentStatusSelect = document.getElementById('patient_payment_status');
            const paymentMethodWrap = document.getElementById('patient_payment_method_wrap');
            const unpaidReasonWrap = document.getElementById('patient_unpaid_reason_wrap');

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
        });
    </script>
</x-app-layout>
