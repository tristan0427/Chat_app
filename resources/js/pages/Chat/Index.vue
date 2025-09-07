<script setup lang="ts">
import {onMounted, onBeforeUnmount, reactive, computed, nextTick} from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

// Types
interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
}

interface Message {
    id: number;
    body: string;
    sender_id: number;
    created_at: string;
    sender?: { id: number; name: string };
}

const page = usePage();

// Reactive state
const state = reactive({
    users: [] as User[],
    activeType: 'global' as 'global' | 'direct' | 'profile',
    activeUserId: null as number | null,
    messages: [] as Message[],
    newMessage: '',
    lastId: 0,
    profileForm: {
        name: page.props.auth.user.name as string,
        email: page.props.auth.user.email as string,
        avatar: null as File | null,
        password: '',
        password_confirmation: '',
    },
});

let poller: ReturnType<typeof setInterval> | null = null;

const csrf = (): string => {
    const meta = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;
    return meta?.getAttribute('content') ?? '';
};

// Fetch users list
async function fetchUsers() {
    const res = await fetch('/chat/users', { credentials: 'same-origin' });
    state.users = await res.json();
}

// Deduplicate helper
function deduplicateMessages(newMessages: Message[]): Message[] {
    const existingIds = new Set(state.messages.map(m => m.id));
    return newMessages.filter(m => !existingIds.has(m.id));
}

// Fetch messages
async function fetchMessages({
                                 global = false,
                                 userId = null,
                                 after = null,
                             }: {
    global?: boolean;
    userId?: number | null;
    after?: number | null;
} = {}) {
    if (state.activeType === 'profile') return;

    const params = new URLSearchParams();
    if (global) params.set('global', '1');
    if (userId) params.set('user_id', String(userId));
    if (after) params.set('after', String(after));

    const res = await fetch('/chat/messages?' + params.toString(), { credentials: 'same-origin' });
    const data: Message[] = await res.json();

    if (after) {
        const newMessages = deduplicateMessages(data);
        for (const m of newMessages) {
            state.messages.push(m);
        }
    } else {
        state.messages = data;
    }

    if (state.messages.length) {
        state.lastId = Math.max(...state.messages.map(m => m.id));
        await nextTick();
        scrollToBottom();
    }
}

// Sidebar actions
function pickGlobal() {
    state.activeType = 'global';
    state.activeUserId = null;
    state.messages = [];
    state.lastId = 0;
    fetchMessages({ global: true });
    restartPolling();
}

function pickDirect(userId: number) {
    state.activeType = 'direct';
    state.activeUserId = userId;
    state.messages = [];
    state.lastId = 0;
    fetchMessages({ userId });
    restartPolling();
}

function pickProfile() {
    state.activeType = 'profile';
    stopPolling();
}

// Send message
async function sendMessage() {
    const body = state.newMessage.trim();
    if (!body) return;

    const payload = {
        body,
        receiver_id: state.activeType === 'direct' ? state.activeUserId : null,
    };

    const res = await fetch('/chat/messages', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf(),
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
    });

    if (res.ok) {
        const newMsg: Message = await res.json();
        state.newMessage = '';
        if (!state.messages.some(m => m.id === newMsg.id)) {
            state.messages.push(newMsg);
            state.lastId = newMsg.id;
            await nextTick();
            scrollToBottom();
        }
    }
}

// Profile form submit
async function updateProfile() {
    const formData = new FormData();
    formData.append('name', state.profileForm.name);
    formData.append('email', state.profileForm.email);

    if (state.profileForm.password) {
        formData.append('password', state.profileForm.password);
        formData.append('password_confirmation', state.profileForm.password_confirmation);
    }

    if (state.profileForm.avatar) {
        formData.append('avatar', state.profileForm.avatar);
    }

    const res = await fetch('/profile/update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf(),
        },
        body: formData,
    });

    if (res.ok) {
        try {
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                console.error('Profile update failed:', data);
            }
            // eslint-disable-next-line @typescript-eslint/no-unused-vars
        } catch (e) {
            window.location.reload();
        }
    } else {
        console.error('Profile update failed:', await res.text());
    }
}

