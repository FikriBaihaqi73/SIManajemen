<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Briefcase, Plus, Search, Folder, Eye, Pencil, Trash2, X, AlertTriangle } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextAreaInput from '@/Components/TextAreaInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    projects: Array,
    canCreate: Boolean,
    users: Array // For member selection
});

const showingModal = ref(false);
const editingProject = ref(null);

// State for Delete Confirmation Modal
const confirmingProjectDeletion = ref(false);
const projectToDelete = ref(null);

const form = useForm({
    name: '',
    description: '',
    status: 'active',
    members: []
});

const searchMembers = ref('');

const filteredUsers = computed(() => {
    if (!searchMembers.value) return props.users;
    return props.users.filter(user => 
        user.name.toLowerCase().includes(searchMembers.value.toLowerCase()) ||
        user.role.toLowerCase().includes(searchMembers.value.toLowerCase())
    );
});

const openModal = (project = null) => {
    form.reset();
    editingProject.value = project;
    
    if (project) {
        form.name = project.name;
        form.description = project.description;
        form.status = project.status;
        // Note: Members are not loaded in the project list view, so we don't populate them for editing here.
        // We hide the members field in edit mode to avoid confusion.
    }
    
    showingModal.value = true;
};

const submit = () => {
    if (editingProject.value) {
        form.put(route('tasks.projects.update', editingProject.value.id), {
            onSuccess: () => {
                showingModal.value = false;
                form.reset();
                editingProject.value = null;
            }
        });
    } else {
        form.post(route('tasks.projects.store'), {
            onSuccess: () => {
                showingModal.value = false;
                form.reset();
            }
        });
    }
};

const confirmProjectDeletion = (project) => {
    projectToDelete.value = project;
    confirmingProjectDeletion.value = true;
};

const deleteProject = () => {
    if (projectToDelete.value) {
        router.delete(route('tasks.projects.destroy', projectToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeDeleteModal();
            },
            onError: (errors) => {
                alert('Failed to delete project.');
                console.error(errors);
                closeDeleteModal();
            }
        });
    }
};

const closeDeleteModal = () => {
    confirmingProjectDeletion.value = false;
    projectToDelete.value = null;
};
</script>

<template>
    <Head title="Projects" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-2">
                <Briefcase class="w-6 h-6 text-indigo-600" />
                <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                    Projects
                </h2>
            </div>
        </template>

        <template #actions>
            <button 
                v-if="canCreate"
                @click="openModal()"
                class="flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors shadow-sm"
            >
                <Plus class="w-4 h-4 mr-2" /> New Project
            </button>
        </template>

        <!-- Project Grid -->
        <div v-if="projects.length === 0" class="flex flex-col items-center justify-center py-20 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
            <Folder class="w-16 h-16 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-500">No projects found.</h3>
            <p v-if="canCreate" class="text-gray-400 text-sm mb-6">Start by creating a new project.</p>
            <PrimaryButton v-if="canCreate" @click="openModal()">Create Project</PrimaryButton>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="project in projects" :key="project.id" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow group relative flex flex-col">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg text-indigo-600 dark:text-indigo-400">
                        <Folder class="w-6 h-6" />
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <span :class="[
                            'px-2 py-1 text-xs font-bold rounded uppercase',
                            project.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
                        ]">
                            {{ project.status }}
                        </span>
                        
                        <!-- Admin Actions -->
                        <div v-if="canCreate" class="flex bg-gray-100 dark:bg-gray-700 rounded p-1">
                            <button @click="openModal(project)" class="p-1 text-gray-500 hover:text-indigo-600 transition-colors" title="Edit">
                                <Pencil class="w-3.5 h-3.5" />
                            </button>
                            <button @click="confirmProjectDeletion(project)" class="p-1 text-gray-500 hover:text-red-600 transition-colors" title="Delete">
                                <Trash2 class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>
                </div>
                
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2 truncate">{{ project.name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 line-clamp-2 min-h-[40px]">{{ project.description || 'No description provided.' }}</p>
                
                <div class="mt-auto flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-4">
                    <div class="text-xs text-gray-500 font-medium">
                        {{ project.tasks_count || 0 }} Tasks
                    </div>
                    <Link :href="route('tasks.projects.show', project.id)" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 flex items-center">
                        View Project <Eye class="w-4 h-4 ml-1" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Create/Edit Project Modal -->
        <Modal :show="showingModal" @close="showingModal = false" maxWidth="md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ editingProject ? 'Edit Project' : 'Create New Project' }}</h2>
                    <button @click="showingModal = false" class="text-gray-400 hover:text-gray-600"><X class="w-5 h-5" /></button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Project Name" />
                        <TextInput v-model="form.name" class="w-full mt-1" placeholder="e.g. Website Redesign" />
                    </div>
                    <div>
                        <InputLabel value="Description" />
                        <TextAreaInput v-model="form.description" class="w-full mt-1" rows="3" placeholder="Brief description..." />
                    </div>

                    <div v-if="editingProject">
                        <InputLabel value="Status" />
                        <select v-model="form.status" class="w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-md shadow-sm">
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    
                    <div v-if="!editingProject">
                        <div class="flex justify-between items-center mb-2">
                            <InputLabel value="Invite Members" class="mb-0" />
                        </div>
                        
                        <!-- Search Box -->
                        <div class="relative mb-2">
                             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Search class="h-4 w-4 text-gray-400" />
                            </div>
                            <TextInput 
                                v-model="searchMembers" 
                                class="w-full pl-10 py-1.5 text-sm" 
                                placeholder="Search by name..." 
                            />
                        </div>

                        <div class="border border-gray-300 dark:border-gray-700 rounded-lg max-h-48 overflow-y-auto p-2 bg-gray-50 dark:bg-gray-900">
                            <div v-if="filteredUsers.length === 0" class="text-xs text-gray-500 text-center py-2">
                                No members found.
                            </div>
                            <div v-for="user in filteredUsers" :key="user.id" class="flex items-center p-2 hover:bg-white dark:hover:bg-gray-800 rounded transition-colors cursor-pointer" @click="!form.members.includes(user.id) ? form.members.push(user.id) : form.members = form.members.filter(id => id !== user.id)">
                                <input 
                                    type="checkbox" 
                                    :value="user.id" 
                                    v-model="form.members" 
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    @click.stop
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 flex-1">{{ user.name }} <span class="text-xs text-gray-400">({{ user.role }})</span></span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Select members who can access this project.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showingModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="submit" :disabled="form.processing">{{ editingProject ? 'Save Changes' : 'Create Project' }}</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmingProjectDeletion" @close="closeDeleteModal" maxWidth="sm">
            <div class="p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <AlertTriangle class="h-6 w-6 text-red-600" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            Delete Project
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Are you sure you want to delete <span class="font-bold text-gray-800 dark:text-gray-200">"{{ projectToDelete?.name }}"</span>? This action cannot be undone. All tasks associated with this project will be permanently deleted.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:flex-row-reverse">
                    <DangerButton class="w-full justify-center" @click="deleteProject">
                        Delete Project
                    </DangerButton>
                    <SecondaryButton class="mt-3 w-full justify-center sm:mt-0" @click="closeDeleteModal">
                        Cancel
                    </SecondaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
