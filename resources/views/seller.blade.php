@component('layouts.seller')
    @slot('header')
        <h2 class="font-semibold text-xl text-terracotta leading-tight">
            {{ __('Mitra UMKM Profile') }}
        </h2>
    @endslot

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 font-bold text-lg">
                    {{ __("Halo, ini adalah halaman profil Anda sebagai Mitra UMKM!") }}
                </div>
            </div>
        </div>
    </div>
@endcomponent
