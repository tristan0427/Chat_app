<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle, Eye, EyeOff, User, Mail, Lock } from 'lucide-vue-next';
import { ref } from 'vue';

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const togglePasswordConfirmationVisibility = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
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
        <Head title="Create Account" />

        <div class="w-full max-w-md relative z-10">
            <!-- Logo/Brand Area -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <span class="text-white font-bold text-xl">M</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Create Account</h1>
                <p class="text-gray-600 text-sm">Join us today and get started</p>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-3xl shadow-xl p-8 backdrop-blur-sm border border-white/20">
                <Form
                    v-bind="RegisteredUserController.store.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="space-y-5"
                >
                    <!-- Name -->
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <User class="h-5 w-5" />
                        </div>
                        <Input
                            id="name"
                            type="text"
                            name="name"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="name"
                            placeholder="Full name"
                            class="h-14 pl-12 pr-4 bg-gray-50 border-0 text-gray-800 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 rounded-2xl transition-all duration-200 text-base"
                        />
                        <InputError :message="errors.name" class="text-red-500 text-sm mt-2" />
                    </div>

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
                            :tabindex="2"
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
                            :tabindex="3"
                            autocomplete="new-password"
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

                    <!-- Password Confirmation -->
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <Lock class="h-5 w-5" />
                        </div>
                        <Input
                            id="password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            name="password_confirmation"
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                            class="h-14 pl-12 pr-12 bg-gray-50 border-0 text-gray-800 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 rounded-2xl transition-all duration-200 text-base"
                        />
                        <button
                            type="button"
                            @click="togglePasswordConfirmationVisibility"
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                            :tabindex="-1"
                        >
                            <Eye v-if="!showPasswordConfirmation" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                        <InputError :message="errors.password_confirmation" class="text-red-500 text-sm mt-2" />
                    </div>

                    <!-- Submit button -->
                    <Button
                        type="submit"
                        class="w-full h-14 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold transition-all duration-300 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-[0.98] text-base mt-6"
                        :tabindex="5"
                        :disabled="processing"
                    >
                        <LoaderCircle v-if="processing" class="h-5 w-5 animate-spin mr-2" />
                        <span v-else>Create Account</span>
                    </Button>
                </Form>

                <!-- Social Login Divider -->
                <div class="flex items-center my-6">
                    <div class="flex-1 border-t border-gray-200"></div>
                    <div class="flex-1 border-t border-gray-200"></div>
                </div>

                <p class="text-center text-gray-600 text-sm">
                    Already have an account?
                    <TextLink :href="login()" class="text-indigo-600 hover:text-blue-700 font-medium transition-colors ml-1">
                        Sign in
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

</style>
