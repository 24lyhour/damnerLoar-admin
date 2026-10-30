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

const shortDateFormatter = new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
});

const dateTimeFormatter = new Intl.DateTimeFormat('en-GB', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
});

/** "2026-10-22T…" -> "22 Oct 2026" (spec tables). */
export function formatDate(iso: string): string {
    return shortDateFormatter.format(new Date(iso));
}

/** "2026-10-23T16:10…" -> "23 Oct, 16:10" (notes, activity). */
export function formatDateTime(iso: string): string {
    return dateTimeFormatter.format(new Date(iso));
}

/** 1258291 -> "1.2 MB" */
export function formatFileSize(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    const units = ['KB', 'MB', 'GB'];
    let size = bytes / 1024;
    let unit = 0;

    while (size >= 1024 && unit < units.length - 1) {
        size /= 1024;
        unit++;
    }

    return `${size.toFixed(size < 10 ? 1 : 0)} ${units[unit]}`;
}
