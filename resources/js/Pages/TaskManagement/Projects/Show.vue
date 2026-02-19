<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Briefcase, Plus, Search, Calendar, User as UserIcon, CheckCircle, Clock, AlertCircle, Trash2, X, Tag, BarChart3, Layout, ChevronRight, ChevronLeft, Filter } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextAreaInput from '@/Components/TextAreaInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

// Chart.js imports
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js';
import { Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps({
    project: Object,
    members: Array,
    tasks: Array
});

const activeTab = ref('board'); // 'board' or 'dashboard'

// Task Filters
const taskFilters = ref({
    sprint: '',
    month: '',
    year: ''
});

const availableSprints = computed(() => {
    const sprints = new Set();
    props.tasks.forEach(t => {
        if (t.sprint_group) sprints.add(t.sprint_group);
    });
    // Sort naturally (Sprint 1, Sprint 2, Sprint 10)
    return Array.from(sprints).sort((a, b) => {
        const numA = parseInt(a.match(/\d+/)?.[0] || 0);
        const numB = parseInt(b.match(/\d+/)?.[0] || 0);
        return numA - numB;
    });
});

const months = [
    { value: '01', label: 'January' },
    { value: '02', label: 'February' },
    { value: '03', label: 'March' },
    { value: '04', label: 'April' },
    { value: '05', label: 'May' },
    { value: '06', label: 'June' },
    { value: '07', label: 'July' },
    { value: '08', label: 'August' },
    { value: '09', label: 'September' },
    { value: '10', label: 'October' },
    { value: '11', label: 'November' },
    { value: '12', label: 'December' }
];

const years = Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - i); // Current year and past 4 years

const clearFilters = () => {
    taskFilters.value.sprint = '';
    taskFilters.value.month = '';
    taskFilters.value.year = '';
};

// Group Tasks by Sprint/Group with Filters
const groupedTasks = computed(() => {
    const groups = {};
    let filteredTasks = props.tasks;

    // Apply Sprint Filter
    if (taskFilters.value.sprint) {
        filteredTasks = filteredTasks.filter(t => t.sprint_group === taskFilters.value.sprint);
    }

    // Apply Month Filter (on due_date)
    if (taskFilters.value.month) {
        filteredTasks = filteredTasks.filter(t => {
            if (!t.due_date) return false;
            const d = new Date(t.due_date);
            const taskMonth = (d.getMonth() + 1).toString().padStart(2, '0');
            return taskMonth === taskFilters.value.month;
        });
    }

    // Apply Year Filter (on due_date)
    if (taskFilters.value.year) {
        filteredTasks = filteredTasks.filter(t => {
            if (!t.due_date) return false;
            const d = new Date(t.due_date);
            return d.getFullYear() === parseInt(taskFilters.value.year);
        });
    }

    filteredTasks.forEach(task => {
        const key = task.sprint_group || 'Uncategorized';
        if (!groups[key]) groups[key] = [];
        groups[key].push(task);
    });
    
    // Ensure the filtered Sprint is the only key if selected (optional, but grouping handles it)
    return groups;
});

// Use project labels or default if empty (though backend adds defaults)
const projectLabels = computed(() => props.project.labels || []);

// Task Modal State
const showTaskModal = ref(false);
const editingTask = ref(null);
const activeSprintGroup = ref('');

// Label Management State
const showLabelInput = ref(false);
const newLabel = ref('');

const taskForm = useForm({
    sprint_number: 1, // Changed to number
    sprint_group: '', // Hidden, constructed from sprint_number
    description: '',
    category: '',
    story_point: 0,
    status: 'backlog',
    assigned_to: '',
    due_date: new Date().toISOString().slice(0, 10)
});

const openTaskModal = (groupName = '', task = null) => {
    editingTask.value = task;
    activeSprintGroup.value = groupName;
    
    // Parse Sprint Number from "Sprint X"
    let sprintNum = 1;
    if (task && task.sprint_group) {
        const match = task.sprint_group.match(/\d+/);
        if (match) sprintNum = parseInt(match[0]);
    } else if (groupName) {
        const match = groupName.match(/\d+/);
        if (match) sprintNum = parseInt(match[0]);
    }

    taskForm.sprint_number = sprintNum;
    taskForm.description = task ? task.description : '';
    taskForm.category = task ? task.category : (projectLabels.value[0] || '');
    taskForm.story_point = task ? task.story_point : 1;
    taskForm.status = task ? task.status : 'backlog';
    taskForm.assigned_to = task ? task.assigned_to : '';
    taskForm.due_date = task ? task.due_date : new Date().toISOString().slice(0, 10);
    
    showTaskModal.value = true;
};

