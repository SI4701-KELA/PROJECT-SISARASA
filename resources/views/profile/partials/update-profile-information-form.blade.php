<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informasi Profil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Perbarui nama, nomor telepon, dan foto profil Anda. Email tidak dapat diubah dari halaman ini.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nama')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required minlength="3" maxlength="100" autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email_display" type="email" class="mt-1 block w-full bg-gray-100 cursor-not-allowed" :value="$user->email" disabled autocomplete="username" />
            <p class="mt-1 text-xs text-gray-500">{{ __('Email hanya dapat dilihat; hubungi admin jika perlu diubah.') }}</p>
        </div>

        <div>
            <x-input-label for="phone" :value="__('Nomor telepon (opsional)')" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" maxlength="15" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label :value="__('Foto profil (opsional)')" />

            <div class="mt-3 flex items-center gap-4">
                {{-- Photo Preview --}}
                <div class="shrink-0">
                    @if ($user->photo)
                        <img id="photo-preview" src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" class="h-10 w-10 object-cover ring-1 ring-gray-300">
                    @else
                        <span id="photo-preview-initials" class="inline-flex items-center justify-center h-10 w-10 bg-gray-300 ring-1 ring-gray-300">
                            <span class="text-sm font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </span>
                        <img id="photo-preview" src="#" alt="" class="h-10 w-10 object-cover ring-1 ring-gray-300 hidden">
                    @endif
                </div>

                {{-- Upload Button --}}
                <label for="photo" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer transition ease-in-out duration-150">
                    {{ __('Ganti Foto') }}
                </label>
                <input id="photo" name="photo" type="file" accept="image/*" class="hidden" onchange="previewPhoto(event)" />
            </div>

            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
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

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
