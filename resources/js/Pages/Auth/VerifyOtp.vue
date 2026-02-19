<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { KeyRound, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    email: String,
});

const form = useForm({
    email: props.email,
    otp: '',
});

const submit = () => {
    form.post(route('otp.verify'), {
        onFinish: () => form.reset('otp'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi OTP" />

        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Verifikasi OTP</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">
                Kami telah mengirimkan kode OTP ke email <span class="font-bold text-gray-900 dark:text-white">{{ email }}</span>
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="otp" value="Masukkan Kode OTP" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <KeyRound class="h-5 w-5 text-gray-400" />
                    </div>
                    <TextInput
                        id="otp"
                        type="text"
                        class="pl-10 block w-full text-center text-2xl tracking-[1em] font-bold"
                        v-model="form.otp"
                        required
                        autofocus
                        maxlength="6"
                        placeholder="000000"
                    />
                </div>
                <InputError class="mt-2 text-center" :message="form.errors.otp" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center py-3 text-sm"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Verifikasi Akun <ArrowRight class="ml-2 w-4 h-4" />
                </PrimaryButton>
            </div>

            <div class="text-center text-sm">
                <p class="text-gray-600 dark:text-gray-400">
                    Tidak menerima kode? 
                    <button type="button" class="font-bold text-indigo-600 hover:text-indigo-500">Kirim Ulang</button>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
