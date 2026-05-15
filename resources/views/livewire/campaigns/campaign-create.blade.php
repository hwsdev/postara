<div class="max-w-2xl">
    <form wire:submit="save" class="space-y-6">

        <div class="bg-white border border-gray-100 p-6 space-y-4">
            <h3 class="font-semibold text-sm border-b border-gray-100 pb-3">Campaign details</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Campaign name</label>
                <input wire:model="name" type="text" placeholder="e.g. May Newsletter"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject line</label>
                <input wire:model="subject" type="text" placeholder="Your email subject"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors">
                @error('subject') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Preview text <span class="text-gray-400 font-normal">(optional)</span></label>
                <input wire:model="previewText" type="text" placeholder="Short preview shown in inbox"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors">
            </div>
        </div>

        <div class="bg-white border border-gray-100 p-6 space-y-4">
            <h3 class="font-semibold text-sm border-b border-gray-100 pb-3">Sender</h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From name</label>
                    <input wire:model="fromName" type="text" placeholder="Acme Inc"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors">
                    @error('fromName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From email</label>
                    <input wire:model="fromEmail" type="email" placeholder="hello@mail.yourdomain.com"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors">
                    @error('fromEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 p-6 space-y-4">
            <h3 class="font-semibold text-sm border-b border-gray-100 pb-3">Audience & template</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact list</label>
                <select wire:model="contactListId"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors bg-white">
                    <option value="">Select a list...</option>
                    @foreach ($contactLists as $list)
                        <option value="{{ $list->id }}">{{ $list->name }}</option>
                    @endforeach
                </select>
                @error('contactListId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Template</label>
                <select wire:model="templateId"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors bg-white">
                    <option value="">Select a template...</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
                @error('templateId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="bg-white border border-gray-100 p-6 space-y-4">
            <h3 class="font-semibold text-sm border-b border-gray-100 pb-3">Schedule</h3>

            <label class="flex items-center gap-3 cursor-pointer">
                <input wire:model.live="scheduleForLater" type="checkbox" class="rounded border-gray-300">
                <span class="text-sm font-medium text-gray-700">Schedule for later</span>
            </label>

            @if ($scheduleForLater)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Send at</label>
                    <input wire:model="scheduledAt" type="datetime-local"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors">
                    @error('scheduledAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex items-center gap-2 bg-black text-white font-semibold px-6 py-2.5 rounded text-sm hover:opacity-90 transition-opacity disabled:opacity-60">
                <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span wire:loading wire:target="save">{{ $scheduleForLater ? 'Scheduling…' : 'Saving…' }}</span>
                <span wire:loading.remove wire:target="save">{{ $scheduleForLater ? 'Schedule campaign' : 'Save as draft' }}</span>
            </button>
            <a href="{{ route('campaigns.index') }}"
               class="border border-gray-200 font-medium px-6 py-2.5 rounded text-sm hover:bg-gray-50 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
