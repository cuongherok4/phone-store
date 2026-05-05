<div x-data="aiAssistant()" class="fixed bottom-6 right-6 z-[60]">
    {{-- Floating Bubble --}}
    <button @click="toggleChat()" 
            :class="open ? 'scale-0 opacity-0' : 'scale-100 opacity-100'"
            class="w-16 h-16 bg-gradient-to-tr from-brand-600 to-blue-500 text-white rounded-full shadow-2xl flex items-center justify-center transition-all duration-500 hover:rotate-12 group relative overflow-hidden">
        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
        <i class="fas fa-robot text-2xl relative z-10"></i>
        <span class="absolute -top-1 -right-1 flex h-4 w-4">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-4 w-4 bg-blue-500 border-2 border-white"></span>
        </span>
    </button>

    {{-- Chat Window --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-10 scale-95"
         class="absolute bottom-20 right-0 w-[380px] h-[550px] bg-white/95 backdrop-blur-xl rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-white/50 flex flex-col overflow-hidden"
         x-cloak>
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-brand-600 to-blue-600 p-5 text-white flex items-center justify-between shadow-lg relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white"></path>
                </svg>
            </div>
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/30">
                    <i class="fas fa-robot text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm tracking-wide">PhoneStore AI</h3>
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                        <span class="text-[10px] text-blue-100 font-medium">Trợ lý ảo thông minh</span>
                    </div>
                </div>
            </div>
            <button @click="open = false" class="w-8 h-8 hover:bg-white/20 rounded-full flex items-center justify-center transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Messages --}}
        <div x-ref="messageContainer" class="flex-1 overflow-y-auto p-5 space-y-5 scroll-smooth no-scrollbar">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user' ? 'bg-brand-600 text-white rounded-2xl rounded-tr-none' : 'bg-gray-100 text-gray-800 rounded-2xl rounded-tl-none'"
                         class="max-w-[85%] p-4 shadow-sm text-sm leading-relaxed relative group">
                        
                        <div x-html="formatMessage(msg.content)"></div>

                        {{-- Product Cards if any --}}
                        <template x-if="msg.products && msg.products.length > 0">
                            <div class="mt-4 space-y-3">
                                <template x-for="product in msg.products">
                                    <a :href="product.url" class="flex gap-3 bg-white p-2 rounded-xl border border-gray-100 hover:border-blue-300 hover:shadow-md transition group/card">
                                        <div class="w-16 h-16 bg-gray-50 rounded-lg overflow-hidden flex-shrink-0">
                                            <img :src="product.image" class="w-full h-full object-cover group-hover/card:scale-110 transition duration-300">
                                        </div>
                                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                                            <h4 class="text-xs font-bold text-gray-900 truncate" x-text="product.name"></h4>
                                            <p class="text-xs text-brand-600 font-bold mt-0.5" x-text="product.price"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <span class="text-[9px] opacity-50 block mt-2 text-right uppercase tracking-tighter" x-text="msg.time"></span>
                    </div>
                </div>
            </template>

            {{-- Typing Indicator --}}
            <div x-show="typing" class="flex justify-start" x-transition>
                <div class="bg-gray-100 p-4 rounded-2xl rounded-tl-none flex gap-1">
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="p-5 bg-gray-50/50 border-t border-gray-100">
            <form @submit.prevent="sendMessage()" class="relative">
                <input type="text" x-model="userInput" 
                       placeholder="Hỏi tôi về sản phẩm..."
                       class="w-full bg-white border border-gray-200 rounded-2xl py-3.5 pl-5 pr-14 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition outline-none shadow-inner">
                <button type="submit" 
                        :disabled="!userInput.trim()"
                        class="absolute right-2 top-2 bottom-2 px-4 bg-brand-600 text-white rounded-xl hover:bg-brand-700 disabled:opacity-50 disabled:grayscale transition shadow-lg shadow-brand-500/30">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            <div class="flex gap-2 mt-3 overflow-x-auto no-scrollbar">
                <template x-for="suggestion in suggestions">
                    <button @click="userInput = suggestion; sendMessage()" 
                            class="whitespace-nowrap text-[10px] font-bold bg-white text-gray-500 border border-gray-200 px-3 py-1.5 rounded-full hover:border-brand-600 hover:text-brand-600 transition">
                        <span x-text="suggestion"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function aiAssistant() {
    return {
        open: false,
        userInput: '',
        typing: false,
        messages: [
            {
                role: 'bot',
                content: 'Xin chào! Tôi là trợ lý ảo PhoneStore. Tôi có thể giúp gì cho bạn hôm nay? Bạn có thể hỏi tôi về cấu hình sản phẩm hoặc nhờ tôi tư vấn chọn máy phù hợp nhé! 📱',
                time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                products: []
            }
        ],
        suggestions: [
            'Tư vấn iPhone dưới 20 triệu',
            'Samsung tốt nhất hiện nay?',
            'Điện thoại chơi game mượt',
            'Sản phẩm mới nhất'
        ],

        toggleChat() {
            this.open = !this.open;
            if (this.open) {
                this.scrollToBottom();
            }
        },

        async sendMessage() {
            if (!this.userInput.trim()) return;

            const userMsg = this.userInput;
            this.messages.push({
                role: 'user',
                content: userMsg,
                time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
            });
            this.userInput = '';
            this.typing = true;
            this.scrollToBottom();

            try {
                const response = await fetch('{{ route('customer.ai.consult') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: userMsg })
                });

                const data = await response.json();
                
                setTimeout(() => {
                    this.typing = false;
                    this.messages.push({
                        role: 'bot',
                        content: data.response,
                        time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                        products: data.products
                    });
                    this.scrollToBottom();
                }, 800);

            } catch (error) {
                console.error(error);
                this.typing = false;
                this.messages.push({
                    role: 'bot',
                    content: 'Rất tiếc, hệ thống đang gặp chút sự cố. Bạn vui lòng thử lại sau giây lát nhé!',
                    time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
                });
            }
        },

        formatMessage(text) {
            // Simple markdown-like formatting for bold
            return text.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-brand-700">$1</strong>')
                       .replace(/\n/g, '<br>');
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messageContainer;
                container.scrollTop = container.scrollHeight;
            });
        }
    }
}
</script>
