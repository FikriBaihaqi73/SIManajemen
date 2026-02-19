<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Trello, 
    Plus, 
    Pencil, 
    Trash2,
    StickyNote
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextAreaInput from '@/Components/TextAreaInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    items: Object,
    canEdit: Boolean
});

const bmcBlocks = [
    { key: 'key_partners', title: 'Key Partners', col: 'col-span-2', row: 'row-span-2' },
    { key: 'key_activities', title: 'Key Activities', col: 'col-span-2', row: 'row-span-1' },
    { key: 'value_propositions', title: 'Value Propositions', col: 'col-span-2', row: 'row-span-2' },
    { key: 'customer_relationships', title: 'Customer Relationships', col: 'col-span-2', row: 'row-span-1' },
    { key: 'customer_segments', title: 'Customer Segments', col: 'col-span-2', row: 'row-span-2' },
    { key: 'key_resources', title: 'Key Resources', col: 'col-span-2', row: 'row-span-1' },
    { key: 'channels', title: 'Channels', col: 'col-span-2', row: 'row-span-1' },
    { key: 'cost_structure', title: 'Cost Structure', col: 'col-span-5', row: 'row-span-1' },
    { key: 'revenue_streams', title: 'Revenue Streams', col: 'col-span-5', row: 'row-span-1' },
];

const colors = [
    { name: 'blue', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' },
    { name: 'green', class: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' },
    { name: 'yellow', class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' },
    { name: 'red', class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' },
    { name: 'purple', class: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' },
];

const showingModal = ref(false);
const editingItem = ref(null);
const currentBlock = ref('');

const form = useForm({
    content: '',
    color: 'blue',
    block: ''
});

const openModal = (block, item = null) => {
    editingItem.value = item;
    currentBlock.value = block;
    form.content = item ? item.content : '';
    form.color = item ? (item.color || 'blue') : 'blue';
    form.block = block;
    showingModal.value = true;
};

const submit = () => {
    if (editingItem.value) {
        form.put(route('bmc.update', editingItem.value.id), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('bmc.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const deleteItem = (id) => {
    if (confirm('Hapus item ini?')) {
        useForm({}).delete(route('bmc.destroy', id));
    }
};

const closeModal = () => {
    showingModal.value = false;
    editingItem.value = null;
    form.reset();
};
</script>

<template>
    <Head title="Business Model Canvas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-2">
                <Trello class="w-6 h-6 text-indigo-600" />
                <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                    Business Model Canvas
                </h2>
            </div>
        </template>

        <!-- Grid Layout -->
        <div class="grid grid-cols-10 gap-4 h-[calc(100vh-12rem)] min-h-[800px]">
            
            <!-- Generate Blocks -->
            <div 
                v-for="block in bmcBlocks" 
                :key="block.key"
                :class="[
                    block.col, 
                    block.row,
                    'bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden'
                ]"
            >
                <!-- Block Header -->
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-sm text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ block.title }}
                    </h3>
                    <button 
                        v-if="canEdit"
                        @click="openModal(block.key)"
                        class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full text-gray-400 hover:text-indigo-600 transition-colors"
                    >
                        <Plus class="w-4 h-4" />
                    </button>
                </div>

                <!-- Block Content (Sticky Notes) -->
                <div class="flex-1 p-4 overflow-y-auto space-y-3 bg-gray-50/30 dark:bg-gray-900/20">
                    <div 
                        v-for="item in (items[block.key] || [])" 
                        :key="item.id"
                        :class="[
                            'p-3 rounded-lg border shadow-sm group relative transition-all hover:shadow-md cursor-default',
                            colors.find(c => c.name === item.color)?.class || colors[0].class,
                            item.color === 'yellow' ? 'border-yellow-200 dark:border-yellow-800' :
                            item.color === 'green' ? 'border-green-200 dark:border-green-800' :
                            item.color === 'red' ? 'border-red-200 dark:border-red-800' :
                            item.color === 'purple' ? 'border-purple-200 dark:border-purple-800' :
                            'border-blue-200 dark:border-blue-800'
                        ]"
                    >
                        <p class="text-sm font-medium whitespace-pre-wrap">{{ item.content }}</p>
                        
                        <!-- Actions -->
                        <div v-if="canEdit" class="absolute top-2 right-2 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity bg-white/50 dark:bg-black/20 backdrop-blur-sm rounded p-0.5">
                            <button @click="openModal(block.key, item)" class="p-1 hover:text-indigo-700">
                                <Pencil class="w-3 h-3" />
                            </button>
                            <button @click="deleteItem(item.id)" class="p-1 hover:text-red-700">
                                <Trash2 class="w-3 h-3" />
                            </button>
                        </div>
                    </div>

                    <div v-if="(!items[block.key] || items[block.key].length === 0) && !canEdit" class="h-full flex items-center justify-center opacity-30">
                        <StickyNote class="w-8 h-8 text-gray-400" />
                    </div>
                </div>
            </div>

        </div>

        <!-- Add/Edit Modal -->
        <Modal :show="showingModal" @close="closeModal" maxWidth="sm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    {{ editingItem ? 'Edit Note' : 'Add New Note' }}
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Content" />
                        <TextAreaInput
                            v-model="form.content"
                            class="mt-1 block w-full"
                            rows="4"
                            placeholder="Write something..."
                            autofocus
                        />
                    </div>

                    <div>
                        <InputLabel value="Color" />
                        <div class="flex space-x-3 mt-2">
                            <button
                                v-for="color in colors"
                                :key="color.name"
                                type="button"
                                @click="form.color = color.name"
                                :class="[
                                    'w-8 h-8 rounded-full border-2 transition-all',
                                    color.class.split(' ')[0], // Get bg color class
                                    form.color === color.name ? 'border-gray-500 scale-110 shadow-md ring-2 ring-offset-2 ring-indigo-500' : 'border-transparent'
                                ]"
                            ></button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton @click="submit" :disabled="form.processing">Save</PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
