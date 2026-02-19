<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { 
    Plus, 
    GitBranch, 
    Building2, 
    User as UserIcon, 
    Edit2,
    Trash2,
    Search
} from 'lucide-vue-next';
import { computed } from 'vue';
import DepartmentNode from './Partials/DepartmentNode.vue';

const props = defineProps({
    departments: Array,
    allDepartments: Array,
    unassignedUsers: Array,
    allUsers: Array,
});

const searchQuery = ref('');
const page = usePage();

const canManageDepts = computed(() => {
    return ['ceo', 'director'].includes(page.props.auth.user.role);
});

const canManageHierarchy = computed(() => {
    return ['ceo', 'director', 'manajer'].includes(page.props.auth.user.role);
});

// Helper to check if a department is under another department hierarchy
const isDescendant = (parentDeptId, targetDeptId) => {
    if (!parentDeptId || !targetDeptId) return false;
    if (String(parentDeptId) === String(targetDeptId)) return true;
    
    let current = props.allDepartments.find(d => String(d.id) === String(targetDeptId));
    while (current && current.parent_id) {
        if (String(current.parent_id) === String(parentDeptId)) return true;
        current = props.allDepartments.find(d => String(d.id) === String(current.parent_id));
    }
    return false;
};

const showingDeptModal = ref(false);
const showingAssignModal = ref(false);
const showingDeleteModal = ref(false);
const deptToDelete = ref(null);
const selectedUser = ref(null);

const deptForm = useForm({
    id: null,
    name: '',
    parent_id: null,
});

// assignForm is no longer needed since the modal is read-only

const openDeptModal = (parentId = null, department = null) => {
    deptForm.reset();
    if (department) {
        deptForm.id = department.id;
        // Clean prefixes when editing (so we don't save "— — Marketing" back)
        deptForm.name = department.name.replace(/^[—\s-]+/, '').trim();
        deptForm.parent_id = department.parent_id;
    } else {
        deptForm.id = null;
        deptForm.parent_id = parentId;
    }
    showingDeptModal.value = true;
};

const submitDept = () => {
    if (deptForm.id) {
        deptForm.put(route('organization.departments.update', deptForm.id), {
            onSuccess: () => {
                showingDeptModal.value = false;
                deptForm.reset();
            },
        });
    } else {
        deptForm.post(route('organization.departments.store'), {
            onSuccess: () => {
                showingDeptModal.value = false;
                deptForm.reset();
            },
        });
    }
};

const openAssignModal = (user) => {
    selectedUser.value = user.id; // Store ID for reactive lookup
    showingAssignModal.value = true;
};

// Find fresh user data from props when modal is open
const currentUserDetail = computed(() => {
    if (!selectedUser.value) return null;
    return props.allUsers.find(u => u.id == selectedUser.value) || null;
});

const getDepartmentName = (id) => {
    if (!id) return 'Tanpa Departemen';
    if (!props.allDepartments) return 'Loading...';
    
    // Ensure both are compared as strings
    const dept = props.allDepartments.find(d => String(d.id) === String(id));
    // Clean up recursive prefixes (handles various hyphen/dash types)
    return dept ? dept.name.replace(/^[—\s-]+/, '').trim() : 'Tidak diketahui';
};

const getSuperiorName = (user) => {
    if (!user) return 'Loading...';
    if (!props.allUsers || !props.allDepartments) return 'Loading...';
    
    // 1. Direct superior if explicitly set
    if (user.superior_id) {
        const superior = props.allUsers.find(u => String(u.id) === String(user.superior_id));
        if (superior) return superior.name;
    }

    // 2. Hierarchical Fallback: If in a sub-department, the superior is functionally 
    // someone from the parent level (e.g. Director or Head of Parent Dept)
    if (user.department_id) {
        const currentDept = props.allDepartments.find(d => String(d.id) === String(user.department_id));
        if (currentDept && currentDept.parent_id) {
            // Find any user in the parent department
            const parentSuperior = props.allUsers.find(u => String(u.department_id) === String(currentDept.parent_id));
            if (parentSuperior) return parentSuperior.name + ' (Sesuai Hirarki)';
        }
    }

    return 'Tanpa Atasan (Top Level)';
};


const confirmDelete = (dept) => {
    deptToDelete.value = dept;
    showingDeleteModal.value = true;
};

const deleteDept = () => {
    router.delete(route('organization.departments.destroy', deptToDelete.value.id), {
        onSuccess: () => {
            showingDeleteModal.value = false;
            deptToDelete.value = null;
        },
    });
};

