<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Uredi kategoriju: {{ $findingCategory->name }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('finding-categories.update', $findingCategory) }}">
                    @csrf
                    @method('PUT')
                    @include('finding-categories._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
