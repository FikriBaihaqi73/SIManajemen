<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, Lock, LogIn, ArrowRight } from 'lucide-vue-next';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk" />

        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Selamat Datang Kembali</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Silakan masuk ke akun Anda untuk melanjutkan.</p>
        </div>

        <div v-if="status" class="mb-4 p-4 rounded-lg bg-green-50 dark:bg-green-900/30 text-sm font-medium text-green-600 dark:text-green-400">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Mail class="h-4 w-4 text-gray-400" />
                    </div>
                    <TextInput
                        id="email"
                        type="email"
                        class="pl-10 block w-full"
                        v-model="form.email"
                        required
                        autofocus
                        placeholder="email@perusahaan.com"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Password" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-bold text-indigo-600 hover:text-indigo-500 underline underline-offset-4"
                    >
                        Lupa Password?
                    </Link>
                </div>
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Lock class="h-4 w-4 text-gray-400" />
                    </div>
                    <TextInput
                        id="password"
                        type="password"
                        class="pl-10 block w-full"
                        v-model="form.password"
                        required
                        placeholder="••••••••"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Ingat Saya</span>
                </label>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center py-3 text-sm"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Masuk Sekarang <LogIn class="ml-2 w-4 h-4" />
                </PrimaryButton>
            </div>

            <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                Belum punya akun?
                <Link
                    :href="route('register')"
                    class="font-bold text-indigo-600 hover:text-indigo-500 underline decoration-2 underline-offset-4"
                >
                    Daftar di sini
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
