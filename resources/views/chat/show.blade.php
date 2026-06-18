@extends($layout)

@section('title', 'Chat — ' . ($contact->name ?? 'User'))

@push('styles')
<style>
    #chat-body { scroll-behavior: smooth; }
    .bubble-in  { animation: bubbleIn 0.25s ease-out; }
    @keyframes bubbleIn {
        from { opacity: 0; transform: translateY(8px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>
@endpush

@section('content')
<div class="flex flex-col h-[calc(100vh-120px)] max-w-3xl mx-auto">

    {{-- Chat Header --}}
    <div class="bg-white rounded-t-3xl border border-gray-100/80 border-b-0 shadow-sm px-6 py-4 flex items-center gap-4 shrink-0">
        <a href="{{ route('chat.inbox') }}" class="text-gray-400 hover:text-gray-600 transition-colors shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="w-10 h-10 rounded-2xl bg-[#2aab7f]/10 flex items-center justify-center text-[#2aab7f] font-black text-sm shrink-0">
            {{ strtoupper(substr($contact->name ?? 'U', 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <h2 class="text-sm font-bold text-gray-900 truncate">{{ $contact->name ?? 'User' }}</h2>
            <p class="text-[10px] font-bold uppercase tracking-wider
                {{ $contact->role === 'seller' ? 'text-emerald-500' : 'text-blue-500' }}">
                {{ $contact->role === 'seller' ? 'Penjual' : 'Pembeli' }}
            </p>
        </div>
        <div class="flex items-center gap-1.5 text-xs text-gray-400 font-medium" id="chat-status">
            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
            Live
        </div>
    </div>

    {{-- Chat Body (Scrollable) --}}
    <div id="chat-body" class="flex-1 overflow-y-auto bg-white border-x border-gray-100/80 px-6 py-4 space-y-1">
        {{-- Pesan akan dirender oleh JavaScript --}}
        <div id="chat-messages"></div>

        {{-- Loading State --}}
        <div id="chat-loading" class="flex flex-col items-center justify-center py-12 text-center">
            <svg class="w-8 h-8 text-gray-300 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-xs text-gray-400 font-medium mt-3">Memuat percakapan...</p>
        </div>
    </div>

    {{-- Chat Input (Fixed Bottom) --}}
    <form id="chat-form" class="bg-white rounded-b-3xl border border-gray-100/80 border-t-0 shadow-sm px-4 py-3 flex items-center gap-3 shrink-0">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="text"
               id="chat-input"
               name="message"
               placeholder="Ketik pesan..."
               autocomplete="off"
               maxlength="2000"
               class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200/60 rounded-2xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2aab7f]/20 focus:border-[#2aab7f]/40 transition-all font-medium">
        <button type="submit"
                id="chat-send-btn"
                class="w-10 h-10 bg-[#2aab7f] hover:bg-[#239970] text-white rounded-2xl flex items-center justify-center transition-all duration-200 shadow-sm hover:shadow-md active:scale-95 shrink-0 disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
        </button>
    </form>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const contactId   = {{ $contact->id }};
    const authUserId  = {{ auth()->id() }};
    const fetchUrl    = @json(route('api.chat.fetch', $contact->id));
    const sendUrl     = @json(route('api.chat.send', $contact->id));
    const csrfToken   = document.querySelector('input[name="_token"]').value;

    const chatBody     = document.getElementById('chat-body');
    const chatMessages = document.getElementById('chat-messages');
    const chatLoading  = document.getElementById('chat-loading');
    const chatForm     = document.getElementById('chat-form');
    const chatInput    = document.getElementById('chat-input');
    const sendBtn      = document.getElementById('chat-send-btn');

    let lastMessageId  = 0;
    let isFirstLoad    = true;
    let isSending      = false;
    let lastRenderedDate = '';

    /**
     * Render pesan ke DOM tanpa kedip.
     * Hanya menambahkan pesan baru (id > lastMessageId).
     */
    function renderMessages(messages) {
        if (!messages || messages.length === 0) {
            if (isFirstLoad) {
                chatMessages.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-gray-500">Mulai percakapan!</p>
                        <p class="text-xs text-gray-400 mt-1">Kirim pesan pertamamu di bawah.</p>
                    </div>`;
            }
            chatLoading.style.display = 'none';
            isFirstLoad = false;
            return;
        }

        let hasNew = false;

        messages.forEach(function (msg) {
            if (msg.id <= lastMessageId) return;
            hasNew = true;

            // Date separator
            if (msg.date !== lastRenderedDate) {
                lastRenderedDate = msg.date;
                const dateSep = document.createElement('div');
                dateSep.className = 'flex items-center justify-center my-4';
                dateSep.innerHTML = `<span class="px-3 py-1 bg-gray-100 text-gray-400 text-[10px] font-bold rounded-full uppercase tracking-wider">${msg.date}</span>`;
                chatMessages.appendChild(dateSep);
            }

            // Bubble
            const wrapper = document.createElement('div');
            wrapper.className = `flex ${msg.is_mine ? 'justify-end' : 'justify-start'} mb-2 bubble-in`;

            const bubble = document.createElement('div');
            bubble.className = msg.is_mine
                ? 'max-w-[75%] px-4 py-2.5 rounded-2xl rounded-br-md bg-[#2aab7f] text-white text-sm font-medium shadow-sm'
                : 'max-w-[75%] px-4 py-2.5 rounded-2xl rounded-bl-md bg-gray-100 text-gray-800 text-sm font-medium';

            bubble.innerHTML = `
                <p class="leading-relaxed break-words">${msg.message}</p>
                <p class="text-[10px] mt-1 ${msg.is_mine ? 'text-white/60' : 'text-gray-400'} text-right font-semibold">
                    ${msg.time}${msg.is_mine ? (msg.is_read ? ' ✓✓' : ' ✓') : ''}
                </p>`;

            wrapper.appendChild(bubble);
            chatMessages.appendChild(wrapper);

            lastMessageId = msg.id;
        });

        chatLoading.style.display = 'none';
        isFirstLoad = false;

        if (hasNew) {
            scrollToBottom();
        }
    }

    function scrollToBottom() {
        requestAnimationFrame(function () {
            chatBody.scrollTop = chatBody.scrollHeight;
        });
    }

    /**
     * Fetch messages via AJAX (untuk polling).
     */
    async function loadMessages() {
        try {
            const res = await fetch(fetchUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                const data = await res.json();
                renderMessages(data);
            }
        } catch (err) {
            // Silently fail on network errors to avoid UI disruption
            console.warn('Chat polling error:', err);
        }
    }

    /**
     * Send message via AJAX POST.
     */
    async function sendMessage(e) {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text || isSending) return;

        isSending = true;
        sendBtn.disabled = true;
        chatInput.value = '';

        try {
            const res = await fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message: text })
            });

            if (res.ok) {
                // Langsung load untuk melihat pesan yang baru dikirim
                await loadMessages();
            } else {
                // Kembalikan teks jika gagal
                chatInput.value = text;
            }
        } catch (err) {
            chatInput.value = text;
            console.error('Send error:', err);
        } finally {
            isSending = false;
            sendBtn.disabled = false;
            chatInput.focus();
        }
    }

    // Event listeners
    chatForm.addEventListener('submit', sendMessage);

    // Enter to send (Shift+Enter for newline di masa depan)
    chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });

    // Initial load
    loadMessages();

    // AJAX Polling setiap 3 detik
    const pollInterval = setInterval(loadMessages, 3000);

    // Cleanup saat meninggalkan halaman
    window.addEventListener('beforeunload', function () {
        clearInterval(pollInterval);
    });
});
</script>
@endpush
