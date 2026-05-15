<x-guest-layout title="Sign in">

    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Welcome back</h1>
        <p class="text-gray-400 text-sm mt-1">Sign in to your Postara workspace.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div class="space-y-1">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required
                   value="{{ old('email') }}"
                   class="w-full px-3.5 py-2.5 border rounded-lg text-sm transition-colors focus:outline-none
                          {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:border-red-500' : 'border-gray-200 focus:border-black' }}">
            @error('email')
                <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="space-y-1">
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            </div>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                   class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-black transition-colors">
        </div>

        <div class="flex items-center gap-2 pt-1">
            <input id="remember" name="remember" type="checkbox"
                   class="w-4 h-4 rounded border-gray-300 text-black focus:ring-black">
            <label for="remember" class="text-sm text-gray-500 select-none">Keep me signed in</label>
        </div>

        <button type="submit"
                class="w-full bg-black text-white font-semibold py-2.5 px-4 rounded-lg text-sm hover:bg-gray-900 active:scale-[0.99] transition-all mt-2">
            Sign in
        </button>
    </form>

    <div class="mt-6 pt-6 border-t border-gray-100 text-center">
        <p class="text-sm text-gray-400">
            First time? Run the setup wizard at
            <a href="{{ route('setup') }}" class="font-semibold text-black hover:underline">/setup</a>
        </p>
    </div>

</x-guest-layout>
