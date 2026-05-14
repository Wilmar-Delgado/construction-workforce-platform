<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate';
import { Building2, Users, HardHat, CheckCircle2 } from 'lucide-vue-next'

const { t } = useTranslate();
const currentYear = new Date().getFullYear()

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
})
</script>

<template>
  <Head title="Welcome" />

    <!-- PAGE WRAPPER -->
    <div class="landing">

        <!-- NAVBAR -->
        <header class="navbar">
            <div class="brand">
                <div class="brand-icon"><HardHat size="20" /></div>
                <span>{{ t('app_name') }}</span>
            </div>

            <div class="nav-actions">
                <Link v-if="canLogin" :href="route('login')" class="btn-outline">
                {{ t('welcome_page.actions.login') }}
                </Link>
                <Link v-if="canRegister" :href="route('register')" class="btn-primary">
                {{ t('welcome_page.actions.register') }}
                </Link>
            </div>
        </header>

        <!-- HERO -->
        <section class="hero">
            <div class="hero-icon"><HardHat size="32" /></div>

            <h1>
                {{ t('app_name') }}
                <span>{{ t('welcome_page.hero_highlight') }}</span>
            </h1>

            <p>
                {{ t('welcome_page.subtitle') }}
            </p>
        </section>

        <!-- AUDIENCE -->
        <section class="audience">
            <h2>{{ t('welcome_page.audience_title') }}</h2>
            <p class="subtitle">
                {{ t('welcome_page.audience_subtitle') }}
            </p>

            <div class="audience-grid">
                <!-- Companies looking to hire -->
                <div class="audience-card">
                <div class="card-icon blue"><Building2 size="22" /></div>
                <br>
                <h3><strong>{{ t('welcome_page.audience.hiring.title') }}</strong></h3>
                <br>
                <p>{{ t('welcome_page.audience.hiring.description') }}</p>
                <ul>
                    <li><CheckCircle2 size="18" class="check-icon blue" />{{ t('welcome_page.audience.hiring.points.0') }}</li>
                    <li><CheckCircle2 size="18" class="check-icon blue" />{{ t('welcome_page.audience.hiring.points.1') }}</li>
                    <li><CheckCircle2 size="18" class="check-icon blue" />{{ t('welcome_page.audience.hiring.points.2') }}</li>
                </ul>
                </div>

                <!-- Companies with employees + Popular-->
                <div class="audience-card popular">
                <span class="badge">{{ t('welcome_page.badges.popular') }}</span>
                <div class="card-icon green"><Users size="22" /></div>
                <br>
                <h3><strong>{{ t('welcome_page.audience.employers.title') }}</strong></h3>
                <br>
                <p>{{ t('welcome_page.audience.employers.description') }}</p>
                <ul>
                    <li><CheckCircle2 size="18" class="check-icon green" />{{ t('welcome_page.audience.employers.points.0') }}</li>
                    <li><CheckCircle2 size="18" class="check-icon green" />{{ t('welcome_page.audience.employers.points.1') }}</li>
                    <li><CheckCircle2 size="18" class="check-icon green" />{{ t('welcome_page.audience.employers.points.2') }}</li>
                </ul>
                </div>

                <!-- Self-employed -->
                <div class="audience-card">
                    <div class="card-icon orange"><HardHat size="22" /></div>
                    <br>
                    <h3><strong>{{ t('welcome_page.audience.contractors.title') }}</strong></h3>
                    <br>
                    <p>{{ t('welcome_page.audience.contractors.description') }}</p>
                    <ul>
                        <li><CheckCircle2 size="18" class="check-icon orange" />{{ t('welcome_page.audience.contractors.points.0') }}</li>
                        <li><CheckCircle2 size="18" class="check-icon orange" />{{ t('welcome_page.audience.contractors.points.1') }}</li>
                        <li><CheckCircle2 size="18" class="check-icon orange" />{{ t('welcome_page.audience.contractors.points.2') }}</li>
                    </ul>
                </div>

            </div>
        </section>

        <!-- CTA -->
        <section class="cta">
            <h2>{{ t('welcome_page.cta.title') }}</h2>
            <p>{{ t('welcome_page.cta.subtitle') }}</p>

            <Link :href="route('register')" class="btn-cta">
                {{ t('welcome_page.cta.action') }} →
            </Link>
        </section>

        <!-- FOOTER -->
        <footer class="footer">
            © {{ currentYear }} {{ t('app_name') }}. <span v-html="t('welcome_page.footer_rights')"></span>
        </footer>
    </div>