// Search filtering logic
const filterDepartmentRecursive = (dept, query) => {
    const lowerQuery = query.toLowerCase();
    
    // Check if any user in this department matches
    const matchingUsers = dept.users?.filter(user => 
        user.name.toLowerCase().includes(lowerQuery)
    ) || [];

    // Check children recursively
    const filteredChildren = dept.all_children?.map(child => 
        filterDepartmentRecursive(child, query)
    ).filter(child => child !== null) || [];

    // If this department matches, or has matching users, or has matching children
    if (dept.name.toLowerCase().includes(lowerQuery) || matchingUsers.length > 0 || filteredChildren.length > 0) {
        return {
            ...dept,
            users: matchingUsers, // Optional: show only matching users OR all users if dept matches. Let's show all for context if dept matches.
            // But if we want to "find employee", we should probably show the employee.
            // Let's keep original users but highlight them? No, let's filter them for clarity.
            all_children: filteredChildren
        };
    }

    return null;
};

const filteredDepartments = computed(() => {
    if (!searchQuery.value) return props.departments;
    
    return props.departments.map(dept => 
        filterDepartmentRecursive(dept, searchQuery.value)
    ).filter(dept => dept !== null);
});const filteredUnassignedUsers = computed(() => {
    if (!searchQuery.value) return props.unassignedUsers;
    const lowerQuery = searchQuery.value.toLowerCase();
    return props.unassignedUsers.filter(user => 
        user.name.toLowerCase().includes(lowerQuery)
    );
});

const onDragStart = (event, user) => {
    // Only CEO, Director, and Manager can drag
    if (!canManageHierarchy.value) return;

    // Manajer additional restriction: Territory
    if (page.props.auth.user.role === 'manajer') {
        const higherRoles = ['ceo', 'director', 'manajer'];
        if (higherRoles.includes(user.role)) {
            event.preventDefault();
            return;
        }
        
        // Cannot drag from other divisions
        if (user.department_id && !isDescendant(page.props.auth.user.department_id, user.department_id)) {
            event.preventDefault();
            return;
        }
    }

    event.dataTransfer.setData('userId', user.id);
    event.dataTransfer.effectAllowed = 'move';
};

const isUnassignedDragOver = ref(false);

const handleDrop = (data) => {
    // data contains { userId, departmentId }
    router.put(route('organization.users.hierarchy', data.userId), {
        department_id: data.departmentId,
        superior_id: null
    }, {
        preserveScroll: true,
    });
};