const submitTask = () => {
    // Construct Sprint Group String
    taskForm.sprint_group = `Sprint ${taskForm.sprint_number}`;

    if (editingTask.value) {
        taskForm.put(route('tasks.tasks.update', editingTask.value.id), {
            onSuccess: () => showTaskModal.value = false
        });
    } else {
        taskForm.post(route('tasks.tasks.store', props.project.id), {
            onSuccess: () => showTaskModal.value = false
        });
    }
};

// Label Management Functions
const isAddingLabel = ref(false);

const addLabel = () => {
    if (!newLabel.value) return;
    
    router.post(route('tasks.projects.labels.store', props.project.id), {
        label: newLabel.value
    }, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => isAddingLabel.value = true,
        onFinish: () => isAddingLabel.value = false,
        onSuccess: () => {
            taskForm.category = newLabel.value; // Auto select new label
            newLabel.value = '';
            showLabelInput.value = false;
        },
        onError: (errors) => {
            alert('Failed to add label. Please try again.');
            console.error(errors);
        }
    });
};

const removeLabel = (label) => {
    if (confirm(`Remove label "${label}"? Tasks using this label will keep it.`)) {
        router.delete(route('tasks.projects.labels.destroy', props.project.id), {
            data: { label: label },
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                 if (taskForm.category === label) taskForm.category = '';
            }
        });
    }
};

// --- DASHBOARD LOGIC ---

// Helper to get month name
const getMonthName = (dateStr) => {
    if (!dateStr) return 'Unknown';
    const date = new Date(dateStr);
    return date.toLocaleString('default', { month: 'long' });
};

// Compute Performance Data (User x Month -> Story Points)
// Using 'updated_at' for 'done' tasks as the "completed date" approximation
const performanceData = computed(() => {
    const data = {}; // { UserName: { Month: Points, Month2: Points } }
    const months = new Set();

    // Initialize all members with 0
    props.members.forEach(m => {
        data[m.name] = {};
    });

    props.tasks.forEach(task => {
        if (task.status === 'done' && task.assignee) {
            const month = getMonthName(task.updated_at); // or due_date? using updated_at is better for velocity
            months.add(month);
            
            const assigneeName = task.assignee.name;
            if (!data[assigneeName]) data[assigneeName] = {};
            
            if (!data[assigneeName][month]) data[assigneeName][month] = 0;
            data[assigneeName][month] += parseInt(task.story_point || 0);
        }
    });

    // Sort months (simplified, assumes same year for now or just chronological appearance)
    const sortedMonths = Array.from(months).sort((a, b) => {
         // Simple month sort logic
         const monthOrder = { 'January': 1, 'February': 2, 'March': 3, 'April': 4, 'May': 5, 'June': 6, 'July': 7, 'August': 8, 'September': 9, 'October': 10, 'November': 11, 'December': 12 };
         return (monthOrder[a] || 99) - (monthOrder[b] || 99);
    });

    return {
        users: data,
        months: sortedMonths
    };
});

