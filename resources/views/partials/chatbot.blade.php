{{-- EstateFlow Chatbot Widget --}}
<div id="chatbot-wrapper">

    {{-- Toggle Button --}}
    <button id="chatbot-toggle"
        onclick="toggleChat()"
        class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 transition flex items-center justify-center"
        title="Chat with us">
        <i class="fas fa-comments text-xl" id="chat-icon"></i>
    </button>

    {{-- Chat Window --}}
    <div id="chatbot-window"
        class="hidden fixed bottom-24 right-6 z-50 w-80 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden"
        style="height: 420px;">

        {{-- Header --}}
        <div class="bg-indigo-600 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-robot text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-white text-sm font-semibold">EstateFlow Assistant</p>
                    <p class="text-indigo-200 text-xs">Online</p>
                </div>
            </div>
            <button onclick="toggleChat()" class="text-white hover:text-indigo-200 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Messages --}}
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
            {{-- Welcome message --}}
            <div class="flex items-start gap-2">
                <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-robot text-indigo-600 text-xs"></i>
                </div>
                <div class="bg-white rounded-2xl rounded-tl-none px-3 py-2 shadow-sm max-w-xs">
                    <p class="text-sm text-gray-800">Hi! 👋 I'm the EstateFlow Assistant. How can I help you today?</p>
                </div>
            </div>
        </div>

        {{-- Quick Topics --}}
        <div id="quick-topics" class="px-3 py-2 bg-white border-t border-gray-100">
            <p class="text-xs text-gray-400 mb-2">Quick topics:</p>
            <div class="flex flex-wrap gap-1.5">
                <button onclick="askTopic('properties')" class="text-xs px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full hover:bg-indigo-100 transition">🏠 Properties</button>
                <button onclick="askTopic('reservation')" class="text-xs px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full hover:bg-indigo-100 transition">📋 Reservation</button>
                <button onclick="askTopic('payment')" class="text-xs px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full hover:bg-indigo-100 transition">💳 Payment</button>
                <button onclick="askTopic('contact')" class="text-xs px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full hover:bg-indigo-100 transition">📞 Contact</button>
                <button onclick="askTopic('documents')" class="text-xs px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full hover:bg-indigo-100 transition">📄 Documents</button>
                <button onclick="askTopic('hours')" class="text-xs px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full hover:bg-indigo-100 transition">🕐 Hours</button>
            </div>
        </div>

        {{-- Input --}}
        <div class="px-3 py-2 bg-white border-t border-gray-100 flex gap-2">
            <input type="text" id="chat-input" placeholder="Type a message..."
                onkeydown="if(event.key==='Enter') sendMessage()"
                class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <button onclick="sendMessage()"
                class="w-9 h-9 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition flex items-center justify-center flex-shrink-0">
                <i class="fas fa-paper-plane text-xs"></i>
            </button>
        </div>
    </div>
</div>

