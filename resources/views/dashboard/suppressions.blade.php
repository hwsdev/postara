<x-app-layout>
    <x-slot name="title">Suppression List</x-slot>

    @php
        $suppressions = \App\Models\Suppression::where('workspace_id', session('current_workspace_id'))
            ->latest()
            ->paginate(50);
    @endphp

    <div class="bg-white border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-sm">Suppressed addresses</h2>
            <p class="text-xs text-gray-400 mt-0.5">Emails to these addresses will be blocked automatically.</p>
        </div>

        @if ($suppressions->isEmpty())
            <div class="px-6 py-12 text-center text-gray-400 text-sm">
                No suppressed addresses yet.
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Added</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suppressions as $suppression)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="px-6 py-3 font-mono text-xs">{{ $suppression->email }}</td>
                            <td class="px-6 py-3">
                                <span class="text-xs font-semibold uppercase tracking-wide text-red-600">
                                    {{ str_replace('_', ' ', $suppression->reason) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-400 text-xs">{{ $suppression->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-3 text-right">
                                <form method="POST" action="#" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-gray-400 hover:text-red-600 transition-colors">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $suppressions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
