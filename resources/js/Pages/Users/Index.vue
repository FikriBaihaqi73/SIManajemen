<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Plus, Edit2, Trash2, Search, User as UserIcon, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    users: Array,
});

const showingUserModal = ref(false);
const showingDeleteModal = ref(false);
const editingUser = ref(null);
const userToDelete = ref(null);
const page = usePage();
const currentUser = computed(() => page.props.auth.user);
const search = ref('');

const filteredUsers = computed(() => {
    if (!search.value) return props.users;
    const query = search.value.toLowerCase();
    return props.users.filter(user => 
        user.name.toLowerCase().includes(query) || 
        user.email.toLowerCase().includes(query) ||
        user.role.toLowerCase().includes(query)
    );
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'staff',
});

const openCreateModal = () => {
    editingUser.value = null;
    form.reset();
    showingUserModal.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.password = ''; // Leave blank for edit unless changing
    showingUserModal.value = true;
};

const submit = () => {
    if (editingUser.value) {
        form.put(route('users.update', editingUser.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDelete = (user) => {
    userToDelete.value = user;
    showingDeleteModal.value = true;
};

const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(route('users.destroy', userToDelete.value.id), {
            onSuccess: () => {
                closeDeleteModal();
            },
            preserveScroll: true
        });
    }
};

const closeModal = () => {
    showingUserModal.value = false;
    form.reset();
};

const closeDeleteModal = () => {
    showingDeleteModal.value = false;
    userToDelete.value = null;
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header>
            User Management
        </template>

        <template #actions>
            <button 
                @click="openCreateModal"
                class="flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors shadow-sm"
            >
                <Plus class="w-4 h-4 mr-2" /> Add User
            </button>
        </template>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl">
            <!-- Search & Filters -->
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <div class="relative max-w-md">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Search users..."
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border-none rounded-lg focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm"
                    >
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ user.name }}</p>
                                        <p class="text-xs text-gray-500">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span 
                                    :class="[
                                        'px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider',
                                        user.role === 'ceo' ? 'bg-purple-100 text-purple-700' :
                                        user.role === 'director' ? 'bg-blue-100 text-blue-700' :
                                        user.role === 'manajer' ? 'bg-indigo-100 text-indigo-700' :
                                        user.role === 'supervisor' ? 'bg-teal-100 text-teal-700' :
                                        'bg-gray-100 text-gray-700'
                                    ]"
                                >
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button @click="openEditModal(user)" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors">
                                    <Edit2 class="w-4 h-4" />
                                </button>
                                <button 
                                    v-if="user.id !== currentUser.id"
                                    @click="confirmDelete(user)" 
                                    class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Modal -->
        <Modal :show="showingUserModal" @close="closeModal" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">
                    {{ editingUser ? 'Edit User' : 'Add New User' }}
                </h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="role" value="Role" />
                        <select 
                            id="role"
                            v-model="form.role"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        >
                            <option value="ceo">CEO</option>
                            <option value="director">Director</option>
                            <option value="manajer">Manajer</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="staff">Staff</option>
                        </select>
                        <InputError :message="form.errors.role" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="password" :value="editingUser ? 'Password (leave blank to keep current)' : 'Password'" />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            :required="!editingUser"
                        />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-8 space-x-3">
                        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ editingUser ? 'Update User' : 'Create User' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showingDeleteModal" @close="closeDeleteModal" maxWidth="sm">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <AlertCircle class="w-8 h-8 text-red-600 dark:text-red-400" />
                </div>
                
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                    Confirm Deletion
                </h3>
                
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
                    Are you sure you want to delete <span class="font-bold text-gray-900 dark:text-white">{{ userToDelete?.name }}</span>? 
                    This action cannot be undone.
                </p>

                <div class="flex items-center justify-center space-x-4">
                    <SecondaryButton @click="closeDeleteModal" class="w-32 justify-center">
                        Cancel
                    </SecondaryButton>
                    <DangerButton @click="deleteUser" class="w-32 justify-center">
                        Delete
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
