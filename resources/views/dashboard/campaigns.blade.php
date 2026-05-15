<x-app-layout>
    <x-slot name="title">Campaigns</x-slot>
    <x-slot name="actions">
        <a href="{{ route('campaigns.create') }}"
           class="bg-black text-white text-sm font-semibold px-4 py-2 rounded hover:opacity-90 transition-opacity">
            New campaign
        </a>
    </x-slot>
    <livewire:campaigns.campaign-list />
</x-app-layout>