// Chart Data
const chartData = computed(() => {
    const { users, months } = performanceData.value;
    
    // Random colors for datasets
    const colors = ['#4f46e5', '#ef4444', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#6366f1'];

    return {
        labels: months,
        datasets: Object.keys(users).map((userName, index) => {
            return {
                label: userName,
                backgroundColor: colors[index % colors.length],
                data: months.map(m => users[userName][m] || 0)
            };
        })
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' },
        title: { display: true, text: 'Team Velocity (Story Points Completed)' }
    }
};

// Task Deletion State
const confirmingTaskDeletion = ref(false);
const taskToDeleteId = ref(null);

const confirmTaskDeletion = (id) => {
    taskToDeleteId.value = id;
    confirmingTaskDeletion.value = true;
};

const deleteTaskConfirm = () => {
    if (taskToDeleteId.value) {
        useForm({}).delete(route('tasks.tasks.destroy', taskToDeleteId.value), {
            onSuccess: () => {
                showTaskModal.value = false;
                closeDeleteTaskModal();
            },
            onError: () => {
                alert('Failed to delete task.');
                closeDeleteTaskModal();
            }
        });
    }
};

const closeDeleteTaskModal = () => {
    confirmingTaskDeletion.value = false;
    taskToDeleteId.value = null;
};
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button @click="router.visit(route('tasks.projects.index'))" class="flex items-center text-gray-500 hover:text-indigo-600 transition-colors group cursor-pointer focus:outline-none">
                        <div class="p-2 rounded-full group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/30 transition-colors">
                            <ChevronLeft class="w-5 h-5" />
                        </div>
                        <span class="text-sm font-medium ml-1">Back</span>
                    </button>
                    <div class="h-6 w-px bg-gray-300 dark:bg-gray-600 mx-2"></div>
                    <div class="flex items-center space-x-2">
                        <Briefcase class="w-6 h-6 text-indigo-600" />
                        <h2 class="font-bold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                            {{ project.name }} <span class="text-sm font-normal text-gray-500 ml-2">({{ project.status }})</span>
                        </h2>
                    </div>
                </div>
            </div>
        </template>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden min-h-[600px]">
             
             <!-- Tab Navigation -->
             <div class="bg-indigo-50 dark:bg-indigo-900/40 border-b border-indigo-100 dark:border-indigo-800 flex items-center justify-between px-6 py-2">
                 <div class="flex space-x-4">
                     <button 
                        @click="activeTab = 'board'" 
                        :class="[
                            'flex items-center px-4 py-2 text-sm font-medium rounded-t-lg transition-colors border-b-2',
                            activeTab === 'board' 
                                ? 'text-indigo-600 border-indigo-600 dark:text-indigo-300 dark:border-indigo-400 bg-white dark:bg-gray-800' 
                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 border-transparent'
                        ]"
                     >
                        <Layout class="w-4 h-4 mr-2" />
                        Task Board
                     </button>
                     <button 
                        @click="activeTab = 'dashboard'" 
                        :class="[
                            'flex items-center px-4 py-2 text-sm font-medium rounded-t-lg transition-colors border-b-2',
                            activeTab === 'dashboard' 
                                ? 'text-indigo-600 border-indigo-600 dark:text-indigo-300 dark:border-indigo-400 bg-white dark:bg-gray-800' 
                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 border-transparent'
                        ]"
                     >
                        <BarChart3 class="w-4 h-4 mr-2" />
                        Dashboard
                     </button>
                 </div>

                 <div v-if="activeTab === 'board'">
                     <button @click="openTaskModal('Sprint 1')" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded hover:bg-indigo-700 transition flex items-center">
                        <Plus class="w-3 h-3 mr-1" /> Add New Sprint/Task
                     </button>
                 </div>
             </div>

             <!-- Filter Bar -->
             <div v-if="activeTab === 'board'" class="bg-indigo-50/50 dark:bg-indigo-900/20 border-b border-indigo-100 dark:border-indigo-800 p-4 flex flex-wrap gap-4 items-center">
                 <div class="flex items-center space-x-2 text-sm text-indigo-700 dark:text-indigo-300 font-medium">
                     <Filter class="w-4 h-4" />
                     <span>Filters:</span>
                 </div>
                 
                 <!-- Sprint Filter -->
                 <select v-model="taskFilters.sprint" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1 pl-2 pr-8">
                     <option value="">All Sprints</option>
                     <option v-for="sprint in availableSprints" :key="sprint" :value="sprint">{{ sprint }}</option>
                 </select>

                 <!-- Month Filter -->
                 <select v-model="taskFilters.month" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1 pl-2 pr-8">
                     <option value="">All Months</option>
                     <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                 </select>

                 <!-- Year Filter -->
                 <select v-model="taskFilters.year" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1 pl-2 pr-8">
                     <option value="">All Years</option>
                     <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                 </select>

                 <!-- Clear Button -->
                 <button 
                    v-if="taskFilters.sprint || taskFilters.month || taskFilters.year" 
                    @click="clearFilters"
                    class="text-xs text-red-500 hover:text-red-700 underline flex items-center"
                 >
                    <X class="w-3 h-3 mr-1" /> Clear
                 </button>
             </div>

             <!-- Task Board View -->
             <div v-if="activeTab === 'board'" class="overflow-x-auto">
                 <table class="w-full text-left border-collapse">
                     <thead class="bg-gray-100 dark:bg-gray-900 text-xs font-bold text-gray-500 uppercase tracking-wider">
                         <tr>
                             <th class="px-6 py-3 border-b dark:border-gray-700 w-1/3">Task Description</th>
                             <th class="px-4 py-3 border-b dark:border-gray-700 w-1/12 text-center">Label</th>
                             <th class="px-4 py-3 border-b dark:border-gray-700 w-1/12 text-center">Story Point</th>
                             <th class="px-4 py-3 border-b dark:border-gray-700 w-1/12 text-center">Status</th>
                             <th class="px-4 py-3 border-b dark:border-gray-700 w-1/6">Assign To</th>
                             <th class="px-4 py-3 border-b dark:border-gray-700 w-1/6">Due Date</th>
                             <th class="px-2 py-3 border-b dark:border-gray-700"></th>
                         </tr>
                     </thead>
                     <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                         <!-- Loop Groups -->
                         <template v-for="(groupTasks, groupName) in groupedTasks" :key="groupName">
                             <!-- Group Header -->
                             <tr class="bg-gray-50 dark:bg-gray-800/80">
                                 <td colspan="7" class="px-6 py-2 text-center font-bold text-sm text-gray-700 dark:text-gray-300 border-y border-gray-200 dark:border-gray-600 uppercase tracking-widest">
                                     ---------------- {{ groupName }} ----------------
                                 </td>
                             </tr>

                             <!-- Tasks -->
                             <tr v-for="task in groupTasks" :key="task.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                 <td class="px-6 py-3 border-r border-gray-100 dark:border-gray-700 text-sm font-medium text-gray-800 dark:text-gray-200">
                                     {{ task.description }}
                                 </td>
                                 <td class="px-4 py-3 border-r border-gray-100 dark:border-gray-700 text-center">
                                     <span :class="[
                                         'px-2 py-1 text-[10px] font-bold rounded-full border',
                                         ['Backend', 'Mobile'].includes(task.category) ? 'bg-blue-50 text-blue-600 border-blue-200' : 
                                         ['Frontend', 'Design'].includes(task.category) ? 'bg-green-50 text-green-600 border-green-200' : 
                                         'bg-gray-50 text-gray-600 border-gray-200'
                                     ]">
                                         {{ task.category }}
                                     </span>
                                 </td>
                                 <td class="px-4 py-3 border-r border-gray-100 dark:border-gray-700 text-center">
                                     <span class="inline-flex items-center justify-center w-6 h-6 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">
                                         {{ task.story_point }}
                                     </span>
                                 </td>
                                 <td class="px-4 py-3 border-r border-gray-100 dark:border-gray-700 text-center">
                                     <span :class="[
                                         'px-2 py-1 text-xs font-bold rounded uppercase whitespace-nowrap',
                                         task.status === 'done' ? 'bg-green-100 text-green-700' : 
                                         task.status === 'on_review' ? 'bg-purple-100 text-purple-700' :
                                         task.status === 'on_progress' ? 'bg-yellow-100 text-yellow-700' :
                                         task.status === 'todo' ? 'bg-blue-100 text-blue-700' :
                                         'bg-gray-100 text-gray-600'
                                     ]">
                                         {{ task.status ? task.status.replace('_', ' ') : 'Unknown' }}
                                     </span>
                                 </td>
                                 <td class="px-4 py-3 border-r border-gray-100 dark:border-gray-700">
                                     <div v-if="task.assignee" class="flex items-center space-x-2">
                                         <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] text-indigo-700 font-bold border border-indigo-200">
                                             {{ task.assignee.name.charAt(0) }}
                                         </div>
                                         <span class="text-xs text-gray-600 dark:text-gray-400 truncate max-w-[100px]">{{ task.assignee.name }}</span>
                                     </div>
                                     <span v-else class="text-xs text-gray-400 italic">Unassigned</span>
                                 </td>
                                 <td class="px-4 py-3 border-r border-gray-100 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400 text-center">
                                     {{ task.due_date ? new Date(task.due_date).toLocaleDateString() : '-' }}
                                 </td>
                                 <td class="px-2 py-3 text-right">
                                     <button @click="openTaskModal(groupName, task)" class="text-xs bg-gray-100 hover:bg-indigo-100 text-gray-500 hover:text-indigo-600 px-2 py-1 rounded transition-colors">
                                         Edit
                                     </button>
                                 </td>
                             </tr>
                             
                             <!-- Add Task Button Row -->
                             <tr>
                                 <td colspan="7" class="px-6 py-2 border-b border-gray-200 dark:border-gray-600 text-center">
                                     <button @click="openTaskModal(groupName)" class="text-xs inline-flex items-center text-gray-400 hover:text-indigo-600 font-bold uppercase tracking-wider py-1 border border-transparent hover:border-dashed hover:border-gray-300 w-full justify-center rounded transition-all">
                                         <Plus class="w-3 h-3 mr-1" /> Add Task to {{ groupName }}
                                     </button>
                                 </td>
                             </tr>
                         </template>
                         
                         <tr v-if="Object.keys(groupedTasks).length === 0">
                             <td colspan="7" class="p-8 text-center text-gray-400">
                                 No tasks found. Create a new sprint group to start.
                             </td>
                         </tr>
                     </tbody>
                 </table>
             </div>

             <!-- Dashboard View -->
             <div v-if="activeTab === 'dashboard'" class="p-6">
                <!-- Data Table -->
                <div class="overflow-x-auto mb-8 bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-center border-collapse">
                        <thead class="bg-gray-100 dark:bg-gray-900 text-xs font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="px-6 py-4 border-b dark:border-gray-700 text-left bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300">User / Month</th>
                                <th v-for="month in performanceData.months" :key="month" class="px-6 py-4 border-b dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-l border-gray-200 dark:border-gray-700">
                                    {{ month }}
                                </th>
                            </tr>
                        </thead>
                         <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <tr v-for="(userData, userName) in performanceData.users" :key="userName">
                                <td class="px-6 py-3 font-medium text-gray-700 dark:text-gray-300 text-left border-r border-gray-100 dark:border-gray-700">
                                    {{ userName }}
                                </td>
                                <td v-for="month in performanceData.months" :key="month" class="px-6 py-3 border-r border-gray-100 dark:border-gray-700">
                                    <span :class="{
                                        'px-2 py-1 rounded font-bold': true,
                                        'bg-red-100 text-red-700': !userData[month] || userData[month] === 0,
                                        'bg-green-100 text-green-700': userData[month] > 0
                                    }">
                                        {{ userData[month] || 0 }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!performanceData.months.length">
                                <td colspan="100%" class="p-4 text-gray-400 italic">No completed tasks yet to show statistics.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Chart -->
                <div class="h-80 w-full bg-white dark:bg-gray-800">
                    <Bar :data="chartData" :options="chartOptions" />
                </div>
             </div>
        </div>

        <!-- Task Modal -->
        <Modal :show="showTaskModal" @close="showTaskModal = false" maxWidth="lg">
            <div class="p-6 bg-white dark:bg-gray-800">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ editingTask ? 'Edit Task' : 'New Task' }}</h2>
                    <button @click="showTaskModal = false" class="text-gray-400 hover:text-gray-600"><X class="w-5 h-5" /></button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                         <InputLabel value="Description" class="text-gray-700 dark:text-gray-300" />
                         <TextAreaInput v-model="taskForm.description" class="w-full mt-1 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100" rows="3" placeholder="Task description..." />
                    </div>
                    
                    <div>
                        <InputLabel value="Sprint" class="text-gray-700 dark:text-gray-300" />
                        <div class="mt-1 flex items-center">
                             <span class="bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 px-3 py-2 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 text-sm">Sprint</span>
                             <TextInput v-model="taskForm.sprint_number" type="number" class="w-full rounded-l-none text-gray-900 dark:text-gray-100" />
                        </div>
                    </div>

                    <div>
                         <InputLabel value="Label" class="text-gray-700 dark:text-gray-300" />
                         <div class="flex gap-2">
                             <div class="relative w-full">
                                 <select v-model="taskForm.category" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                     <option value="" disabled>Select a label</option>
                                     <option v-for="label in projectLabels" :key="label" :value="label">{{ label }}</option>
                                 </select>
                                 <button 
                                    v-if="taskForm.category && projectLabels.includes(taskForm.category)" 
                                    @click="removeLabel(taskForm.category)"
                                    type="button"
                                    class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500"
                                    title="Delete this label"
                                 >
                                    <Trash2 class="w-3 h-3" />
                                 </button>
                             </div>
                             <button 
                                type="button" 
                                @click="showLabelInput = !showLabelInput" 
                                class="px-3 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                                title="Add New Label"
                             >
                                <Plus class="w-4 h-4 text-gray-600 dark:text-gray-300" />
                             </button>
                         </div>
                         
                         <!-- Add Label Input (Appears below) -->
                         <div v-if="showLabelInput" class="mt-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-md border border-gray-200 dark:border-gray-600">
                            <InputLabel value="New Label Name" class="text-xs mb-1" />
                            <div class="flex gap-2">
                                <TextInput v-model="newLabel" :disabled="isAddingLabel" class="w-full text-xs h-8" placeholder="e.g. Bugfix" @keyup.enter="addLabel" />
                                <button type="button" @click="addLabel" :disabled="isAddingLabel" class="bg-indigo-600 text-white px-3 rounded text-xs font-medium hover:bg-indigo-700 transition h-8 disabled:opacity-50">
                                    {{ isAddingLabel ? '...' : 'Add' }}
                                </button>
                            </div>
                         </div>
                    </div>

                    <div>
                        <InputLabel value="Story Points" class="text-gray-700 dark:text-gray-300" />
                        <TextInput v-model="taskForm.story_point" type="number" class="w-full mt-1 text-gray-900 dark:text-gray-100" />
                    </div>

                    <div>
                        <InputLabel value="Status" class="text-gray-700 dark:text-gray-300" />
                        <select v-model="taskForm.status" class="w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-md shadow-sm">
                            <option value="backlog">Backlog</option>
                            <option value="todo">To Do</option>
                            <option value="on_progress">On Progress</option>
                            <option value="on_review">On Review</option>
                            <option value="done">Done</option>
                        </select>
                    </div>

                    <div>
                        <InputLabel value="Assign To" class="text-gray-700 dark:text-gray-300" />
                        <select v-model="taskForm.assigned_to" class="w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-md shadow-sm">
                            <option value="">Unassigned</option>
                            <option v-for="m in members" :key="m.id" :value="m.id">{{ m.name }}</option>
                        </select>
                    </div>

                    <div>
                        <InputLabel value="Due Date" class="text-gray-700 dark:text-gray-300" />
                        <TextInput v-model="taskForm.due_date" type="date" class="w-full mt-1 text-gray-900 dark:text-gray-100" />
                    </div>
                </div>

                 <div class="mt-8 flex justify-between border-t border-gray-100 dark:border-gray-700 pt-4">
                    <div>
                         <button v-if="editingTask" @click="confirmTaskDeletion(editingTask.id)" class="text-red-500 hover:text-red-700 text-sm font-medium flex items-center">
                            <Trash2 class="w-4 h-4 mr-1" /> Delete Task
                        </button>
                    </div>
                    <div class="flex space-x-3">
                        <SecondaryButton @click="showTaskModal = false">Cancel</SecondaryButton>
                        <PrimaryButton @click="submitTask">Save Task</PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Task Delete Confirmation Modal -->
        <Modal :show="confirmingTaskDeletion" @close="closeDeleteTaskModal" maxWidth="sm">
            <div class="p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <AlertCircle class="h-6 w-6 text-red-600" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            Delete Task
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Are you sure you want to delete this task? This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:flex-row-reverse">
                    <DangerButton class="w-full justify-center" @click="deleteTaskConfirm">
                        Delete Task
                    </DangerButton>
                    <SecondaryButton class="mt-3 w-full justify-center sm:mt-0" @click="closeDeleteTaskModal">
                        Cancel
                    </SecondaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