const handleUnassignedDrop = (event) => {
    event.preventDefault();
    isUnassignedDragOver.value = false;
    const userId = event.dataTransfer.getData('userId');
    if (userId) {
        router.put(route('organization.users.hierarchy', userId), {
            department_id: null,
            superior_id: null
        }, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Struktur Organisasi" />

    <AuthenticatedLayout>
        <template #header>
            Struktur Organisasi
        </template>

        <template #actions>
            <div class="flex items-center space-x-3">
                <div class="relative flex items-center">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <Search class="h-4 w-4 text-gray-400" />
                    </div>
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Cari karyawan..."
                        class="block w-64 pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                </div>
                <button 
                    v-if="canManageDepts"
                    @click="openDeptModal()"
                    class="flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors shadow-sm"
                >
                    <Plus class="w-4 h-4 mr-2" /> Tambah Departemen
                </button>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Organization Tree -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                        <GitBranch class="w-5 h-5 mr-2 text-indigo-500" />
                        Hirarki Perusahaan
                    </h3>
                </div>

                <div class="p-6">
                    <div v-if="filteredDepartments.length === 0" class="text-center py-12">
                        <div v-if="searchQuery" class="space-y-2">
                             <Search class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                             <p class="text-gray-500 font-medium">Tidak ada hasil untuk "{{ searchQuery }}"</p>
                             <button @click="searchQuery = ''" class="text-sm text-indigo-600 hover:text-indigo-700 font-bold">Bersihkan pencarian</button>
                        </div>
                        <div v-else>
                            <Building2 class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                            <p class="text-gray-500">Belum ada departemen yang dibuat.</p>
                        </div>
                    </div>

                    <div class="space-y-6" v-else>
                        <DepartmentNode 
                            v-for="dept in filteredDepartments" 
                            :key="dept.id" 
                            :department="dept" 
                            :isRoot="true"
                            @add-sub="openDeptModal"
                            @delete-dept="confirmDelete"
                            @edit-user="openAssignModal"
                            @user-dropped="handleDrop"
                            @edit-dept="(dept) => openDeptModal(null, dept)"
                            :canEdit="canManageDepts"
                            :canManageHierarchy="canManageHierarchy"
                            :isDescendant="isDescendant"
                        />
                    </div>
                </div>
            </div>

            <!-- Unassigned Employees Section -->
            <div 
                @dragover.prevent="isUnassignedDragOver = true"
                @dragleave="isUnassignedDragOver = false"
                @drop="handleUnassignedDrop"
                :class="[
                    'bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border transition-all duration-200',
                    isUnassignedDragOver ? 'border-amber-500 ring-2 ring-amber-500 scale-[1.01] bg-amber-50/50 dark:bg-amber-900/10' : 'border-gray-100 dark:border-gray-700'
                ]"
            >
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-amber-100/50 dark:bg-amber-900/10">
                    <h3 class="text-lg font-bold text-amber-800 dark:text-amber-500 flex items-center">
                        <UserIcon class="w-5 h-5 mr-2" />
                        Karyawan Perlu Penempatan
                        <span v-if="isUnassignedDragOver" class="ml-auto text-xs font-bold animate-pulse">Lepas untuk membatalkan penempatan</span>
                    </h3>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-if="filteredUnassignedUsers.length === 0" class="col-span-full text-center py-6 text-gray-500 italic">
                        {{ searchQuery ? 'Tidak ada karyawan yang cocok.' : 'Semua karyawan sudah memiliki departemen.' }}
                    </div>
                    
                    <div 
                        v-for="user in filteredUnassignedUsers" 
                        :key="user.id" 
                        :draggable="canManageHierarchy"
                        @dragstart="onDragStart($event, user)"
                        @click="openAssignModal(user)"
                        :class="[
                            'flex flex-col p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700 transition-all shadow-sm hover:shadow-md group',
                            canManageHierarchy ? 'cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-700' : 'cursor-default'
                        ]"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                {{ user.name.charAt(0) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ user.name }}</p>
                                <p class="text-[10px] text-gray-500 uppercase font-bold">{{ user.role }}</p>
                            </div>
                        </div>
                        <div class="mt-3 text-[10px] text-gray-400 font-medium italic">
                            Tarik ke divisi tujuan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Department Modal -->
        <Modal :show="showingDeptModal" @close="showingDeptModal = false" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">
                    {{ deptForm.id ? 'Edit Departemen' : 'Tambah Departemen Baru' }}
                </h2>

                <form @submit.prevent="submitDept" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama Departemen" />
                        <TextInput
                            id="name"
                            v-model="deptForm.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="deptForm.errors.name" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-8 space-x-3">
                        <SecondaryButton type="button" @click="showingDeptModal = false"> Batal </SecondaryButton>
                        <PrimaryButton type="submit" :class="{ 'opacity-25': deptForm.processing }" :disabled="deptForm.processing">
                            Simpan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Employee Detail Modal (Read-only) -->
        <Modal :show="showingAssignModal" @close="showingAssignModal = false" maxWidth="md">
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-2xl font-bold">
                        {{ currentUserDetail?.name.charAt(0) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ currentUserDetail?.name }}
                        </h2>
                        <p class="text-sm text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-wider">
                            {{ currentUserDetail?.role }}
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700">
                        <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">Departemen / Divisi</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white flex items-center">
                            <Building2 class="w-4 h-4 mr-2 text-gray-400" />
                            {{ getDepartmentName(currentUserDetail?.department_id) }}
                        </p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700">
                        <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">Atasan Langsung</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white flex items-center">
                            <UserIcon class="w-4 h-4 mr-2 text-gray-400" />
                            {{ getSuperiorName(currentUserDetail) }}
                        </p>
                    </div>
                </div>

                <div class="mt-8 bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg border border-indigo-100 dark:border-indigo-900/30">
                    <p class="text-[10px] text-indigo-600 dark:text-indigo-400 font-medium leading-relaxed italic">
                        * Penempatan karyawan dikelola sepenuhnya secara otomatis melalui fitur Drag and Drop di halaman Struktur Organisasi.
                    </p>
                </div>

                <div class="flex items-center justify-end mt-8">
                    <SecondaryButton @click="showingAssignModal = false"> Tutup </SecondaryButton>
                </div>
            </div>
        </Modal>
        
        <!-- Delete Confirmation Modal -->
        <Modal :show="showingDeleteModal" @close="showingDeleteModal = false" maxWidth="sm">
            <div class="p-6 text-center">
                <Trash2 class="w-12 h-12 text-red-500 mx-auto mb-4" />
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                    Hapus Departemen?
                </h2>
                <p class="text-sm text-gray-500 mb-6">
                    Apakah Anda yakin ingin menghapus <span class="font-bold">{{ deptToDelete?.name }}</span>? 
                    <br><span class="text-red-500 text-xs mt-2 block italic">Semua sub-departemen di dalamnya juga akan terhapus. Karyawan akan menjadi tidak terarah.</span>
                </p>

                <div class="flex items-center justify-center space-x-3">
                    <SecondaryButton @click="showingDeleteModal = false"> Batal </SecondaryButton>
                    <button 
                        @click="deleteDept"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors shadow-sm"
                    >
                        Hapus Sekarang
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
