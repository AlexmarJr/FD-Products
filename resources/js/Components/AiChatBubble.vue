<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import Swal from 'sweetalert2';

marked.setOptions({
    gfm: true,
    breaks: true,
    headerIds: false,
    mangle: false,
});

const isOpen = ref(false);
const isExpanded = ref(false);
const input = ref('');
const isSending = ref(false);
const messages = ref([
    {
        role: 'assistant',
        text: 'Oi! Envie sua pergunta.',
    },
]);
const SESSION_KEY_PREFIX = 'ai-chat-history';
const messagesEl = ref(null);
const page = usePage();
const TYPING_CHUNK_SIZE = 4;
const TYPING_DELAY_MS = 22;
const AUTO_SCROLL_THRESHOLD = 40;
const shouldAutoScroll = ref(true);

const sessionKey = computed(() => {
    const userId = page.props?.auth?.user?.id ?? 'guest';
    return `${SESSION_KEY_PREFIX}:${userId}`;
});

const quickPrompts = [
    'Me ajude a otimizar meu estoque de produtos.',
];

const canSend = computed(() => input.value.trim().length > 0);
const isAssistantTyping = computed(() => messages.value.some((message) => message.role === 'assistant' && message.isTyping));


function updateAutoScrollState() {
    const element = messagesEl.value;
    if (!element) {
        return;
    }

    shouldAutoScroll.value = element.scrollHeight - element.scrollTop - element.clientHeight <= AUTO_SCROLL_THRESHOLD;;
}

function handleMessagesScroll() {
    updateAutoScrollState();
}

function scrollToBottom(force = false) {
    nextTick(() => {
        const element = messagesEl.value;
        if (!element) {
            return;
        }

        if (!force && !shouldAutoScroll.value) {
            return;
        }

        element.scrollTop = element.scrollHeight;
        shouldAutoScroll.value = true;
    });
}

function saveHistoryToSession() {
    try {
        window.sessionStorage.setItem(sessionKey.value, JSON.stringify(messages.value));
    } catch {
    }
}

function loadHistoryFromSession() {
    try {
        const raw = window.sessionStorage.getItem(sessionKey.value);
        if (!raw) {
            return;
        }

        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed) && parsed.length > 0) {
            messages.value = parsed;
        }
    } catch {
    }
}

async function sendMessage(rawText = input.value) {
    const text = String(rawText || '').trim();

    if (!text || isSending.value) return;

    const userMessage = { role: 'user', text };
    messages.value.push(userMessage);
    saveHistoryToSession();
    input.value = '';
    scrollToBottom();

    isSending.value = true;

    try {
        const historyToSend = messages.value.slice(0, -1).slice(-12).map((m) => ({ role: m.role, text: m.text }));

        const response = await window.axios.post('/api/v1/ai-chat', {
            message: userMessage.text,
            history: historyToSend,
        });
        scrollToBottom(true);
        const aiText = response?.data?.data?.reply || '';
        await streamAssistantReply(aiText);
    } catch (error) {
        const fallback = error?.response?.data?.error || 'Erro ao falar com a IA. Tente novamente.';
        messages.value.push({ role: 'assistant', text: fallback });
        saveHistoryToSession();
    } finally {
        isSending.value = false;
        await nextTick();
    }
}

async function clearHistory() {
    const result = await Swal.fire({
        title: 'Apagar historico?',
        text: 'Essa acao vai limpar as mensagens desta sessao.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, apagar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ff5f1f',
        cancelButtonColor: '#334155',
        background: '#08111f',
        color: '#ffffff',
    });

    if (!result.isConfirmed) {
        return;
    }

    messages.value = [
        {
            role: 'assistant',
            text: 'Oi! Envie sua pergunta.',
        },
    ];
    saveHistoryToSession();
    scrollToBottom(true);
}

function toggleChat() {
    isOpen.value = !isOpen.value;
    if (!isOpen.value) {
        isExpanded.value = false;
    }
    if (isOpen.value) {
        scrollToBottom(true);
    }
}

function expandChat() {
    isOpen.value = true;
    isExpanded.value = true;
    scrollToBottom(true);
}

function collapseChat() {
    isExpanded.value = false;
}

function formatMessage(text) {
    try {
        const trimmed = String(text).trim();
        if (trimmed === '') return '';
        if ((trimmed.startsWith('{') && trimmed.endsWith('}')) || (trimmed.startsWith('[') && trimmed.endsWith(']'))) {
            const parsed = JSON.parse(trimmed);
            return JSON.stringify(parsed, null, 2);
        }
        return text;
    } catch {
        return text;
    }
}

function renderMarkdown(text) {
    try {
        const raw = marked.parse(String(text ?? ''));
        return DOMPurify.sanitize(raw);
    } catch {
        return String(text ?? '');
    }
}

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

