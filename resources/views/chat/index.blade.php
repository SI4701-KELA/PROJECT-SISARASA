@extends($layout)

@section('title', 'Inbox Chat')

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Inbox Chat</h1>
        <p class="text-gray-500 font-medium mt-1">Percakapan langsung antara pembeli dan penjual.</p>
    </div>

    @if($contactList->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-20 bg-white rounded-3xl border border-gray-100/80 shadow-sm max-w-2xl mx-auto px-6">
            <div class="w-24 h-24 mx-auto bg-gray-50 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Belum Ada Percakapan</h3>
            <p class="text-sm text-gray-400 font-medium max-w-sm mx-auto">
                @if(auth()->user()->role === 'buyer')
                    Mulai chat dengan penjual melalui halaman detail toko.
                @else
                    Chat akan muncul saat pembeli mengirimkan pesan ke toko Anda.
                @endif
            </p>
        </div>
    @else
        <div class="bg-white rounded-3xl border border-gray-100/80 shadow-sm overflow-hidden divide-y divide-gray-50">
            @foreach($contactList as $item)
                <a href="{{ route('chat.show', $item->user->id) }}"
                   class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50/70 transition-colors group">

                    {{-- Avatar --}}
                    <div class="relative shrink-0">
                        <div class="w-12 h-12 rounded-2xl bg-[#2aab7f]/10 flex items-center justify-center text-[#2aab7f] font-black text-lg group-hover:scale-105 transition-transform">
                            {{ strtoupper(substr($item->user->name ?? 'U', 0, 1)) }}
                        </div>
                        @if($item->unreadCount > 0)
                            <div class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center ring-2 ring-white">
                                {{ $item->unreadCount > 9 ? '9+' : $item->unreadCount }}
                            </div>
                        @endif
                    </div>

                    {{-- Contact Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $item->user->name ?? 'User' }}</p>
                            <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded
                                {{ $item->user->role === 'seller' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600' }}">
                                {{ $item->user->role === 'seller' ? 'Penjual' : 'Pembeli' }}
                            </span>
                        </div>
                        @if($item->lastMessage)
                            <p class="text-xs text-gray-400 font-medium truncate mt-0.5 {{ $item->unreadCount > 0 ? 'text-gray-600 font-semibold' : '' }}">
                                @if($item->lastMessage->sender_id === auth()->id())
                                    <span class="text-gray-400">Anda: </span>
                                @endif
                                {{ Str::limit($item->lastMessage->message, 50) }}
                            </p>
                        @endif
                    </div>

                    {{-- Timestamp --}}
                    <div class="text-right shrink-0">
                        @if($item->lastMessage)
                            <p class="text-[10px] font-bold text-gray-400 uppercase">
                                {{ $item->lastMessage->created_at->diffForHumans(null, true) }}
                            </p>
                        @endif
                    </div>

                    {{-- Chevron --}}
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endforeach
        </div>
    @endif

</div>
@endsection
