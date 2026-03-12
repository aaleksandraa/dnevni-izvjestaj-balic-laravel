<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Podesavanja
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-600">
                    Centralizovano upravljanje administrativnim modulima sistema.
                </p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                @foreach ($modules as $module)
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $module['title'] }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ $module['description'] }}</p>
                        <a
                            href="{{ $module['route'] }}"
                            class="mt-4 inline-flex items-center rounded-md border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50"
                        >
                            {{ $module['action'] }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