<script>
const responses = {
    properties: {
        keywords: ['property','properties','lot','house','unit','available','listing','browse','buy','price','cost','how much'],
        reply: "🏠 We have a variety of properties available including residential lots, house & lot, and condominiums. You can browse all available listings on our <a href='/estateflow/public/browse' class='text-indigo-600 underline'>Browse Properties</a> page. Prices vary per unit — feel free to check the listings for details!"
    },
    reservation: {
        keywords: ['reserve','reservation','book','booking','how to reserve','process','steps'],
        reply: "📋 To reserve a property:\n1. Browse available properties\n2. Click 'Reserve' on your chosen property\n3. Create an account or log in\n4. Fill out the reservation form\n5. An agent will confirm your reservation within 24 hours.\n\nYou can track your reservation status anytime from your dashboard."
    },
    payment: {
        keywords: ['payment','pay','cash','bank','transfer','installment','downpayment','receipt','proof'],
        reply: "💳 We accept the following payment methods:\n• Cash\n• Bank Transfer\n• Credit Card\n• Check\n\nYou can upload your payment proof/receipt when recording a payment. Our team will verify it within 1-2 business days."
    },
    contact: {
        keywords: ['contact','reach','call','email','agent','talk','speak','inquire','inquiry'],
        reply: "📞 You can reach us through:\n• Email: info@estateflow.com\n• Use the Contact Form on our homepage\n• Message us directly through the Messages feature (after logging in)\n\nOur agents are available Monday–Saturday, 8AM–5PM."
    },
    documents: {
        keywords: ['document','documents','requirements','id','contract','deed','title','file','upload'],
        reply: "📄 Required documents for reservation:\n• Valid Government ID\n• Proof of Income (for financing)\n• TIN Number\n\nAll documents can be uploaded digitally through your client portal. Our team will verify and notify you once approved."
    },
    hours: {
        keywords: ['hours','open','office','schedule','time','when','available','working'],
        reply: "🕐 Our office hours are:\n• Monday – Friday: 8:00 AM – 5:00 PM\n• Saturday: 8:00 AM – 12:00 PM\n• Sunday: Closed\n\nYou can still browse properties and submit inquiries online 24/7!"
    },
    greeting: {
        keywords: ['hi','hello','hey','good morning','good afternoon','good evening','howdy'],
        reply: "👋 Hello! Welcome to EstateFlow. I'm here to help you with property inquiries, reservations, payments, and more. What can I assist you with today?"
    },
    thanks: {
        keywords: ['thank','thanks','thank you','salamat','ty'],
        reply: "😊 You're welcome! Is there anything else I can help you with?"
    },
    default: "I'm not sure about that, but I'd be happy to connect you with one of our agents! You can use our <a href='/estateflow/public/#contact' class='text-indigo-600 underline'>Contact Form</a> or call us directly. Is there anything else I can help with?"
};

function toggleChat() {
    const win  = document.getElementById('chatbot-window');
    const icon = document.getElementById('chat-icon');
    win.classList.toggle('hidden');
    icon.className = win.classList.contains('hidden') ? 'fas fa-comments text-xl' : 'fas fa-times text-xl';
    if (!win.classList.contains('hidden')) {
        document.getElementById('chat-input').focus();
    }
}

function askTopic(topic) {
    const labels = {
        properties: 'Tell me about available properties',
        reservation: 'How do I make a reservation?',
        payment: 'What payment methods do you accept?',
        contact: 'How can I contact an agent?',
        documents: 'What documents do I need?',
        hours: 'What are your office hours?'
    };
    addMessage(labels[topic], 'user');
    setTimeout(() => {
        addMessage(responses[topic].reply, 'bot');
    }, 400);
}

function sendMessage() {
    const input = document.getElementById('chat-input');
    const text  = input.value.trim();
    if (!text) return;

    addMessage(text, 'user');
    input.value = '';

    setTimeout(() => {
        const reply = getResponse(text.toLowerCase());
        addMessage(reply, 'bot');
    }, 500);
}

function getResponse(text) {
    for (const [key, data] of Object.entries(responses)) {
        if (key === 'default') continue;
        if (data.keywords && data.keywords.some(kw => text.includes(kw))) {
            return data.reply;
        }
    }
    return responses.default;
}

function addMessage(text, sender) {
    const container = document.getElementById('chat-messages');
    const isBot     = sender === 'bot';

    const wrapper = document.createElement('div');
    wrapper.className = isBot ? 'flex items-start gap-2' : 'flex items-start gap-2 justify-end';

    const bubble = document.createElement('div');
    bubble.className = isBot
        ? 'bg-white rounded-2xl rounded-tl-none px-3 py-2 shadow-sm max-w-xs text-sm text-gray-800'
        : 'bg-indigo-600 rounded-2xl rounded-tr-none px-3 py-2 max-w-xs text-sm text-white';

    // Handle newlines
    bubble.innerHTML = text.replace(/\n/g, '<br>');

    if (isBot) {
        const avatar = document.createElement('div');
        avatar.className = 'w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5';
        avatar.innerHTML = '<i class="fas fa-robot text-indigo-600 text-xs"></i>';
        wrapper.appendChild(avatar);
    }

    wrapper.appendChild(bubble);
    container.appendChild(wrapper);
    container.scrollTop = container.scrollHeight;
}
</script>
