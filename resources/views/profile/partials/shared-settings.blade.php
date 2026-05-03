<div class="max-w-4xl">
    <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-8">Pengaturan Akun</h1>

    <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-10 mb-8">
        <div class="mb-8 border-b border-gray-100 pb-6">
            <h2 class="text-xl font-bold text-terracotta mb-1">Informasi Profil</h2>
            <p class="text-sm text-gray-500 font-medium">Perbarui foto profil, informasi akun, dan email Anda.</p>
        </div>

        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('patch')

            {{-- Foto Profil --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Foto Profil (Opsional)</label>
                <div class="flex items-center gap-4">
                    <div class="shrink-0">
                        @if (auth()->user()->photo)
                            <img id="photo-preview" src="{{ asset('storage/' . auth()->user()->photo) }}" alt="{{ auth()->user()->name }}" class="w-16 h-16 rounded-full object-cover shadow-sm border border-gray-100">
                        @else
                            <div id="photo-preview-initials" class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center border border-gray-200 border-dashed">
                                <span class="text-xl font-bold text-gray-400">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            </div>
                            <img id="photo-preview" src="#" alt="" class="w-16 h-16 rounded-full object-cover shadow-sm border border-gray-100 hidden">
                        @endif
                    </div>
                    <div class="flex-1">
                        <input id="photo" name="photo" type="file" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-terracotta hover:file:bg-red-100 cursor-pointer transition-colors" onchange="previewPhoto(event)" />
                    </div>
                </div>
                @error('photo') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <script>
                function previewPhoto(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.getElementById('photo-preview');
                            const initials = document.getElementById('photo-preview-initials');
                            preview.src = e.target.result;
                            preview.classList.remove('hidden');
                            if (initials) initials.classList.add('hidden');
                        };
                        reader.readAsDataURL(file);
                    }
                }
            </script>

            {{-- Nama / Username --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nama / Username</label>
                <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" required
                    class="w-full bg-white border border-gray-200 focus:border-terracotta focus:ring-1 focus:ring-terracotta rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all">
                @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Nomor Telepon --}}
            <div>
                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Nomor Telepon</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', auth()->user()->phone) }}"
                    class="w-full bg-white border border-gray-200 focus:border-terracotta focus:ring-1 focus:ring-terracotta rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all">
                @error('phone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" name="email_display" id="email" value="{{ auth()->user()->email }}" disabled
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-500 font-medium cursor-not-allowed">
                <p class="mt-2 text-xs text-gray-400">Email tidak dapat diubah dari halaman ini.</p>
            </div>

            <div class="pt-4 flex items-center gap-4">
                <button type="submit" class="bg-terracotta hover:bg-[#a6402d] text-white font-bold py-3 px-8 rounded-xl shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-terracotta text-sm">
                    SIMPAN
                </button>
                @if (session('status') === 'profile-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm font-medium text-green-600">
                        Tersimpan.
                    </p>
                @endif
            </div>
        </form>
    </div>

    {{-- Ubah Password --}}
    <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-10 mb-8">
        <div class="mb-8 border-b border-gray-100 pb-6">
            <h2 class="text-xl font-bold text-terracotta mb-1">Ubah Password</h2>
            <p class="text-sm text-gray-500 font-medium">Gunakan kata sandi kuat. Kata sandi baru minimal 8 karakter.</p>
        </div>

        <form method="post" action="{{ route('password.update') }}" class="space-y-6">
            @csrf
            @method('put')

            <div>
                <label for="update_password_current_password" class="block text-sm font-semibold text-gray-700 mb-2">Password Saat Ini</label>
                <input id="update_password_current_password" name="current_password" type="password" required
                    class="w-full bg-white border border-gray-200 focus:border-terracotta focus:ring-1 focus:ring-terracotta rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all" autocomplete="current-password" />
                @error('current_password', 'updatePassword') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="update_password_password" class="block text-sm font-semibold text-gray-700 mb-2">Password Baru</label>
                <input id="update_password_password" name="password" type="password" required
                    class="w-full bg-white border border-gray-200 focus:border-terracotta focus:ring-1 focus:ring-terracotta rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all" autocomplete="new-password" />
                @error('password', 'updatePassword') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="update_password_password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" required
                    class="w-full bg-white border border-gray-200 focus:border-terracotta focus:ring-1 focus:ring-terracotta rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all" autocomplete="new-password" />
                @error('password_confirmation', 'updatePassword') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 flex items-center gap-4">
                <button type="submit" class="bg-terracotta hover:bg-[#a6402d] text-white font-bold py-3 px-8 rounded-xl shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-terracotta text-sm">
                    UBAH PASSWORD
                </button>
                @if (session('status') === 'password-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm font-medium text-green-600">
                        Tersimpan.
                    </p>
                @endif
            </div>
        </form>
    </div>

    {{-- Hapus Akun --}}
    <div class="bg-white rounded-[24px] border border-red-100 shadow-sm p-10 mb-8" x-data="{ showDeleteModal: false }">
        <div class="mb-8 border-b border-red-100 pb-6">
            <h2 class="text-xl font-bold text-red-600 mb-1">Hapus Akun</h2>
            <p class="text-sm text-red-400 font-medium">Sekali akun dihapus, semua sumber daya dan data akan terhapus secara permanen.</p>
        </div>

        <button @click="showDeleteModal = true" class="bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 font-bold py-3 px-8 rounded-xl shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 text-sm">
            HAPUS AKUN
        </button>

        {{-- Delete Modal --}}
        <div x-show="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>

                <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                    <div class="px-6 py-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-red-600">Apakah Anda yakin?</h3>
                        <button @click="showDeleteModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                        @csrf
                        @method('delete')
                        <p class="text-sm text-gray-500 mb-4">Sekali akun dihapus, semua sumber daya dan data akan terhapus secara permanen. Masukkan password Anda untuk mengkonfirmasi.</p>
                        
                        <div class="mb-6">
                            <label for="password" class="sr-only">Password</label>
                            <input id="password" name="password" type="password" class="w-full bg-white border border-gray-200 focus:border-red-500 focus:ring-1 focus:ring-red-500 rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all" placeholder="Password" />
                            @error('password', 'userDeletion') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showDeleteModal = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-sm transition-colors">Batal</button>
                            <button type="submit" class="py-2.5 px-6 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-sm shadow-sm transition-colors">Hapus Akun</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
