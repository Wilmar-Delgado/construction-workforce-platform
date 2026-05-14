<script setup>
import { Link } from '@inertiajs/vue3';
import { HardHat } from 'lucide-vue-next';
</script>

<template>
    <div class="guest-wrapper">

        <!-- Logo Section -->
        <div class="guest-logo">
            <Link href="/" class="logo-link">
                <HardHat class="logo-icon" />
                <span class="logo-text">Construction Workspace</span>
            </Link>
        </div>

        <!-- Card Container -->
        <div class="guest-card">
            <slot />
        </div>

    </div>
</template>

<style scoped>

/* Page background */
.guest-wrapper {
    min-height: 100vh;
    background: #ecf3ff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

/* Logo */
.guest-logo {
    margin-bottom: 24px;
}

.logo-link {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.logo-icon {
    width: 36px;
    height: 36px;
    color: #4f46e5;
}

.logo-text {
    font-size: 20px;
    font-weight: 600;
    color: #111827;
}

/* Card */
.guest-card {
    width: 100%;
    max-width: 420px;
    background: #ffffff;
    padding: 2rem;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

/* Mobile */
@media (max-width: 480px) {
    .guest-card {
        padding: 1.5rem;
    }

    .logo-text {
        font-size: 18px;
    }
}
</style>