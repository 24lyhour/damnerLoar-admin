import { onBeforeUnmount, onMounted, ref } from 'vue';
import type { Ref } from 'vue';

export type ChartTheme = {
    series: string[];
    text: string;
    grid: string;
    surface: string;
};

const SERIES_VARS = ['--chart-1', '--chart-2', '--chart-3', '--chart-4'];

/** "#174a8b" + 0.1 -> "#174a8b1a". Non-hex colours are returned unchanged. */
export function withAlpha(color: string, alpha: number): string {
    if (!/^#[0-9a-f]{6}$/i.test(color)) {
        return color;
    }

    const hex = Math.round(alpha * 255)
        .toString(16)
        .padStart(2, '0');

    return `${color}${hex}`;
}

function readVar(styles: CSSStyleDeclaration, name: string): string {
    return styles.getPropertyValue(name).trim();
}

function readTheme(): ChartTheme {
    const styles = getComputedStyle(document.documentElement);

    return {
        series: SERIES_VARS.map((name) => readVar(styles, name)),
        text: readVar(styles, '--muted-foreground'),
        grid: readVar(styles, '--border'),
        surface: readVar(styles, '--card'),
    };
}

/**
 * Chart.js cannot read CSS variables, so resolve the theme tokens to
 * real colours and refresh them when light / dark mode is toggled.
 */
export function useChartTheme(): Ref<ChartTheme | null> {
    const theme = ref<ChartTheme | null>(null);
    let observer: MutationObserver | null = null;

    onMounted(() => {
        theme.value = readTheme();
        observer = new MutationObserver(() => {
            theme.value = readTheme();
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });
    });

    onBeforeUnmount(() => observer?.disconnect());

    return theme;
}
