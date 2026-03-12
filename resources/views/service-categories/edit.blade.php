<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Uredi kategoriju: {{ $serviceCategory->name }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('service-categories.update', $serviceCategory) }}">
                    @csrf
                    @method('PUT')
                    @include('service-categories._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
