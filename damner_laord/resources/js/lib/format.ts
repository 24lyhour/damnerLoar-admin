const LOCALE = 'en-US';

const currencyFormatter = new Intl.NumberFormat(LOCALE, {
    style: 'currency',
    currency: 'USD',
});

const numberFormatter = new Intl.NumberFormat(LOCALE);

const longDateFormatter = new Intl.DateTimeFormat(LOCALE, {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

export function formatCurrency(value: number): string {
    return currencyFormatter.format(value);
}

export function formatNumber(value: number): string {
    return numberFormatter.format(value);
}

/** 8.2 -> "8.2%" (sign handled by the caller's icon). */
export function formatPercent(value: number, digits = 1): string {
    return `${Math.abs(value).toFixed(digits)}%`;
}

/** 0.3 -> "0.3 pt" (percentage-point change). */
export function formatPoints(value: number, digits = 1): string {
    return `${Math.abs(value).toFixed(digits)} pt`;
}

/** 102 -> "1:42" */
export function formatCountdown(totalSeconds: number): string {
    const seconds = Math.max(0, Math.floor(totalSeconds));
    const minutes = Math.floor(seconds / 60);

    return `${minutes}:${String(seconds % 60).padStart(2, '0')}`;
}

export function formatLongDate(iso: string): string {
    return longDateFormatter.format(new Date(iso));
}
