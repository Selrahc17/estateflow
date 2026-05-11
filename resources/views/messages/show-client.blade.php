<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with {{ $user->name }} — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans flex flex-col h-screen">

@include('partials.client-nav')

<div class="flex-1 flex flex-col max-w-3xl mx-auto w-full px-6 py-4 overflow-hidden">

    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('messages.index') }}" class="text-sm text-gray-500 hover:text-indigo-600 transition">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
        <div class="flex items-center gap-2 ml-2">
            <div class="relative">
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center font-semibold text-white text-sm">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                @if($user->isOnline())
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 border-2 border-white rounded-full"></span>
                @endif
            </div>
            <span class="text-sm font-medium text-gray-700">{{ $user->name }}</span>
            <span class="text-xs text-gray-400 capitalize">({{ $user->role }})</span>
            <span class="text-xs {{ $user->isOnline() ? 'text-green-500' : 'text-gray-400' }}">
                {{ $user->isOnline() ? '● Online' : '○ Offline' }}
            </span>
        </div>
    </div>

    {{-- Messages Box --}}
    <div class="flex-1 bg-white rounded-xl shadow-sm overflow-y-auto p-6 mb-4" id="chat-box">
        <div id="messages-container" class="space-y-4">
            @forelse($messages as $msg)
                @php $isMine = $msg->from_user_id === auth()->id(); @endphp
                <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}" data-msg-id="{{ $msg->id }}">
                    <div class="max-w-sm flex flex-col gap-1 {{ $isMine ? 'items-end' : 'items-start' }}">
                        @if($msg->reservation)
                            <div class="text-xs text-indigo-500 mb-1">
                                <i class="fas fa-home mr-1"></i>Re: {{ $msg->reservation->property->title ?? 'Property' }}
                            </div>
                        @endif
                        <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed
                            {{ $isMine ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">
                            {{ $msg->message }}
                        </div>
                        @include('partials.message-attachment')
                        <div class="flex items-center gap-1">
                            <p class="text-xs text-gray-400">{{ $msg->created_at->format('M d, h:i A') }}</p>
                            @if($isMine)
                                @if($msg->read_at)
                                    <span class="text-xs text-green-500" title="Read {{ $msg->read_at->format('M d, h:i A') }}">
                                        <i class="fas fa-check-double"></i>
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400" title="Sent">
                                        <i class="fas fa-check"></i>
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-400 py-8" id="empty-state">
                    <i class="fas fa-comments text-3xl mb-2 block text-gray-200"></i>
                    <p class="text-sm">No messages yet. Say hello!</p>
                </div>
            @endforelse
        </div>

        {{-- Typing indicator --}}
        <div id="typing-indicator" class="hidden mt-3">
            <div class="flex justify-start">
                <div class="bg-gray-100 rounded-2xl rounded-bl-sm px-4 py-2.5 flex items-center gap-1">
                    <span class="text-xs text-gray-400 mr-1">{{ $user->name }} is typing</span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Send Form --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form id="send-form" class="space-y-3" enctype="multipart/form-data">
            @csrf
            @if($reservations->count())
            <select id="reservation-select" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">No linked reservation (general message)</option>
                @foreach($reservations as $res)
                    <option value="{{ $res->id }}">#{{ $res->id }} — {{ $res->property->title ?? 'Property' }} ({{ ucfirst($res->status) }})</option>
                @endforeach
            </select>
            @endif
            {{-- Attachment preview --}}
            <div id="attachment-preview" class="hidden items-center gap-2 px-3 py-2 bg-indigo-50 rounded-lg text-xs text-indigo-700">
                <i class="fas fa-paperclip"></i>
                <span id="attachment-name"></span>
                <button type="button" onclick="clearAttachment()" class="ml-auto text-red-400 hover:text-red-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex gap-2">
                <label for="attachment-input" class="cursor-pointer flex items-center justify-center w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl transition flex-shrink-0" title="Attach file">
                    <i class="fas fa-paperclip text-gray-500"></i>
                </label>
                <input type="file" id="attachment-input" name="attachment" class="hidden"
                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                    onchange="previewAttachment(this)">
                <textarea id="message-input" rows="2" placeholder="Type your message..."
                    class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl hover:bg-indigo-700 transition self-end">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <p class="text-xs text-gray-400">Press Enter to send · Shift+Enter for new line · Max 10MB</p>
        </form>
    </div>

</div>

<script>
function previewAttachment(input) {
    if (!input.files || !input.files[0]) return;
    document.getElementById('attachment-name').textContent = input.files[0].name;
    document.getElementById('attachment-preview').classList.remove('hidden');
    document.getElementById('attachment-preview').classList.add('flex');
}
function clearAttachment() {
    document.getElementById('attachment-input').value = '';
    document.getElementById('attachment-preview').classList.add('hidden');
    document.getElementById('attachment-preview').classList.remove('flex');
}

const POLL_URL    = "{{ route('messages.poll', $user) }}";
const SEND_URL    = "{{ route('messages.send', $user) }}";
const TYPING_URL  = "{{ route('messages.typing', $user) }}";
const IS_TYPING_URL = "{{ route('messages.is-typing', $user) }}";
const CSRF        = "{{ csrf_token() }}";
let lastMsgId     = {{ $messages->last()?->id ?? 0 }};
let lastReceivedId = {{ $messages->where('from_user_id', $user->id)->last()?->id ?? 0 }};
let pollInterval  = null;
let typingTimeout = null;
let isSending     = false;

function scrollBottom(smooth = false) {
    const box = document.getElementById('chat-box');
    box.scrollTo({ top: box.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
}
scrollBottom();

// Typing detection
document.getElementById('message-input').addEventListener('input', function() {
    fetch(TYPING_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
});

// Poll typing status every 2 seconds
async function checkTyping() {
    try {
        const res  = await fetch(IS_TYPING_URL);
        const data = await res.json();
        const indicator = document.getElementById('typing-indicator');
        const box = document.getElementById('chat-box');
        const atBottom = box.scrollHeight - box.scrollTop <= box.clientHeight + 60;
        indicator.classList.toggle('hidden', !data.typing);
        if (data.typing && atBottom) scrollBottom(true);
    } catch(e) {}
}
setInterval(checkTyping, 2000);

function buildBubble(msg) {
    const wrap = document.createElement('div');
    wrap.className = `flex ${msg.mine ? 'justify-end' : 'justify-start'}`;
    wrap.dataset.msgId = msg.id;
    const inner = document.createElement('div');
    inner.className = `max-w-sm flex flex-col gap-1 ${msg.mine ? 'items-end' : 'items-start'}`;
    if (msg.property) {
        const prop = document.createElement('div');
        prop.className = 'text-xs text-indigo-500 mb-1';
        prop.innerHTML = `<i class="fas fa-home mr-1"></i>Re: ${msg.property}`;
        inner.appendChild(prop);
    }
    const bubble = document.createElement('div');
    bubble.className = `px-4 py-2.5 rounded-2xl text-sm leading-relaxed ${msg.mine ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm'}`;
    bubble.textContent = msg.message;
    inner.appendChild(bubble);

    if (msg.attachment) {
        if (msg.attachment_type === 'image') {
            const a = document.createElement('a');
            a.href = msg.attachment; a.target = '_blank';
            const img = document.createElement('img');
            img.src = msg.attachment;
            img.className = 'max-w-xs rounded-xl mt-1 border border-gray-200 hover:opacity-90 transition cursor-pointer';
            a.appendChild(img); inner.appendChild(a);
        } else {
            const a = document.createElement('a');
            a.href = msg.attachment; a.target = '_blank';
            a.className = 'flex items-center gap-2 mt-1 px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs text-indigo-600 hover:bg-indigo-50 transition';
            a.innerHTML = `<i class="fas fa-file-alt"></i> ${msg.attachment_name} <i class="fas fa-download ml-auto text-gray-400"></i>`;
            inner.appendChild(a);
        }
    }
    const time = document.createElement('p');
    time.className = 'text-xs text-gray-400';
    time.textContent = msg.time;

    if (msg.mine) {
        const receipt = document.createElement('span');
        receipt.className = msg.read_at ? 'text-xs text-green-500' : 'text-xs text-gray-400';
        receipt.title = msg.read_at ? 'Read ' + msg.read_at : 'Sent';
        receipt.innerHTML = msg.read_at ? '<i class="fas fa-check-double"></i>' : '<i class="fas fa-check"></i>';
        const timeRow = document.createElement('div');
        timeRow.className = 'flex items-center gap-1';
        timeRow.appendChild(time);
        timeRow.appendChild(receipt);
        inner.appendChild(timeRow);
    } else {
        inner.appendChild(time);
    }
    wrap.appendChild(inner);
    return wrap;
}

async function pollMessages() {
    try {
        const res  = await fetch(`${POLL_URL}?after=${lastReceivedId}`);
        const msgs = await res.json();
        if (msgs.length > 0) {
            const container = document.getElementById('messages-container');
            const empty     = document.getElementById('empty-state');
            if (empty) empty.remove();
            const box = document.getElementById('chat-box');
            const atBottom = box.scrollHeight - box.scrollTop <= box.clientHeight + 50;
            msgs.forEach(msg => { container.appendChild(buildBubble(msg)); lastReceivedId = Math.max(lastReceivedId, msg.id); });
            if (atBottom) scrollBottom(true);
        }
    } catch (e) {}
}

pollInterval = setInterval(pollMessages, 5000);
document.addEventListener('visibilitychange', () => {
    if (document.hidden) { clearInterval(pollInterval); }
    else { pollMessages(); pollInterval = setInterval(pollMessages, 5000); }
});

document.getElementById('send-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (isSending) return;
    isSending = true;
    const input   = document.getElementById('message-input');
    const message = input.value.trim();
    const fileInput = document.getElementById('attachment-input');
    if (!message && (!fileInput.files || !fileInput.files[0])) return;
    const resSelect = document.getElementById('reservation-select');
    const resId     = resSelect ? resSelect.value : '';
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    clearInterval(pollInterval);
    try {
        const formData = new FormData();
        formData.append('_token', CSRF);
        formData.append('message', message);
        if (resId) formData.append('reservation_id', resId);
        if (fileInput.files && fileInput.files[0]) formData.append('attachment', fileInput.files[0]);
        const res = await fetch(SEND_URL, { method: 'POST', body: formData });
        if (res.ok) {
            const msg = await res.json();
            msg.time = new Date().toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
            input.value = '';
            clearAttachment();
            const container = document.getElementById('messages-container');
            const empty = document.getElementById('empty-state');
            if (empty) empty.remove();
            container.appendChild(buildBubble(msg));
            lastMsgId = Math.max(lastMsgId, msg.id);
            scrollBottom(true);
        }
    } catch (e) { alert('Failed to send. Please try again.'); }
    finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        input.focus();
        pollInterval = setInterval(pollMessages, 5000);
        isSending = false;
    }
});

document.getElementById('message-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (!isSending) document.getElementById('send-form').requestSubmit();
    }
});
</script>

</body>
</html>
