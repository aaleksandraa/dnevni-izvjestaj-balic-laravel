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
                        <x-input-label for="patient_id" value="Pacijent" />
                        <select id="patient_id" name="patient_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Odaberi pacijenta</option>
                            @foreach ($patients as $patientOption)
                                <option value="{{ $patientOption->id }}" @selected((int) old('patient_id', (int) $item->patient_id) === $patientOption->id)>
                                    {{ $patientOption->full_name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('patient_id')" />
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
