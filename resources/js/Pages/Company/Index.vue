<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Target, 
    Rocket, 
    Heart, 
    Plus, 
    Pencil, 
    Trash2, 
    Check, 
    X,
    LayoutDashboard
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextAreaInput from '@/Components/TextAreaInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    vision: Object,
    missions: Array,
    coreValues: Array,
    isCeo: Boolean
});

// VISION FORM
const visionForm = useForm({
    content: props.vision?.content || ''
});

const isEditingVision = ref(false);
const saveVision = () => {
    visionForm.post(route('company.vision.update'), {
        preserveScroll: true,
        onSuccess: () => isEditingVision.value = false
    });
};

// MISSION MODAL
const showingMissionModal = ref(false);
const editingMission = ref(null);
const missionForm = useForm({
    content: ''
});

const openMissionModal = (mission = null) => {
    editingMission.value = mission;
    missionForm.content = mission ? mission.content : '';
    showingMissionModal.value = true;
};

const submitMission = () => {
    if (editingMission.value) {
        missionForm.put(route('company.missions.update', editingMission.value.id), {
            onSuccess: () => closeModal()
        });
    } else {
        missionForm.post(route('company.missions.store'), {
            onSuccess: () => {
                closeModal();
                missionForm.reset();
            }
        });
    }
};

const deleteMission = (id) => {
    if (confirm('Hapus misi ini?')) {
        useForm({}).delete(route('company.missions.destroy', id));
    }
};

// CORE VALUE MODAL
const showingValueModal = ref(false);
const editingValue = ref(null);
const valueForm = useForm({
    title: '',
    description: ''
});

const openValueModal = (value = null) => {
    editingValue.value = value;
    valueForm.title = value ? value.title : '';
    valueForm.description = value ? value.description : '';
    showingValueModal.value = true;
};

const submitValue = () => {
    if (editingValue.value) {
        valueForm.put(route('company.core-values.update', editingValue.value.id), {
            onSuccess: () => closeModal()
        });
    } else {
        valueForm.post(route('company.core-values.store'), {
            onSuccess: () => {
                closeModal();
                valueForm.reset();
            }
        });
    }
};

const deleteValue = (id) => {
    if (confirm('Hapus Core Value ini?')) {
        useForm({}).delete(route('company.core-values.destroy', id));
    }
};

const closeModal = () => {
    showingMissionModal.value = false;
    showingValueModal.value = false;
    editingMission.value = null;
    editingValue.value = null;
};
</script>

<template>
    <Head title="Our Company" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-2">
                <LayoutDashboard class="w-6 h-6 text-indigo-600" />
                <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                    Our Company Profile
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- VISI SECTION -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="p-3 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg">
                                    <Target class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Visi</h3>
                            </div>
                            <button v-if="isCeo && !isEditingVision" @click="isEditingVision = true" class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center text-sm">
                                <Pencil class="w-4 h-4 mr-1" /> Edit Visi
                            </button>
                        </div>

                        <div v-if="!isEditingVision" class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-xl italic text-lg text-gray-700 dark:text-gray-300 border-l-4 border-indigo-500">
                            {{ vision?.content || 'Visi perusahaan belum ditentukan.' }}
                        </div>

                        <div v-else class="space-y-4">
                            <TextAreaInput
                                v-model="visionForm.content"
                                class="w-full"
                                rows="3"
                                placeholder="Masukkan visi perusahaan..."
                            />
                            <div class="flex items-center space-x-3">
                                <PrimaryButton @click="saveVision" :disabled="visionForm.processing">
                                    {{ visionForm.processing ? 'Menyimpan...' : 'Simpan' }}
                                </PrimaryButton>
                                <SecondaryButton @click="isEditingVision = false">Batal</SecondaryButton>
                                
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p
                                        v-if="visionForm.recentlySuccessful"
                                        class="text-sm text-emerald-600 dark:text-emerald-400 flex items-center"
                                    >
                                        <Check class="w-4 h-4 mr-1" /> Tersimpan
                                    </p>
                                </Transition>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MISI SECTION -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/40 rounded-lg">
                                    <Rocket class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Misi</h3>
                            </div>
                            <button v-if="isCeo" @click="openMissionModal()" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-800 transition ease-in-out duration-150">
                                <Plus class="w-4 h-4 mr-1" /> Tambah Misi
                            </button>
                        </div>

                        <div v-if="missions.length > 0" class="space-y-4">
                            <div v-for="(mission, index) in missions" :key="mission.id" class="group flex items-start space-x-4 p-4 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all">
                                <div class="mt-1 flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-grow">
                                    <p class="text-gray-700 dark:text-gray-300">{{ mission.content }}</p>
                                </div>
                                <div v-if="isCeo" class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openMissionModal(mission)" class="p-2 text-gray-400 hover:text-indigo-600">
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteMission(mission.id)" class="p-2 text-gray-400 hover:text-red-600">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-gray-500 italic">Misi perusahaan belum ditambahkan.</p>
                    </div>
                </div>

                <!-- CORE VALUES SECTION -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center space-x-3">
                                <div class="p-3 bg-rose-100 dark:bg-rose-900/40 rounded-lg">
                                    <Heart class="w-6 h-6 text-rose-600 dark:text-rose-400" />
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Core Values</h3>
                            </div>
                            <button v-if="isCeo" @click="openValueModal()" class="inline-flex items-center px-4 py-2 bg-rose-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-rose-700 active:bg-rose-800 transition ease-in-out duration-150">
                                <Plus class="w-4 h-4 mr-1" /> Tambah Value
                            </button>
                        </div>

                        <div v-if="coreValues.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="value in coreValues" :key="value.id" class="group relative flex flex-col p-6 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-all">
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ value.title }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ value.description }}</p>
                                
                                <div v-if="isCeo" class="absolute top-4 right-4 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openValueModal(value)" class="p-1.5 bg-gray-100 dark:bg-gray-700 rounded-md text-gray-500 hover:text-indigo-600">
                                        <Pencil class="w-3.5 h-3.5" />
                                    </button>
                                    <button @click="deleteValue(value.id)" class="p-1.5 bg-gray-100 dark:bg-gray-700 rounded-md text-gray-500 hover:text-red-600">
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-gray-500 italic">Core values belum ditambahkan.</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- MISSION MODAL -->
        <Modal :show="showingMissionModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    {{ editingMission ? 'Edit Misi' : 'Tambah Misi Baru' }}
                </h2>
                <div class="mt-4">
                    <InputLabel for="mission_content" value="Deskripsi Misi" />
                    <TextAreaInput
                        id="mission_content"
                        v-model="missionForm.content"
                        class="mt-1 block w-full"
                        rows="4"
                    />
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">Batal</SecondaryButton>
                    <PrimaryButton @click="submitMission" :disabled="missionForm.processing">Simpan</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- VALUE MODAL -->
        <Modal :show="showingValueModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    {{ editingValue ? 'Edit Core Value' : 'Tambah Core Value Baru' }}
                </h2>
                <div class="space-y-4">
                    <div>
                        <InputLabel for="value_title" value="Judul Value" />
                        <TextInput
                            id="value_title"
                            v-model="valueForm.title"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Contoh: Integritas"
                        />
                    </div>
                    <div>
                        <InputLabel for="value_desc" value="Deskripsi" />
                        <TextAreaInput
                            id="value_desc"
                            v-model="valueForm.description"
                            class="mt-1 block w-full"
                            rows="4"
                        />
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">Batal</SecondaryButton>
                    <PrimaryButton @click="submitValue" :disabled="valueForm.processing">Simpan</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
