<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import { useTranslate } from '@/composables/useTranslate';
import { useUserRole } from '@/composables/useUserRole';
import { usePermissions } from '@/composables/usePermissions';
import { Menu, Home, User, Users, Search, LayoutDashboard, Briefcase, Calendar, ClipboardList, Settings as SettingsIcon, LogOut } from 'lucide-vue-next';

const sidebarOpen = ref(false);
const authStore = useAuthStore(); 
const { t } = useTranslate();
const { isSelfEmployed } = useUserRole();
const { can } = usePermissions();
</script>

<template>
    <div class="layout-container">

        <!-- SIDEBAR -->
        <aside :class="['sidebar', { open: sidebarOpen }]">
            <div class="logo">
                <Link :href="route('home')">
                    {{ t('app_name') }}
                </Link>
            </div>

            <nav class="nav-links">
                <Link
                    :href="route('home')"
                    class="nav-link"
                    :class="{ active: $page.url.startsWith('/home') }"
                >
                    <Home class="nav-icon" />
                    {{ t('home') }}
                </Link>

                <Link
                    :href="route('worker-profiles.index')"
                    class="nav-link"
                    :class="{ active: $page.url.startsWith('/worker-profiles') }"
                >
                    <User v-if="isSelfEmployed" class="nav-icon" />
                    <Users v-else class="nav-icon" />
                    {{ isSelfEmployed ? t('profile') : t('profiles') }}
                </Link>

                <Link
                    :href="route('availability.index')"
                    class="nav-link"
                    :class="{ active: $page.url.startsWith('/availability') }"
                >
                    <Calendar class="nav-icon" />
                    {{ t('availability') }}
                </Link>

                <Link
                    v-if="can('view_workers')"
                    :href="route('find-workers.index')"
                    class="nav-link"
                    :class="{ active: $page.url.startsWith('/find-workers') }"
                >
                    <Search class="nav-icon" />
                    {{ t('find_workers') }}
                </Link>

                <Link
                    :href="route('find-missions.index')"
                    class="nav-link"
                    :class="{ active: $page.url.startsWith('/find-missions') }"
                >
                    <Briefcase class="nav-icon" />
                    {{ t('find_missions') }}
                </Link>

                <Link
                    v-if="can('create_missions')"
                    :href="route('missions.index')"
                    class="nav-link"
                    :class="{ active: $page.url.startsWith('/missions') }"
                >
                    <ClipboardList class="nav-icon" />
                    {{ t('missions') }}
                </Link>

                <Link
                    :href="route('mission-management.index')"
                    class="nav-link"
                    :class="{ active: $page.url.startsWith('/mission-management') }"
                >
                    <LayoutDashboard class="nav-icon" />
                    {{ t('mission_management') }}
                </Link>

                <Link
                    :href="route('settings')"
                    class="nav-link"
                    :class="{ active: $page.url.startsWith('/settings') }"
                >
                    <SettingsIcon class="nav-icon" />
                    {{ t('settings') }}
                </Link>

                <!-- <Link
                    :href="route('users.index')"
                    class="nav-link"
                    :class="{ active: $page.url.startsWith('/users') }"
                >
                    Users
                </Link> -->
            </nav>

            <div class="logout-container">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="logout-btn"
                >
                    <LogOut class="nav-icon" />
                    {{ t('logout') }}
                </Link>
            </div>
        </aside>
        
        <div v-if="sidebarOpen" class="overlay" @click="sidebarOpen = false"></div>

        <!-- MAIN CONTENT -->
        <div class="content-wrapper">

            <!-- TOPBAR -->
            <header class="topbar">
                <div class="topbar-left">
                    <!-- Hamburger (mobile only) -->
                    <button class="menu-btn" @click="sidebarOpen = true">
                        <Menu />
                    </button>

                    <h1 class="topbar-title">
                        <slot name="title" />
                    </h1>
                </div>

                <p class="topbar-user">{{ authStore.userName }}</p>
            </header>

            <!-- PAGE CONTENT -->
            <main class="content-area">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
/* ===== Layout Container ===== */
.layout-container {
    display: flex;
    height: 100vh;
    overflow: hidden;
    background: #ecf3ff;
    color: #374151;
    position: relative;
}

.content-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
    overflow: hidden; /* important */
}

.content-area {
    flex: 1;
    overflow-y: auto;
}

/* ===== Sidebar ===== */
.sidebar {
    position: fixed;
    left: -240px;
    top: 0;
    height: 100%;
    width: 240px;
    background: #ffffff;
    border-right: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    transition: left 0.3s ease;
    z-index: 1000;
}

/* When open */
.sidebar.open {
    left: 0;
}

.overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 900;
}

/* Logo */
.logo {
    height: 64px;
    padding: 0 24px;
    display: flex;
    align-items: center;
    border-bottom: 1px solid #e5e7eb;
    font-size: 20px;
    font-weight: bold;
}

/* Navigation */
.nav-links {
    flex: 1;
    padding: 12px;
    overflow-y: auto;
    scroll-behavior: smooth;
}

/* Sidebar links layout */
.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    margin-bottom: 6px;
    border-radius: 10px;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease;
}

/* Icon size */
.nav-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    color: #6b7280;
}

/* Hover */
.nav-link:hover {
    background: #f1f5ff;
}

/* Active state */
.nav-link.active {
    background: #eef2ff;
    font-weight: 600;
    color: #4f46e5;
}

.nav-link.active .nav-icon {
    color: #4f46e5;
}

/* Logout button alignment */
.logout-btn {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Logout section */
.logout-container {
    padding: 16px;
    border-top: 1px solid #e5e7eb;
}

.logout-btn {
    width: 100%;
    padding: 10px 16px;
    border-radius: 4px;
    background: #fef2f2;
    color: #dc2626;
    text-align: left;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}

.logout-btn:hover {
    background: #fee2e2;
}

/* ===== Main Content ===== */
.content-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

/* Topbar */
.topbar {
    min-height: 64px;
    padding: 0 20px;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.menu-btn {
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
}

.topbar-title {
    font-size: 20px;
    font-weight: 600;
}

.topbar-user {
    color: #6b7280;
}

@media (min-width: 768px) {

    .sidebar {
        position: static;
        left: 0;
        height: 100%;
    }

    .overlay {
        display: none;
    }

    .menu-btn {
        display: none;
    }

    .topbar {
        padding: 0 35px;
    }

}
</style>