<x-app-layout>
    <x-slot name="title">Contact List</x-slot>

    {{-- Tab nav --}}
    <div class="flex gap-0 border-b border-gray-200 mb-6 -mt-2">
        <a href="/contacts"
           class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-700 transition-colors">
            All contacts
        </a>
        <a href="/contacts/lists"
           class="px-4 py-2.5 text-sm font-medium border-b-2 border-black text-black transition-colors">
            Lists
        </a>
    </div>

    <livewire:contacts.contact-list-detail :list-id="$listId" />
</x-app-layout>
