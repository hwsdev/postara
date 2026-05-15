<x-app-layout>
    <x-slot name="title">Contact Lists</x-slot>

    {{-- Tab nav --}}
    <div class="flex gap-0 border-b border-gray-200 mb-6 -mt-2">
        <a href="/contacts"
           class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                  {{ request()->is('contacts') ? 'border-black text-black' : 'border-transparent text-gray-400 hover:text-gray-700' }}">
            All contacts
        </a>
        <a href="/contacts/lists"
           class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                  {{ request()->is('contacts/lists') ? 'border-black text-black' : 'border-transparent text-gray-400 hover:text-gray-700' }}">
            Lists
        </a>
    </div>

    <livewire:contacts.contact-list-manager />
</x-app-layout>
