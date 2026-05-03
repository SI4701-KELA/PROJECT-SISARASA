<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-3">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs text-gray-500 mb-1">Nama</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Budi"
                    class="pl-9 block w-full rounded-sm border-gray-200 shadow-sm focus:border-teal focus:ring-teal sm:text-xs py-2.5 placeholder-gray-300">
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Phone -->
        <div>
            <label for="phone" class="block text-xs text-gray-500 mb-1">Nomor Telepon (Opsional)</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="1234567890"
                    class="pl-9 block w-full rounded-sm border-gray-200 shadow-sm focus:border-teal focus:ring-teal sm:text-xs py-2.5 placeholder-gray-300">
            </div>
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs text-gray-500 mb-1">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="Masukan Email"
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
                <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 Karakter"
                    class="pl-9 block w-full rounded-sm border-gray-200 shadow-sm focus:border-teal focus:ring-teal sm:text-xs py-2.5 placeholder-gray-300">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs text-gray-500 mb-1">Konfirmasi Kata Sandi</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Konfirmasi Kata Sandi"
                    class="pl-9 block w-full rounded-sm border-gray-200 shadow-sm focus:border-teal focus:ring-teal sm:text-xs py-2.5 placeholder-gray-300">
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Role -->
        <div>
            <label for="role" class="block text-xs text-gray-500 mb-1">Daftar Sebagai</label>
            <select id="role" name="role" required class="block w-full rounded-sm border-gray-200 shadow-sm focus:border-teal focus:ring-teal sm:text-xs py-2.5 text-gray-500">
                <option value="" disabled selected>Pilih :</option>
                <option value="buyer" @if(old('role') == 'buyer') selected @endif>Pengguna</option>
                <option value="seller" @if(old('role') == 'seller') selected @endif>Seller</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <div class="pt-3">
            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-sm shadow-sm text-sm font-medium text-white bg-teal hover:bg-tealHover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal transition-colors duration-200">
                Register
            </button>
        </div>

        <div class="text-center text-xs text-gray-400 mt-5">
            Sudah Punya Akun ? <a href="{{ route('login') }}" class="font-bold text-teal hover:text-tealHover transition-colors">Klik Di sini</a>
        </div>
    </form>
</x-guest-layout>
