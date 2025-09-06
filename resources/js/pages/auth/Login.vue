<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle, Eye, EyeOff, Mail, Lock } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const showPassword = ref(false);
const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 p-4">
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute -left-20 top-1/4 w-40 h-40 bg-blue-500 rounded-full opacity-20"></div>
            <div class="absolute right-20 top-20 w-16 h-16 bg-cyan-400 rounded-full opacity-30"></div>
            <div class="absolute right-32 bottom-32 w-8 h-8 bg-blue-600 rounded-full opacity-40"></div>
            <div class="absolute -right-16 bottom-1/4 w-32 h-32 bg-cyan-500 rounded-full opacity-25"></div>
            <div class="absolute left-1/4 top-16 w-6 h-6 bg-blue-400 rounded-full opacity-50"></div>
            <div class="absolute left-16 bottom-20 w-12 h-12 bg-blue-300 rounded-full opacity-35"></div>
        </div>
        <Head title="Sign In" />

        <div class="w-full max-w-md relative z-10">

            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <span class="text-white font-bold text-xl">M</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Welcome Back</h1>
                <p class="text-gray-600 text-sm">Sign in to your account</p>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-3xl shadow-xl p-8 backdrop-blur-sm border border-white/20">
                <div v-if="status" class="mb-6 text-center text-sm font-medium text-green-600 bg-green-50 p-3 rounded-xl border border-green-200">
                    {{ status }}
                </div>

                <Form
                    v-bind="AuthenticatedSessionController.store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="space-y-5"
                >
                    <!-- Email -->
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <Mail class="h-5 w-5" />
                        </div>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            placeholder="Email address"
                            class="h-14 pl-12 pr-4 bg-gray-50 border-0 text-gray-800 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 rounded-2xl transition-all duration-200 text-base"
                        />
                        <InputError :message="errors.email" class="text-red-500 text-sm mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <Lock class="h-5 w-5" />
                        </div>
                        <Input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="Password"
                            class="h-14 pl-12 pr-12 bg-gray-50 border-0 text-gray-800 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 rounded-2xl transition-all duration-200 text-base"
                        />
                        <button
                            type="button"
                            @click="togglePasswordVisibility"
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                            :tabindex="-1"
                        >
                            <Eye v-if="!showPassword" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                        <InputError :message="errors.password" class="text-red-500 text-sm mt-2" />
                    </div>

                    <!-- Remember me and Forgot password -->
                    <div class="flex items-center justify-between py-2">
                        <Label for="remember" class="flex items-center space-x-3 cursor-pointer group">
                            <Checkbox
                                id="remember"
                                name="remember"
                                :tabindex="3"
                                class="border-2 border-gray-300 data-[state=checked]:bg-blue-500 data-[state=checked]:border-blue-500 rounded-md"
                            />
                            <span class="text-sm text-gray-600 group-hover:text-gray-800 transition-colors">Remember me</span>
                        </Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm text-indigo-600 hover:text-blue-700 transition-colors font-medium"
                            :tabindex="5"
                        >
                            Forgot password?
                        </TextLink>
                    </div>

                    <Button
                        type="submit"
                        class="w-full h-14 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold transition-all duration-300 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-[0.98] text-base"
                        :tabindex="4"
                        :disabled="processing"
                    >
                        <LoaderCircle v-if="processing" class="h-5 w-5 animate-spin mr-2" />
                        <span v-else>Sign In</span>
                    </Button>
                </Form>

                <div class="flex items-center my-6">
                    <div class="flex-1 border-t border-gray-200"></div>
                    <div class="flex-1 border-t border-gray-200"></div>
                </div>


                <p class="text-center text-gray-600 text-sm">
                    Don't have an account?
                    <TextLink :href="register()" class="text-indigo-600 hover:text-blue-700 font-medium transition-colors ml-1">
                        Sign up
                    </TextLink>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
input::placeholder {
    color: #9CA3AF;
}

input {
    background: #F9FAFB;
    border: none;
}

input:focus {
    background: white;
    box-shadow: 0 0 0 2px #3B82F6;
    outline: none;
}

.group:focus-within input {
    background: white;
    box-shadow: 0 0 0 2px #3B82F6;
}

* {
    transition-duration: 200ms;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

</style>
