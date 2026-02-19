<script setup>
import { 
    Building2, 
    Plus, 
    Trash2, 
    User as UserIcon,
    Shield,
    ChevronUp,
    Edit2
} from 'lucide-vue-next';

const props = defineProps({
    department: Object,
    isRoot: {
        type: Boolean,
        default: false
    },
    canEdit: {
        type: Boolean,
        default: false
    },
    canManageHierarchy: {
        type: Boolean,
        default: false
    },
    isDescendant: {
        type: Function,
        required: true
    }
});

const emit = defineEmits(['add-sub', 'delete-dept', 'edit-user', 'user-dropped', 'edit-dept']);

import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
const isDragOver = ref(false);

const onDragOver = (event) => {
    if (!props.canManageHierarchy) return;
    
    // Manajer territory check
    const page = usePage();
    if (page.props.auth.user.role === 'manajer') {
        if (!props.isDescendant(page.props.auth.user.department_id, props.department.id)) {
            return;
        }
    }

    event.preventDefault();
    event.stopPropagation();
    isDragOver.value = true;
};

const onDragLeave = () => {
    isDragOver.value = false;
};

const onDrop = (event) => {
    if (!props.canManageHierarchy) return;
    event.preventDefault();
    event.stopPropagation();
    isDragOver.value = false;
    const userId = event.dataTransfer.getData('userId');
    if (userId) {
        emit('user-dropped', {
            userId: userId,
            departmentId: props.department.id
        });
    }
};
</script>

<template>
    <div 
        @dragover="onDragOver"
        @dragleave="onDragLeave"
        @drop="onDrop"
        :class="[
            'rounded-xl border transition-all duration-200',
            isRoot ? 'p-4 bg-gray-50 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700 shadow-sm' : 'pl-4 border-l-2 border-indigo-100 dark:border-indigo-900 border-t-0 border-r-0 border-b-0 mt-4',
            isDragOver ? 'ring-2 ring-indigo-500 border-indigo-500 scale-[1.01] bg-indigo-50/50 dark:bg-indigo-900/20' : ''
        ]"
    >
        <!-- Department Header -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div :class="['p-1.5 rounded-lg', isRoot ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500']">
                    <Building2 :class="isRoot ? 'w-5 h-5' : 'w-4 h-4'" />
                </div>
                <div class="flex flex-col">
                    <span :class="['font-bold text-gray-900 dark:text-white', isRoot ? 'text-lg' : 'text-base']">
                        {{ department.name }}
                    </span>
                    <span v-if="!isRoot" class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Sub-Departemen</span>
                </div>
            </div>
            
            <div v-if="canEdit" class="flex items-center space-x-1">
                <button 
                    @click="$emit('edit-dept', department)" 
                    class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-all"
                    title="Edit Nama Departemen"
                >
                    <Edit2 class="w-4 h-4" />
                </button>
                <button 
                    @click="$emit('add-sub', department.id)" 
                    class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg transition-all"
                    title="Tambah Sub-Departemen"
                >
                    <Plus class="w-4 h-4" />
                </button>
                <button 
                    @click="$emit('delete-dept', department)" 
                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-all"
                    title="Hapus Departemen"
                >
                    <Trash2 class="w-4 h-4" />
                </button>
            </div>
        </div>

        <div v-if="department.users && department.users.length > 0" 
             :class="['grid gap-3 mb-4', isRoot ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3' : 'grid-cols-1 md:grid-cols-2']">
            <div v-for="user in department.users" :key="user.id" 
                 :draggable="canManageHierarchy"
                 @dragstart="(e) => {
                     if (!canManageHierarchy) return;
                     
                     // Manajer logic: cannot drag peers or superiors
                     const page = usePage();
                     if (page.props.auth.user.role === 'manajer') {
                         const higherRoles = ['ceo', 'director', 'manajer'];
                         if (higherRoles.includes(user.role)) {
                             e.preventDefault();
                             return;
                         }
                     }

                     e.dataTransfer.setData('userId', user.id);
                     e.dataTransfer.effectAllowed = 'move';
                 }"
                 @click="$emit('edit-user', user)"
                 :class="[
                     'bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between group hover:shadow-md transition-all',
                     canManageHierarchy ? 'cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-700' : 'cursor-pointer border-gray-100'
                 ]"
            >
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs uppercase border border-indigo-100 dark:border-indigo-900 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate max-w-[120px]">{{ user.name }}</p>
                        <div class="flex items-center text-[9px] text-gray-500 uppercase font-bold tracking-tight">
                             <Shield class="w-2 h-2 mr-1" />
                             {{ user.role }}
                        </div>
                        <!-- Superior info -->
                        <div v-if="user.superior" class="flex items-center text-[9px] text-indigo-400 font-medium mt-0.5">
                            <ChevronUp class="w-2.5 h-2.5 mr-0.5" />
                            <span class="truncate max-w-[100px]">{{ user.superior.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recursive Children -->
        <div v-if="department.all_children && department.all_children.length > 0" class="space-y-2">
            <DepartmentNode 
                v-for="child in department.all_children" 
                :key="child.id" 
                :department="child" 
                @add-sub="(id) => $emit('add-sub', id)"
                @delete-dept="(dept) => $emit('delete-dept', dept)"
                @edit-user="(user) => $emit('edit-user', user)"
                @user-dropped="(data) => $emit('user-dropped', data)"
                @edit-dept="(dept) => $emit('edit-dept', dept)"
                :canEdit="canEdit"
                :canManageHierarchy="canManageHierarchy"
                :isDescendant="isDescendant"
            />
        </div>
    </div>
</template>