// Polling
function startPolling() {
    stopPolling();
    poller = setInterval(() => {
        if (state.lastId > 0 && state.activeType !== 'profile') {
            fetchMessages({
                global: state.activeType === 'global',
                userId: state.activeUserId,
                after: state.lastId,
            });
        }
    }, 2000);
}
function stopPolling() {
    if (poller) {
        clearInterval(poller);
        poller = null;
    }
}
function restartPolling() {
    stopPolling();
    startPolling();
}

// Scroll
function scrollToBottom() {
    const box = document.getElementById('messagesBox');
    if (box) {
        setTimeout(() => {
            box.scrollTop = box.scrollHeight;
        }, 10);
    }
}

onMounted(async () => {
    await fetchUsers();
    pickGlobal();
    startPolling();
});
onBeforeUnmount(stopPolling);

const activeTitle = computed(() => {
    if (state.activeType === 'global') return 'Global Chat';
    if (state.activeType === 'profile') return 'Profile Settings';
    const u = state.users.find(u => u.id === state.activeUserId);
    return u ? `Chat with ${u.name}` : 'Direct Chat';
});

const avatarPreviewUrl = computed(() => {
    if (state.profileForm.avatar) {
        return (globalThis as any).URL.createObjectURL(state.profileForm.avatar);
    }
    return '';
});

</script>


