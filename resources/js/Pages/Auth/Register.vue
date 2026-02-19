<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    User, 
    Mail, 
    Lock, 
    Building2, 
    Phone,
    ArrowRight
} from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    company_name: '',
    phone_number: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar Akun Baru" />

        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Daftar Akun Baru</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Mulai kelola struktur organisasi Anda hari ini.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Nama Lengkap -->
            <div>
                <InputLabel for="name" value="Nama Lengkap" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <User class="h-4 w-4 text-gray-400" />
                    </div>
                    <TextInput
                        id="name"
                        type="text"
                        class="pl-10 block w-full"
                        v-model="form.name"
                        required
                        autofocus
                        placeholder="John Doe"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Nama Perusahaan -->
            <div>
                <InputLabel for="company_name" value="Nama Perusahaan" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Building2 class="h-4 w-4 text-gray-400" />
                    </div>
                    <TextInput
                        id="company_name"
                        type="text"
                        class="pl-10 block w-full"
                        v-model="form.company_name"
                        required
                        placeholder="PT. Maju Mundur"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.company_name" />
            </div>

            <!-- Email -->
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
                        placeholder="email@perusahaan.com"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Nomor WhatsApp -->
            <div>
                <InputLabel for="phone_number" value="Nomor WhatsApp" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Phone class="h-4 w-4 text-gray-400" />
                    </div>
                    <TextInput
                        id="phone_number"
                        type="text"
                        class="pl-10 block w-full"
                        v-model="form.phone_number"
                        required
                        placeholder="0812xxxxxxxx"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.phone_number" />
            </div>

            <!-- Password -->
            <div>
                <InputLabel for="password" value="Password" />
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

            <!-- Confirm Password -->
            <div>
                <InputLabel for="password_confirmation" value="Konfirmasi Password" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Lock class="h-4 w-4 text-gray-400" />
                    </div>
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="pl-10 block w-full"
                        v-model="form.password_confirmation"
                        required
                        placeholder="••••••••"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center py-3 text-sm"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Daftar Sekarang <ArrowRight class="ml-2 w-4 h-4" />
                </PrimaryButton>
            </div>

            <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                Sudah punya akun?
                <Link
                    :href="route('login')"
                    class="font-bold text-indigo-600 hover:text-indigo-500 underline decoration-2 underline-offset-4"
                >
                    Masuk di sini
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
