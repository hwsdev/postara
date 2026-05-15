<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500 mt-0.5">Reusable HTML email templates for transactional and campaign emails.</p>
        </div>
        <a href="{{ route('templates.create') }}"
           class="bg-black text-white text-sm font-semibold px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
            + New template
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        @if ($templates->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-700">No templates yet</p>
                <p class="text-xs text-gray-400 mt-1">Create a template to use in campaigns or the transactional API.</p>
                <a href="{{ route('templates.create') }}"
                   class="inline-block mt-4 bg-black text-white text-sm font-semibold px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
                    Create your first template
                </a>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Subject</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Updated</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($templates as $template)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3.5 font-medium">{{ $template->name }}</td>
                            <td class="px-5 py-3.5">
                                <span @class([
                                    'inline-flex items-center px-2 py-0.5 text-xs font-semibold uppercase tracking-wide rounded-full',
                                    'text-blue-700 bg-blue-50 ring-1 ring-blue-200' => $template->type === 'transactional',
                                    'text-purple-700 bg-purple-50 ring-1 ring-purple-200' => $template->type === 'campaign',
                                ])>
                                    {{ $template->type }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-400 text-xs max-w-xs truncate">{{ $template->subject ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $template->updated_at->diffForHumans() }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('templates.edit', $template->id) }}"
                                       class="text-xs font-medium text-gray-500 hover:text-black px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                                        Edit
                                    </a>
                                    <button wire:click="delete({{ $template->id }})"
                                            wire:confirm="Delete template &quot;{{ $template->name }}&quot;?"
                                            class="text-xs font-medium text-gray-400 hover:text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
