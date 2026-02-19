<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Target, 
    Plus, 
    Pencil, 
    Trash2,
    ChevronDown,
    ChevronUp,
    CheckCircle,
    Circle,
    BarChart2,
    List
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextAreaInput from '@/Components/TextAreaInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    goals: Array,
    isCeo: Boolean
});

// STATE
const expandedObjectives = ref({});
const toggleObjective = (id) => {
    expandedObjectives.value[id] = !expandedObjectives.value[id];
};

// MODALS
const showGoalModal = ref(false);
const showObjModal = ref(false);
const showKrModal = ref(false);
const showActionModal = ref(false);

const editingGoal = ref(null);
const editingObj = ref(null);
const editingKr = ref(null);
const editingAction = ref(null);

const parentGoalId = ref(null);
const parentObjId = ref(null);
const parentKrId = ref(null);

// FORMS
const goalForm = useForm({ goal: '', year: new Date().getFullYear().toString() });
const objForm = useForm({ company_goal_id: '', division: '', objective: '' });
const krForm = useForm({ objective_id: '', key_result: '', target: 0, actual: 0, weight: 100, unit: 'number' });
const actionForm = useForm({ key_result_id: '', activity: '', is_completed: false });

// GOAL ACTIONS
const openGoalModal = (goal = null) => {
    editingGoal.value = goal;
    goalForm.goal = goal ? goal.goal : '';
    goalForm.year = goal ? goal.year : new Date().getFullYear().toString();
    showGoalModal.value = true;
};

const submitGoal = () => {
    if (editingGoal.value) {
        goalForm.put(route('okr.goals.update', editingGoal.value.id), { onSuccess: closeModals });
    } else {
        goalForm.post(route('okr.goals.store'), { onSuccess: closeModals });
    }
};

const deleteGoal = (id) => {
    if (confirm('Hapus goal ini berserta seluruh isinya?')) {
        useForm({}).delete(route('okr.goals.destroy', id));
    }
};

// OBJECTIVE ACTIONS
const openObjModal = (goalId, obj = null) => {
    parentGoalId.value = goalId;
    editingObj.value = obj;
    objForm.company_goal_id = goalId;
    objForm.division = obj ? obj.division : '';
    objForm.objective = obj ? obj.objective : '';
    showObjModal.value = true;
};

const submitObj = () => {
    if (editingObj.value) {
        objForm.put(route('okr.objectives.update', editingObj.value.id), { onSuccess: closeModals });
    } else {
        objForm.post(route('okr.objectives.store'), { onSuccess: closeModals });
    }
};

const deleteObj = (id) => {
    if (confirm('Hapus Objective ini?')) {
        useForm({}).delete(route('okr.objectives.destroy', id));
    }
};

// KEY RESULT ACTIONS
const openKrModal = (objId, kr = null) => {
    parentObjId.value = objId;
    editingKr.value = kr;
    krForm.objective_id = objId;
    krForm.key_result = kr ? kr.key_result : '';
    krForm.target = kr ? kr.target : 0;
    krForm.actual = kr ? kr.actual : 0;
    krForm.weight = kr ? kr.weight : 100;
    krForm.unit = kr ? kr.unit : 'number';
    showKrModal.value = true;
};

const submitKr = () => {
    if (editingKr.value) {
        krForm.put(route('okr.key-results.update', editingKr.value.id), { onSuccess: closeModals });
    } else {
        krForm.post(route('okr.key-results.store'), { onSuccess: closeModals });
    }
};

const deleteKr = (id) => {
    if (confirm('Hapus Key Result ini?')) {
        useForm({}).delete(route('okr.key-results.destroy', id));
    }
};

// ACTION PLAN ACTIONS
const openActionModal = (krId, action = null) => {
    parentKrId.value = krId;
    editingAction.value = action;
    actionForm.key_result_id = krId;
    actionForm.activity = action ? action.activity : '';
    actionForm.is_completed = action ? action.is_completed : false;
    showActionModal.value = true;
};

const submitAction = () => {
    if (editingAction.value) {
        actionForm.put(route('okr.action-plans.update', editingAction.value.id), { onSuccess: closeModals });
    } else {
        actionForm.post(route('okr.action-plans.store'), { onSuccess: closeModals });
    }
};

const toggleAction = (action) => {
    useForm({
        activity: action.activity,
        is_completed: !action.is_completed
    }).put(route('okr.action-plans.update', action.id), { preserveScroll: true });
};

const deleteAction = (id) => {
    if (confirm('Hapus Action Plan ini?')) {
        useForm({}).delete(route('okr.action-plans.destroy', id), { preserveScroll: true });
    }
};

const closeModals = () => {
    showGoalModal.value = false;
    showObjModal.value = false;
    showKrModal.value = false;
    showActionModal.value = false;
    goalForm.reset();
    objForm.reset();
    krForm.reset();
    actionForm.reset();
    editingGoal.value = null;
    editingObj.value = null;
    editingKr.value = null;
    editingAction.value = null;
};
</script>

