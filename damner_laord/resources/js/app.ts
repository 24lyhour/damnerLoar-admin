import { createInertiaApp } from '@inertiajs/vue3';
import { putConfig, renderApp } from '@inertiaui/modal-vue';
import { definePreset, palette } from '@primeuix/themes';
import Aura from '@primeuix/themes/aura';
import PrimeVue from 'primevue/config';
import { createApp } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import { resolvePage } from '@/lib/resolvePage';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Aura with DamnerLaor brand blue (#174A8B) as the primary colour
const BrandPreset = definePreset(Aura, {
    semantic: {
        primary: palette('#174A8B'),
    },
});

/*
 * InertiaUI Modal: controller actions return Inertia::modal(...) and open
 * as a right-hand slideover (spec p.2 "right drawer"). Persistent by
 * default: only the X button, Cancel or a successful save closes it.
 * The panel chrome lives in DamnerFormModal, so padding/panel are bare here.
 */
putConfig({
    type: 'slideover',
    navigate: false,
    slideover: {
        closeButton: false,
        closeExplicitly: true,
        closeOnClickOutside: false,
        maxWidth: 'xl',
        paddingClasses: '',
        panelClasses: 'bg-background text-foreground min-h-screen shadow-xl',
        position: 'right',
    },
    modal: {
        closeButton: false,
        closeExplicitly: true,
        closeOnClickOutside: false,
        maxWidth: 'lg',
        paddingClasses: '',
        panelClasses: 'bg-background text-foreground rounded-xl shadow-xl',
        position: 'center',
    },
});

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    // App pages + module pages ("merchant::Dashboard/V1/Merchant/Index")
    resolve: resolvePage,
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    setup({ el, App, props, plugin }) {
        // renderApp also resolves the modal pages; give it our async resolver.
        const app = createApp({
            render: renderApp(App, { ...props, resolveComponent: resolvePage }),
        });

        app.use(plugin);

        app.use(PrimeVue, {
            theme: {
                preset: BrandPreset,
                options: {
                    // Follow the app's appearance toggle, which sets .dark on <html>
                    darkModeSelector: '.dark',
                    cssLayer: {
                        name: 'primevue',
                        order: 'theme, base, primevue',
                    },
                },
            },
        });

        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });

        app.mount(el!);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