<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 p-4">
        <!-- Reusing the same bubble background concept as Login -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute -left-20 top-1/4 w-40 h-40 bg-blue-500 rounded-full opacity-20"></div>
            <div class="absolute right-20 top-20 w-16 h-16 bg-cyan-400 rounded-full opacity-30"></div>
            <div class="absolute right-32 bottom-32 w-8 h-8 bg-blue-600 rounded-full opacity-40"></div>
            <div class="absolute -right-16 bottom-1/4 w-32 h-32 bg-cyan-500 rounded-full opacity-25"></div>
            <div class="absolute left-1/4 top-16 w-6 h-6 bg-blue-400 rounded-full opacity-50"></div>
            <div class="absolute left-16 bottom-20 w-12 h-12 bg-blue-300 rounded-full opacity-35"></div>
        </div>

        <Head title="Chat" />

        <div class="w-full max-w-6xl relative z-10">
            <div class="bg-white rounded-3xl shadow-xl p-4 md:p-6 border border-white/20">

                <div class="flex gap-4 h-960 md:h-[80vh]">
                    <aside class="basis-2/5 border-r pr-4">
                        <div class="mb-4">
                            <button class="w-50 text-left px-4 py-3 rounded-xl bg-pink-50 hover:bg-pink-100 font-medium text-black flex items-center gap-3"
                                    @click="pickProfile">
                                <img
                                    v-if="$page.props.auth.user.avatar"
                                    :src="`/storage/${$page.props.auth.user.avatar}`"
                                    class="w-7 h-7 rounded-full object-cover"
                                    alt="User avatar"
                                />
                                <span v-else class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-base font-bold text-indigo-700">
                                {{ $page.props.auth.user.name.substring(0,1).toUpperCase() }}</span>
                                Profile Settings
                            </button>
                        </div>
                        <div class="mb-4">
                            <button
                                class="w-full text-left text-black px-4 py-3 rounded-xl bg-indigo-50 hover:bg-indigo-100 font-medium"
                                @click="pickGlobal"
                            >
                                Global Chat
                            </button>
                        </div>

                        <h3 class="text-sm text-gray-500 mb-2">Direct Messages</h3>
                        <ul class="space-y-2 max-h-[65vh] overflow-auto pr-2">
                            <li v-for="u in state.users" :key="u.id">
                                <button
                                    class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50"
                                    @click="pickDirect(u.id)"
                                >
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center font-semibold text-indigo-700">
                                        {{ u.name?.substring(0,1).toUpperCase() }}
                                    </div>
                                    <div class="text-left">
                                        <div class="font-medium text-gray-800">{{ u.name }}</div>
                                        <div class="text-xs text-gray-500">Tap to chat</div>
                                    </div>
                                </button>
                            </li>
                        </ul>
                    </aside>

                    <section class="basis-3/5 flex flex-col">
                        <header class="px-2 pb-3 border-b mb-3">
                            <h2 class="text-lg font-semibold text-gray-800">{{ activeTitle }}</h2>
                        </header>

                        <template v-if="state.activeType !== 'profile'">
                            <div id="messagesBox" class="flex-1 overflow-auto px-1 space-y-3">
                                <div v-for="m in state.messages" :key="m.id" class="flex"
                                     :class="m.sender_id === $page.props.auth.user.id ? 'justify-end' : 'justify-start'">
                                    <div :class="m.sender_id === $page.props.auth.user.id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800'"
                                         class="max-w-[80%] px-4 py-2 rounded-2xl">
                                        <div v-if="m.sender_id !== $page.props.auth.user.id" class="text-xs font-semibold mb-1">
                                            {{ m.sender?.name }}
                                        </div>
                                        <div class="text-sm whitespace-pre-wrap">{{ m.body }}</div>
                                        <div class="text-[10px] opacity-70 mt-1">{{ new Date(m.created_at).toLocaleString() }}</div>
                                    </div>
                                </div>
                            </div>

                            <footer class="mt-4">
                                <form @submit.prevent="sendMessage" class="flex gap-2">
                                    <input v-model="state.newMessage" type="text" placeholder="Type a message..."
                                           class="flex-1 h-12 px-4 rounded-xl text-black bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none" />
                                    <button type="submit"
                                            class="h-12 px-5 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold hover:from-blue-600 hover:to-indigo-700">
                                        Send
                                    </button>
                                </form>
                            </footer>
                        </template>

                        <template v-else>
                            <form @submit.prevent="updateProfile" class="space-y-4 text-black">
                                <div class="flex flex-col items-center">
                                    <img
                                        v-if="state.profileForm.avatar"
                                        :src="avatarPreviewUrl"
                                        class="w-24 h-24 rounded-full object-cover mb-2"
                                        alt="Avatar preview"
                                    />
                                    <template v-else>
                                        <img
                                            v-if="$page.props.auth.user.avatar"
                                            :src="`/storage/${$page.props.auth.user.avatar}`"
                                            class="w-24 h-24 rounded-full object-cover mb-2"
                                            alt="User avatar" />
                                        <div v-else class="w-24 h-24 rounded-full bg-indigo-100 flex items-center justify-center text-4xl font-bold text-indigo-700 mb-2">
                                            {{ $page.props.auth.user.name.substring(0,1).toUpperCase() }}
                                        </div>
                                    </template>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="e => state.profileForm.avatar = (e.target as HTMLInputElement).files?.[0] ?? null"
                                        class="mt-2 text-sm"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input v-model="state.profileForm.name" type="text"
                                           class="mt-1 block w-full border rounded-lg px-3 py-2" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input v-model="state.profileForm.email" type="email"
                                           class="mt-1 block w-full border rounded-lg px-3 py-2" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">New Password</label>
                                    <input v-model="state.profileForm.password" type="password"
                                           class="mt-1 block w-full border rounded-lg px-3 py-2" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                    <input v-model="state.profileForm.password_confirmation" type="password"
                                           class="mt-1 block w-full border rounded-lg px-3 py-2" />
                                </div>

                                <button type="submit"
                                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-red-500 text-white rounded-lg hover:from-pink-600 hover:to-red-600">
                                    Save Changes
                                </button>
                            </form>
                        </template>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

#messagesBox { scroll-behavior: smooth; }
</style>