<template>
    <Head title="Goals & OKR" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-2">
                <Target class="w-6 h-6 text-indigo-600" />
                <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                    Goals & OKR
                </h2>
            </div>
        </template>
        
        <template v-if="isCeo" #actions>
            <PrimaryButton @click="openGoalModal()">
                <Plus class="w-4 h-4 mr-2" /> Company Goal
            </PrimaryButton>
        </template>

        <div v-if="goals.length === 0" class="flex flex-col items-center justify-center py-20 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
            <Target class="w-16 h-16 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-500">Belum ada Goals tahun ini.</h3>
            <p v-if="isCeo" class="text-gray-400 text-sm mb-6">Mulai dengan membuat Company Goal.</p>
            <PrimaryButton v-if="isCeo" @click="openGoalModal()">Buat Goal</PrimaryButton>
        </div>

        <div v-else class="space-y-12 pb-20">
            <!-- COMPANY GOAL LOOP -->
            <div v-for="goal in goals" :key="goal.id" class="space-y-6">
                
                <!-- Level 1: Company Goal Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-xl shadow-lg p-6 text-white relative group">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="inline-block px-3 py-1 bg-white/20 rounded-full text-xs font-bold mb-2">
                                TAHUN {{ goal.year }}
                            </span>
                            <h3 class="text-2xl font-bold leading-tight">{{ goal.goal }}</h3>
                        </div>
                        <div v-if="isCeo" class="flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openGoalModal(goal)" class="p-2 bg-white/10 hover:bg-white/20 rounded-lg backdrop-blur-sm">
                                <Pencil class="w-4 h-4" />
                            </button>
                            <button @click="deleteGoal(goal.id)" class="p-2 bg-red-500/80 hover:bg-red-500 rounded-lg backdrop-blur-sm">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button @click="openObjModal(goal.id)" class="flex items-center text-sm font-semibold bg-white text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-50 transition-colors shadow-sm">
                            <Plus class="w-4 h-4 mr-2" /> Tambah Divisi / Objective
                        </button>
                    </div>
                </div>

                <!-- Level 2: Objectives List -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="obj in goal.objectives" :key="obj.id" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        
                        <!-- Objective Header -->
                        <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                            <div class="flex justify-between items-start mb-3">
                                <span class="bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300 text-xs font-bold px-2 py-1 rounded uppercase tracking-wide">
                                    {{ obj.division }}
                                </span>
                                <div class="flex space-x-1">
                                    <button @click="openObjModal(goal.id, obj)" class="p-1 text-gray-400 hover:text-indigo-600">
                                        <Pencil class="w-3.5 h-3.5" />
                                    </button>
                                    <button @click="deleteObj(obj.id)" class="p-1 text-gray-400 hover:text-red-600">
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                            <h4 class="text-base font-bold text-gray-800 dark:text-white mb-2">{{ obj.objective }}</h4>
                            
                            <!-- Progress Objective -->
                            <div class="flex items-center mt-3">
                                <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full transition-all duration-500" :style="{ width: obj.progress + '%' }"></div>
                                </div>
                                <span class="ml-3 text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ obj.progress }}%</span>
                            </div>
                        </div>

                        <!-- Level 3: Key Results -->
                        <div class="p-0">
                            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/20">
                                <span class="text-xs font-bold text-gray-500 uppercase flex items-center">
                                    <BarChart2 class="w-3 h-3 mr-1" /> Key Results
                                </span>
                                <button @click="openKrModal(obj.id)" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center">
                                    <Plus class="w-3 h-3 mr-1" /> Add KR
                                </button>
                            </div>

                            <div v-if="obj.key_results.length === 0" class="p-6 text-center text-sm text-gray-400 italic">
                                Belum ada Key Results.
                            </div>

                            <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                                <div v-for="kr in obj.key_results" :key="kr.id" class="group">
                                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex-1 pr-4">
                                                <p class="text-sm font-medium text-gray-800 dark:text-indigo-100">{{ kr.key_result }}</p>
                                                <div class="flex items-center space-x-3 mt-1 text-xs text-gray-500">
                                                    <span>Target: <strong class="text-gray-700 dark:text-gray-300">{{ kr.target }}</strong></span>
                                                    <span>Actual: <strong class="text-gray-700 dark:text-gray-300">{{ kr.actual }}</strong></span>
                                                    <span>Weight: {{ kr.weight }}%</span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span :class="[
                                                    'text-xs font-bold px-2 py-1 rounded',
                                                    kr.progress >= 100 ? 'bg-green-100 text-green-700' : 
                                                    kr.progress >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600'
                                                ]">
                                                    {{ kr.progress }}%
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Actions per KR -->
                                        <div class="flex justify-between items-center mt-3 pt-3 border-t border-dashed border-gray-200 dark:border-gray-700">
                                            <div class="flex space-x-2">
                                                <button @click="openKrModal(obj.id, kr)" class="text-[10px] uppercase font-bold text-gray-400 hover:text-indigo-600">Edit</button>
                                                <button @click="deleteKr(kr.id)" class="text-[10px] uppercase font-bold text-gray-400 hover:text-red-600">Delete</button>
                                            </div>
                                            <button @click="expandedObjectives[kr.id] = !expandedObjectives[kr.id]" class="flex items-center text-xs text-gray-500 hover:text-gray-700">
                                                {{ kr.action_plans.length }} Actions
                                                <component :is="expandedObjectives[kr.id] ? ChevronUp : ChevronDown" class="w-3 h-3 ml-1" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Level 4: Action Plans (Dropdown) -->
                                    <div v-show="expandedObjectives[kr.id]" class="bg-gray-50 dark:bg-gray-900/40 border-y border-gray-100 dark:border-gray-700 px-4 py-3 pb-4">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-[10px] font-bold uppercase text-gray-400 tracking-wider">Action Plans</span>
                                            <button @click="openActionModal(kr.id)" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center">
                                                <Plus class="w-3 h-3 mr-1" /> Add Action
                                            </button>
                                        </div>

                                        <div class="space-y-1">
                                            <div v-for="action in kr.action_plans" :key="action.id" class="flex items-start group/action py-1">
                                                <button @click="toggleAction(action)" class="mt-0.5 mr-2 flex-shrink-0 text-gray-400 hover:text-indigo-600 transition-colors">
                                                    <component :is="action.is_completed ? CheckCircle : Circle" :class="['w-4 h-4', action.is_completed ? 'text-green-500' : '']" />
                                                </button>
                                                <span :class="['text-xs text-gray-600 dark:text-gray-400 flex-1', action.is_completed ? 'line-through opacity-60' : '']">
                                                    {{ action.activity }}
                                                </span>
                                                <div class="hidden group-hover/action:flex ml-2">
                                                    <button @click="openActionModal(kr.id, action)" class="text-gray-400 hover:text-indigo-600 p-0.5">
                                                        <Pencil class="w-3 h-3" />
                                                    </button>
                                                    <button @click="deleteAction(action.id)" class="text-gray-400 hover:text-red-600 p-0.5">
                                                        <Trash2 class="w-3 h-3" />
                                                    </button>
                                                </div>
                                            </div>
                                            <p v-if="kr.action_plans.length === 0" class="text-xs text-gray-400 italic pl-6">Belum ada action plan.</p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: COMPANY GOAL -->
        <Modal :show="showGoalModal" @close="closeModals" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-bold mb-4">{{ editingGoal ? 'Edit Goal' : 'New Company Goal' }}</h2>
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Tahun" />
                        <TextInput v-model="goalForm.year" class="w-full mt-1" type="number" />
                    </div>
                    <div>
                        <InputLabel value="Company Goal" />
                        <TextAreaInput v-model="goalForm.goal" class="w-full mt-1" rows="3" placeholder="Target besar tahun ini..." />
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModals">Batal</SecondaryButton>
                    <PrimaryButton @click="submitGoal" :disabled="goalForm.processing">Simpan</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- MODAL: OBJECTIVE (DIVISI) -->
        <Modal :show="showObjModal" @close="closeModals" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-bold mb-4">{{ editingObj ? 'Edit Objective' : 'New Division Objective' }}</h2>
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Nama Divisi" />
                        <TextInput v-model="objForm.division" class="w-full mt-1" placeholder="Contoh: Marketing" />
                    </div>
                    <div>
                        <InputLabel value="Objective" />
                        <TextAreaInput v-model="objForm.objective" class="w-full mt-1" rows="3" placeholder="Target spesifik divisi ini..." />
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModals">Batal</SecondaryButton>
                    <PrimaryButton @click="submitObj" :disabled="objForm.processing">Simpan</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- MODAL: KEY RESULT -->
        <Modal :show="showKrModal" @close="closeModals" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-bold mb-4">{{ editingKr ? 'Edit Key Result' : 'New Key Result' }}</h2>
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Key Result" />
                        <TextAreaInput v-model="krForm.key_result" class="w-full mt-1" rows="2" placeholder="Target terukur..." />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Target" />
                            <TextInput v-model="krForm.target" type="number" class="w-full mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Actual (Saat Ini)" />
                            <TextInput v-model="krForm.actual" type="number" class="w-full mt-1" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Bobot (%)" />
                            <TextInput v-model="krForm.weight" type="number" class="w-full mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Unit (Opsional)" />
                            <TextInput v-model="krForm.unit" class="w-full mt-1" placeholder="clients, %, IDR" />
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModals">Batal</SecondaryButton>
                    <PrimaryButton @click="submitKr" :disabled="krForm.processing">Simpan</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- MODAL: ACTION PLAN -->
        <Modal :show="showActionModal" @close="closeModals" maxWidth="sm">
            <div class="p-6">
                <h2 class="text-lg font-bold mb-4">{{ editingAction ? 'Edit Action' : 'New Action Plan' }}</h2>
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Aktivitas" />
                        <TextAreaInput v-model="actionForm.activity" class="w-full mt-1" rows="3" placeholder="Apa yang akan dilakukan..." autofocus />
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModals">Batal</SecondaryButton>
                    <PrimaryButton @click="submitAction" :disabled="actionForm.processing">Simpan</PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