</template>

<style scoped>
/* Page */
.landing {
    background: #ecf3ff;
    min-height: 100vh;
}

/* Navbar */
.navbar {
    height: 72px;
    padding: 0 20px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #e5e7eb;
    flex-wrap: wrap;
}

.brand {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
}

.brand-icon {
    background: #4f46e5;
    color: white;
    padding: 8px;
    border-radius: 10px;
}

/* Buttons */
.btn-outline {
    padding: 8px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: white;
}

.btn-primary {
    background: #4f46e5;
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    margin-left: 10px;
}

/* Hero */
.hero {
    text-align: center;
    padding: 20px 20px;
}

.hero-icon {
    background: #4f46e5;
    color: white;
    width: 72px;
    height: 72px;
    border-radius: 20px;
    margin: 0 auto 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
}

.hero h1 {
    font-size: 32px;
    font-weight: 800;
}

.hero h1 span {
    color: #4f46e5;
}

.hero p {
    max-width: 640px;
    margin: 16px auto 0;
    color: #6b7280;
}

/* Audience */
.audience {
    padding: 40px 20px;
    text-align: center;
}

.audience-grid {
    margin-top: 40px;
    display: grid;
    grid-template-columns: 1fr; /* mobile default */
    gap: 24px;
}

.audience-card {
    background: white;
    padding: 32px;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    position: relative;
    text-align: left;
}

.audience-card ul {
    margin-top: 16px;
    padding-left: 0;
    list-style: none;
}

.audience-card li {
    display: flex;
    align-items: flex-start; /* or center if you prefer */
    gap: 10px;
    margin-bottom: 8px;
}

.card-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-icon.green { background: #dcfce7; color: #16a34a; }
.card-icon.blue { background: #dbeafe; color: #2563eb; }
.card-icon.orange { background: #ffedd5; color: #ea580c; }

.check-icon {
    margin-top: 2px;
    flex-shrink: 0;
}

.check-icon.green { color: #22c55e; }
.check-icon.blue { color: #2563eb; }
.check-icon.orange { color: #ea580c; }

/* Popular */
.popular {
    border: 2px solid #4f46e5;
}

.badge {
    position: absolute;
    top: -10px;
    right: 16px;
    background: #4f46e5;
    color: white;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
}

/* CTA */
.cta {
    max-width: 900px;
    margin: 40px 20px;
    padding: 40px 20px;
    background: linear-gradient(135deg, #4f46e5, #2563eb);
    color: white;
    border-radius: 24px;
    text-align: center;
}

.btn-cta {
    display: inline-block;
    margin-top: 20px;
    background: white;
    color: #2563eb;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
}

/* Footer */
.footer {
    /* border-top: 1px solid black; */
    border-top: 1px solid color-mix(in srgb, black 25%, transparent);
    text-align: center;
    padding: 24px;
    color: #6b7280;
    max-width: 1200px;
    margin: auto;
}

.footer :deep(a) {
    color: #4f46e5;
    text-decoration: none;
    font-weight: 500;
}

.footer :deep(a:hover) {
    color: #6366f1;
    text-decoration: underline;
}

@media (max-width: 480px) {
    .navbar {
        flex-direction: column;
        align-items: flex-start;
        height: auto;
        padding: 16px 20px;
    }

    .nav-actions {
        width: 100%;
        margin-top: 10px;
        justify-content: flex-start;
    }
}

@media (min-width: 768px) {
    .navbar { padding: 0 70px; }
    .audience { padding: 10px 80px; }
    .cta { padding: 60px; margin: 40px auto; }

    .hero h1 {
        font-size: 48px;
        font-weight: 800;
    }

    .audience-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>