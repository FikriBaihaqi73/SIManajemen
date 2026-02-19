<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutDashboard, 
    Users, 
    LogOut, 
    ChevronLeft, 
    ChevronRight,
    User as UserIcon,
    Shield,
    Network,
    Building2,
    Trello,
    Target,
    BarChart,
    ClipboardList,
    ChevronDown,
    Briefcase
} from 'lucide-vue-next';

const isCollapsed = ref(false);
const page = usePage();
const user = page.props.auth.user;

const navItems = ref([
    { 
        name: 'Dashboard', 
        icon: LayoutDashboard, 
        route: 'dashboard',
        active: 'dashboard'
    },
    { 
        name: 'Our Company', 
        icon: Building2, 
        route: 'company.index',
        active: 'company.*'
    },
    { 
        name: 'Business Model Canvas', 
        icon: Trello, 
        route: 'bmc.index',
        active: 'bmc.*',
        show: ['ceo', 'director', 'manajer'].includes(user.role)
    },
    { 
        name: 'Goals & OKR', 
        icon: Target, 
        route: 'okr.index',
        active: 'okr.*',
        show: ['ceo', 'director'].includes(user.role)
    },
    { 
        name: 'Struktur Organisasi', 
        icon: Network, 
        route: 'organization.index',
        active: 'organization.*',
        show: ['ceo', 'director', 'manajer', 'supervisor', 'staff'].includes(user.role)
    },
    // Task Management Section (Nested)
    {
        name: 'Task Management',
        icon: ClipboardList,
        show: true,
        isOpen: route().current('tasks.*'), // Auto-open if active
        children: [
            {
                name: 'Overview',
                icon: BarChart,
                route: 'tasks.overview',
                active: 'tasks.overview'
            },
            {
                name: 'Projects',
                icon: Briefcase,
                route: 'tasks.projects.index',
                active: 'tasks.projects.*'
            }
        ]
    },
    { 
        name: 'User Management', 
        icon: Users, 
        route: 'users.index',
        active: 'users.*',
        show: user.role === 'ceo'
    },
].filter(item => item.show !== false));
</script>

<template>
    <div class="flex h-screen bg-gray-100 dark:bg-gray-900 overflow-hidden">
        <!-- Sidebar -->
        <aside 
            :class="[
                'flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out z-30',
                isCollapsed ? 'w-20' : 'w-64'
            ]"
        >
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
                <Link :href="route('dashboard')" class="flex items-center space-x-3 overflow-hidden">
                    <ApplicationLogo class="h-8 w-8 fill-current text-indigo-600" />
                    <div v-if="!isCollapsed" class="flex flex-col min-w-0">
                        <span class="font-bold text-lg text-gray-800 dark:text-white truncate">
                            SIManajemen
                        </span>
                        <span class="font-regular text-xs text-gray-500 dark:text-gray-400 truncate leading-tight">
                            {{ user.company_name || 'PT Infraniaga Teknologi Indonesia' }}
                        </span>
                    </div>
                </Link>
                <button 
                    @click="isCollapsed = !isCollapsed"
                    class="p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500"
                >
                    <ChevronLeft v-if="!isCollapsed" class="w-5 h-5" />
                    <ChevronRight v-else class="w-5 h-5" />
                </button>
            </div>

            <!-- Sidebar Nav -->
            <nav class="flex-1 px-3 py-4 space-y-2 overflow-y-auto">
                <template v-for="item in navItems" :key="item.name">
                    
                    <!-- Parent Item with Children -->
                    <div v-if="item.children">
                        <button 
                            @click="item.isOpen = !item.isOpen"
                            :class="[
                                'w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors group',
                                item.isOpen && !isCollapsed ? 'bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
                            ]"
                        >
                            <div class="flex items-center">
                                <component :is="item.icon" :class="['w-5 h-5', (item.isOpen && !isCollapsed) ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-600']" />
                                <span v-if="!isCollapsed" class="ml-3 font-medium">{{ item.name }}</span>
                            </div>
                            <!-- Chevron -->
                            <component 
                                v-if="!isCollapsed" 
                                :is="item.isOpen ? ChevronDown : ChevronRight" 
                                class="w-4 h-4 text-gray-400"
                            />
                        </button>

                        <!-- Children -->
                        <div v-show="item.isOpen && !isCollapsed" class="mt-1 space-y-1 pl-4">
                            <Link
                                v-for="child in item.children"
                                :key="child.name"
                                :href="route(child.route)"
                                :class="[
                                    'flex items-center px-3 py-2 rounded-lg transition-colors text-sm',
                                    route().current(child.active) 
                                        ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-medium' 
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800'
                                ]"
                            >
                                <component :is="child.icon" class="w-4 h-4 mr-3 opacity-70" />
                                <span>{{ child.name }}</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Single Item -->
                    <Link
                        v-else
                        :href="route(item.route)"
                        :class="[
                            'flex items-center px-3 py-2.5 rounded-lg transition-colors group',
                            route().current(item.active) 
                                ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' 
                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
                        ]"
                    >
                        <component :is="item.icon" :class="['w-5 h-5', route().current(item.active) ? '' : 'text-gray-400 group-hover:text-gray-600']" />
                        <span v-if="!isCollapsed" class="ml-3 font-medium">{{ item.name }}</span>
                    </Link>
                </template>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                <div v-if="!isCollapsed" class="px-2 pb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                            {{ user.name.charAt(0) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">
                                {{ user.name }}
                            </p>
                            <div class="flex items-center text-[10px] text-gray-500 uppercase tracking-widest font-bold">
                                <Shield class="w-2.5 h-2.5 mr-1" />
                                <span>{{ user.role }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <Link 
                    :href="route('profile.edit')"
                    :class="[
                        'flex items-center w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-400',
                        isCollapsed ? 'justify-center' : ''
                    ]"
                >
                    <UserIcon class="w-5 h-5" />
                    <span v-if="!isCollapsed" class="ml-3 text-sm font-medium">Profile</span>
                </Link>
                
                <Link 
                    :href="route('logout')" 
                    method="post" 
                    as="button"
                    :class="[
                        'flex items-center w-full p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400',
                        isCollapsed ? 'justify-center' : ''
                    ]"
                >
                    <LogOut class="w-5 h-5" />
                    <span v-if="!isCollapsed" class="ml-3 text-sm font-medium">Log Out</span>
                </Link>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header -->
            <header class="h-16 flex items-center justify-between px-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <h1 v-if="$slots.header" class="text-xl font-bold text-gray-800 dark:text-white">
                    <slot name="header" />
                </h1>
                <div class="flex items-center space-x-4">
                    <slot name="actions" />
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-8">
                <slot />
            </div>
        </main>
    </div>
</template>

