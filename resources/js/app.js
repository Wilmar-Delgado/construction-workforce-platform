import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { useAuthStore } from '@/stores/auth';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { router } from '@inertiajs/vue3';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin)
            .use(pinia)
            .use(ZiggyVue);

        //:: INIT STORE HERE (THIS IS KEY)
        const authStore = useAuthStore(pinia);
        authStore.setUser(props.initialPage.props.auth.user);

        router.on('success', (event) => {
            const user = event.detail.page.props.auth?.user || null;
            authStore.setUser(user);
        });

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
