import type { DefineComponent } from 'vue';

type PageModule = { default: DefineComponent };
type PageLoader = () => Promise<PageModule>;

const appPages = import.meta.glob<PageModule>('../pages/**/*.vue');
const modulePages = import.meta.glob<PageModule>(
    '../../../Modules/*/resources/js/pages/**/*.vue',
);

/** "merchant::Dashboard/Merchant/Index" → loader, keyed case-insensitively by module. */
const moduleLoaders = new Map<string, PageLoader>(
    Object.entries(modulePages).map(([path, loader]) => {
        const match = path.match(
            /Modules\/([^/]+)\/resources\/js\/pages\/(.+)\.vue$/,
        );

        return [`${match?.[1].toLowerCase()}::${match?.[2]}`, loader];
    }),
);

/**
 * Inertia page resolver.
 * - "dashboard/Overview"                → resources/js/pages/dashboard/Overview.vue
 * - "merchant::Dashboard/Merchant/Index" → Modules/Merchant/resources/js/pages/Dashboard/Merchant/Index.vue
 */
export async function resolvePage(name: string): Promise<DefineComponent> {
    const [moduleName, pagePath] = name.split('::');
    const loader = pagePath
        ? moduleLoaders.get(`${moduleName.toLowerCase()}::${pagePath}`)
        : appPages[`../pages/${name}.vue`];

    if (!loader) {
        throw new Error(`Page not found: ${name}`);
    }

    return (await loader()).default;
}
