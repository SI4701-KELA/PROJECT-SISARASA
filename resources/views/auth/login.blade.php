<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs text-gray-500 mb-1">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Masukan Email"
                    class="pl-9 block w-full rounded-sm border-gray-200 shadow-sm focus:border-teal focus:ring-teal sm:text-xs py-2.5 placeholder-gray-300">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs text-gray-500 mb-1">Kata Sandi</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukan Kata Sandi"
                    class="pl-9 block w-full rounded-sm border-gray-200 shadow-sm focus:border-teal focus:ring-teal sm:text-xs py-2.5 placeholder-gray-300">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-sm shadow-sm text-sm font-medium text-white bg-teal hover:bg-tealHover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal transition-colors duration-200">
                Login
            </button>
        </div>

        <div class="text-center text-xs text-gray-400 mt-6">
            Belum punya akun ? <a href="{{ route('register') }}" class="font-bold text-teal hover:text-tealHover transition-colors">Register</a>
        </div>
    </form>
</x-guest-layout>
