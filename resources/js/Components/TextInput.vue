<script setup>
import { onMounted, ref, computed } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';

const props = defineProps({
    type: {
        type: String,
        default: 'text',
    },
});

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);
const showPassword = ref(false);

const inputType = computed(() => {
    if (props.type === 'password') {
        return showPassword.value ? 'text' : 'password';
    }
    return props.type;
});

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="relative w-full">
        <input
            v-bind="$attrs"
            :type="inputType"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 pr-10"
            v-model="model"
            ref="input"
        />
        <button 
            v-if="type === 'password'"
            type="button"
            @click="showPassword = !showPassword"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none"
        >
            <Eye v-if="!showPassword" class="w-4 h-4" />
            <EyeOff v-else class="w-4 h-4" />
        </button>
    </div>
</template>
