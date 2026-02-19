<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { BarChart2, Filter, Crown, Trophy, Medal } from 'lucide-vue-next';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    performances: Array,
    filters: Object
});

const form = ref({
    month: props.filters.month,
    year: props.filters.year
});

const applyFilter = () => {
    router.get(route('tasks.overview'), form.value, { preserveState: true });
};

// Simple Computation for Chart Scaling
const maxPoints = computed(() => {
    if (props.performances.length === 0) return 10;
    return Math.max(...props.performances.map(p => p.points)) * 1.2; // Add buffer
});

const months = [
    { value: '01', label: 'Januari' },
    { value: '02', label: 'Februari' },
    { value: '03', label: 'Maret' },
    { value: '04', label: 'April' },
    { value: '05', label: 'Mei' },
    { value: '06', label: 'Juni' },
    { value: '07', label: 'Juli' },
    { value: '08', label: 'Agustus' },
    { value: '09', label: 'September' },
    { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' },
    { value: '12', label: 'Desember' }
];

const years = Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - i);

// Top 3 Ranking
const topPerformers = computed(() => {
    return [...props.performances]
        .sort((a, b) => b.points - a.points)
        .slice(0, 3);
});
</script>

<template>
    <Head title="Task Overview" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-2">
                <BarChart2 class="w-6 h-6 text-indigo-600" />
                <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                    Task Overview
                </h2>
            </div>
        </template>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            
           

            <!-- Filters -->
            <div class="flex flex-wrap gap-4 items-end mb-8 pb-6 border-b border-gray-100 dark:border-gray-700">
                <div class="w-40">
                    <InputLabel value="Bulan" />
                    <select v-model="form.month" class="w-full mt-1 border-gray-300 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-indigo-500">
                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                </div>
                <div class="w-32">
                    <InputLabel value="Tahun" />
                    <select v-model="form.year" class="w-full mt-1 border-gray-300 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-indigo-500">
                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
                <button @click="applyFilter" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center mb-0.5">
                    <Filter class="w-4 h-4 mr-2" /> Filter
                </button>
            </div>

            <!-- Chart Area -->
            <div v-if="performances.length === 0" class="h-64 flex flex-col items-center justify-center text-gray-400">
                <BarChart2 class="w-12 h-12 mb-2 opacity-50" />
                <p>Belum ada data pencapaian di periode ini.</p>
            </div>

            <div v-else>
                <h3 class="font-bold text-lg text-gray-800 dark:text-white mb-6 text-center">
                    Total Story Points Completed ({{ months.find(m => m.value == form.month)?.label }} {{ form.year }})
                </h3>
                
                <!-- Custom CSS Bar Chart -->
                <div class="relative h-80 flex items-end space-x-8 px-4 border-b border-gray-300 dark:border-gray-600">
                    <!-- Y-Axis Lines (Background) -->
                    <div class="absolute inset-0 flex flex-col justify-between pointer-events-none pb-8">
                        <div v-for="i in 5" :key="i" class="w-full border-t border-gray-100 dark:border-gray-700 border-dashed h-full"></div>
                    </div>

                    <!-- Bars -->
                    <div v-for="user in performances" :key="user.name" class="flex-1 flex flex-col items-center justify-end h-full group z-10 w-full max-w-[100px] mx-auto">
                        <div class="relative w-full flex flex-col justify-end h-full">
                            <!-- Tooltip/Value on Hover -->
                            <div class="absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-black text-white text-xs py-1 px-2 rounded mb-2 whitespace-nowrap z-20">
                                {{ user.points }} Points
                            </div>
                            
                            <!-- The Bar -->
                            <div 
                                class="w-full bg-indigo-500 hover:bg-indigo-600 dark:bg-indigo-600 dark:hover:bg-indigo-500 rounded-t-lg transition-all duration-500 relative"
                                :style="{ height: (user.points / maxPoints * 100) + '%' }"
                            >
                                <span class="absolute -top-6 w-full text-center text-sm font-bold text-indigo-700 dark:text-indigo-300">{{ user.points }}</span>
                            </div>
                        </div>
                        <!-- X-Axis Label -->
                        <div class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400 text-center truncate w-full">
                            {{ user.name }}
                        </div>
                    </div>
                </div>
            </div>

             <!-- Top 3 Ranking -->
            <div v-if="topPerformers.length > 0" class="mb-10 mt-10">
                <h3 class="text-xl font-bold text-center text-gray-800 dark:text-gray-200 mb-6 flex items-center justify-center">
                    <Trophy class="w-6 h-6 text-yellow-500 mr-2" />
                    Top Performers of the Month
                </h3>
                <div class="flex flex-col md:flex-row items-end justify-center gap-6">
                    <!-- 2nd Place -->
                    <div v-if="topPerformers[1]" class="order-2 md:order-1 flex flex-col items-center">
                        <div class="relative mb-2">
                             <div class="w-20 h-20 rounded-full border-4 border-gray-300 bg-gray-100 flex items-center justify-center text-2xl font-bold text-gray-400">
                                {{ topPerformers[1].name.charAt(0) }}
                             </div>
                             <div class="absolute -bottom-2 -right-2 bg-gray-300 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold border-2 border-white">2</div>
                        </div>
                        <h4 class="font-bold text-gray-700 dark:text-gray-300">{{ topPerformers[1].name }}</h4>
                        <span class="text-sm text-gray-500 font-semibold">{{ topPerformers[1].points }} pts</span>
                    </div>

                    <!-- 1st Place -->
                    <div v-if="topPerformers[0]" class="order-1 md:order-2 flex flex-col items-center transform md:-translate-y-4">
                        <Crown class="w-8 h-8 text-yellow-500 mb-2 animate-bounce" />
                        <div class="relative mb-2">
                             <div class="w-24 h-24 rounded-full border-4 border-yellow-400 bg-yellow-50 flex items-center justify-center text-3xl font-bold text-yellow-600 shadow-lg">
                                {{ topPerformers[0].name.charAt(0) }}
                             </div>
                             <div class="absolute -bottom-3 -right-2 bg-yellow-400 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold text-lg border-2 border-white">1</div>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white">{{ topPerformers[0].name }}</h4>
                        <span class="text-indigo-600 font-bold bg-indigo-50 px-3 py-1 rounded-full text-sm mt-1">{{ topPerformers[0].points }} Points</span>
                    </div>

                    <!-- 3rd Place -->
                    <div v-if="topPerformers[2]" class="order-3 flex flex-col items-center mt-5">
                        <div class="relative mb-2">
                             <div class="w-20 h-20 rounded-full border-4 border-orange-300 bg-orange-50 flex items-center justify-center text-2xl font-bold text-orange-400">
                                {{ topPerformers[2].name.charAt(0) }}
                             </div>
                             <div class="absolute -bottom-2 -right-2 bg-orange-300 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold border-2 border-white">3</div>
                        </div>
                        <h4 class="font-bold text-gray-700 dark:text-gray-300">{{ topPerformers[2].name }}</h4>
                        <span class="text-sm text-gray-500 font-semibold">{{ topPerformers[2].points }} pts</span>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