async function streamAssistantReply(fullText) {
    const text = String(fullText ?? '');
    const assistantMessage = { role: 'assistant', text: '', html: '', isTyping: true };
    messages.value.push(assistantMessage);
    const messageIndex = messages.value.length - 1;
    scrollToBottom();

    let index = 0;
    while (index < text.length) {
        index = Math.min(index + TYPING_CHUNK_SIZE, text.length);

        messages.value.splice(messageIndex, 1, {
            ...messages.value[messageIndex],
            text: text.slice(0, index),
        });

        await nextTick();
        scrollToBottom();
        await sleep(TYPING_DELAY_MS);
    }

    messages.value.splice(messageIndex, 1, {
        role: 'assistant',
        text,
        html: renderMarkdown(text),
        isTyping: false,
    });

    saveHistoryToSession();
}

function usePrompt(prompt) {
    input.value = prompt;
}

onMounted(() => {
    loadHistoryFromSession();
    scrollToBottom(true);
});
</script>

<template>
    <div class="fixed bottom-5 right-5 z-[140] flex flex-col items-end gap-3 sm:bottom-6 sm:right-6">
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen && isExpanded"
                class="fixed inset-0 z-[138] bg-[#02070f]/80 backdrop-blur-sm"
                @click="collapseChat"
            ></div>
        </transition>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-y-4 scale-95 opacity-0"
            enter-to-class="translate-y-0 scale-100 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 scale-100 opacity-100"
            leave-to-class="translate-y-4 scale-95 opacity-0"
        >
            <div
                v-if="isOpen"
                class="flex flex-col overflow-hidden border border-white/12 bg-[#08111f]/95 shadow-[0_24px_70px_rgba(0,0,0,0.4)] backdrop-blur"
                :class="isExpanded
                    ? 'fixed inset-x-4 top-6 bottom-6 z-[139] mx-auto h-auto w-[min(64rem,calc(100vw-2rem))] rounded-3xl md:inset-x-8 md:top-10 md:bottom-10'
                    : 'h-[32rem] w-[min(24rem,calc(100vw-1.5rem))] rounded-[1.75rem]'"
                @click.stop
            >
                <div class="border-b border-white/10 bg-gradient-to-r from-[#ff8a2a] to-[#ff5f1f] px-4 py-4 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-[#08111f]/35 px-3 py-1.5 text-xs font-semibold tracking-wide text-white shadow-sm transition hover:bg-[#08111f]/55 focus:outline-none focus:ring-2 focus:ring-white/35"
                            @click="isExpanded ? collapseChat() : expandChat()"
                        >
                            <svg
                                v-if="!isExpanded"
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3" />
                            </svg>
                            <svg
                                v-else
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9H5V5M15 9h4V5M9 15H5v4M15 15h4v4" />
                            </svg>
                        </button>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-white/75">Assistente IA</p>
                            <h3 class="mt-1 text-lg font-semibold">Ajuda rapida</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="rounded-full border border-white/20 px-3 py-1 text-xs font-medium text-white/90 transition hover:bg-white/10"
                                @click="clearHistory"
                            >
                                Apagar
                            </button>
                            <button type="button" class="rounded-full border border-white/20 px-3 py-1 text-xs font-medium text-white/90 transition hover:bg-white/10" @click="toggleChat">
                                X
                            </button>
                        </div>
                    </div>
                </div>

                <div ref="messagesEl" class="flex-1 space-y-3 overflow-y-auto bg-[#091424] px-4 py-4" @scroll="handleMessagesScroll">
                    <div
                        v-for="(message, index) in messages"
                        :key="`${message.role}-${index}`"
                        class="flex"
                        :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
                    >
                        <div
                            class="max-w-[85%] rounded-2xl px-4 py-3 text-sm leading-6 shadow-sm"
                            :class="message.role === 'user'
                                ? 'bg-[#8fb0ff] text-[#07101e]'
                                : 'border border-white/10 bg-white/6 text-white'"
                        >
                            <template v-if="message.isTyping">
                                <div class="whitespace-pre-wrap">
                                    {{ message.text }}<span class="typing-caret">▍</span>
                                </div>
                            </template>
                            <template v-else-if="message.html">
                                <div class="ai-markdown" v-html="message.html"></div>
                            </template>
                            <template v-else-if="message.role === 'assistant'">
                                <div class="ai-markdown" v-html="renderMarkdown(message.text)"></div>
                            </template>
                            <template v-else-if="isExpanded && (String(message.text).includes('\n') || String(message.text).trim().startsWith('{') || String(message.text).trim().startsWith('['))">
                                <pre class="whitespace-pre-wrap font-mono text-sm">{{ formatMessage(message.text) }}</pre>
                            </template>
                            <template v-else>
                                {{ message.text }}
                            </template>
                        </div>
                    </div>

                    <div v-if="isSending && !isAssistantTyping" class="flex justify-start">
                        <div class="max-w-[85%] rounded-2xl border border-white/10 bg-white/6 px-4 py-3 text-sm text-white/80 shadow-sm">
                            <div class="flex items-center gap-2">
                                <span class="loader h-2.5 w-2.5 rounded-full bg-white/85"></span>
                                IA pensando...
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 border-t border-white/10 bg-[#0b1526] p-4">
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="prompt in quickPrompts"
                            :key="prompt"
                            type="button"
                            class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-medium text-white/75 transition hover:border-[#8fb0ff]/50 hover:bg-[#8fb0ff]/10 hover:text-white"
                            @click="usePrompt(prompt)"
                        >
                            {{ prompt }}
                        </button>
                    </div>

                    <form class="flex gap-2" @submit.prevent="sendMessage()">
                        <textarea
                            v-model="input"
                            placeholder="Digite sua mensagem..."
                            rows="2"
                            class="min-w-0 flex-1 resize-none rounded-2xl border border-white/10 bg-[#0f1b31] px-4 py-3 text-sm text-white outline-none transition placeholder:text-white/35 focus:border-[#ff8a2a]/60 focus:ring-2 focus:ring-[#ff8a2a]/20"
                            @keydown.enter.exact.prevent="sendMessage()"
                        />
                        <button
                            type="submit"
                            :disabled="!canSend || isSending"
                            class="rounded-2xl bg-[#ff8a2a] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#ff7a14] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ isSending ? 'Enviando...' : 'Enviar' }}
                        </button>
                    </form>
                </div>
            </div>
        </transition>

        <button
            type="button"
            class="group flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-[#ff8a2a] to-[#ff5f1f] text-white shadow-[0_20px_45px_rgba(255,122,20,0.35)] ring-4 ring-[#ff8a2a]/15 transition hover:scale-105 hover:shadow-[0_24px_55px_rgba(255,122,20,0.45)] focus:outline-none focus:ring-4 focus:ring-[#ff8a2a]/30"
            @click="toggleChat"
        >
            <svg v-if="!isOpen" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 5.94 2 10.8c0 2.79 1.45 5.27 3.74 6.89V22l4.06-2.25c.7.12 1.42.17 2.2.17 5.52 0 10-3.94 10-8.8S17.52 2 12 2zm-4 9.25h8v1.5H8v-1.5zm0-3h8v1.5H8v-1.5zm0 6h5v1.5H8v-1.5z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
            </svg>
        </button>
    </div>
</template>

<style scoped>
.typing-caret {
    display: inline-block;
    margin-left: 0.1rem;
    animation: typing-caret-blink 0.9s steps(1, end) infinite;
}

@keyframes typing-caret-blink {
    0%,
    49% {
        opacity: 1;
    }
    50%,
    100% {
        opacity: 0.2;
    }
}

.ai-markdown :deep(p) {
    margin: 0 0 0.75rem;
}

.ai-markdown :deep(p:last-child) {
    margin-bottom: 0;
}

.ai-markdown :deep(h1),
.ai-markdown :deep(h2),
.ai-markdown :deep(h3),
.ai-markdown :deep(h4) {
    margin: 0.9rem 0 0.5rem;
    font-weight: 700;
    line-height: 1.35;
}

.ai-markdown :deep(h1) {
    font-size: 1.15rem;
}

.ai-markdown :deep(h2) {
    font-size: 1.05rem;
}

.ai-markdown :deep(h3),
.ai-markdown :deep(h4) {
    font-size: 0.98rem;
}

.ai-markdown :deep(ul),
.ai-markdown :deep(ol) {
    margin: 0.6rem 0 0.9rem;
    padding-left: 1.2rem;
}

.ai-markdown :deep(li) {
    margin: 0.25rem 0;
}

.ai-markdown :deep(strong) {
    font-weight: 700;
    color: #f8fafc;
}

.ai-markdown :deep(em) {
    color: #dbeafe;
}

.ai-markdown :deep(a) {
    color: #93c5fd;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.ai-markdown :deep(code) {
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 0.45rem;
    background: rgba(15, 23, 42, 0.55);
    padding: 0.1rem 0.35rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 0.82em;
}

.ai-markdown :deep(pre) {
    margin: 0.7rem 0;
    overflow-x: auto;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 0.75rem;
    background: #020617;
    padding: 0.8rem;
}

.ai-markdown :deep(pre code) {
    border: 0;
    background: transparent;
    padding: 0;
    font-size: 0.84rem;
}

.ai-markdown :deep(blockquote) {
    margin: 0.7rem 0;
    border-left: 3px solid rgba(148, 163, 184, 0.7);
    padding: 0.2rem 0 0.2rem 0.75rem;
    color: #cbd5e1;
}

.ai-markdown :deep(hr) {
    margin: 0.8rem 0;
    border: 0;
    border-top: 1px solid rgba(148, 163, 184, 0.25);
}

.ai-markdown :deep(table) {
    margin: 0.7rem 0;
    width: 100%;
    border-collapse: collapse;
    font-size: 0.84rem;
}

.ai-markdown :deep(th),
.ai-markdown :deep(td) {
    border: 1px solid rgba(148, 163, 184, 0.35);
    padding: 0.35rem 0.5rem;
    text-align: left;
}

.ai-markdown :deep(th) {
    background: rgba(15, 23, 42, 0.75);
    font-weight: 600;
}

.loader {
    animation: loader-pulse 1s linear infinite;
}

@keyframes loader-pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(0.7); opacity: 0.6; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
