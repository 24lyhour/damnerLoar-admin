export type Appearance = 'light' | 'dark' | 'system';
export type ResolvedAppearance = 'light' | 'dark';

export type AppVariant = 'header' | 'sidebar';

/** Spec: green = done, red = failed, grey = waiting (never orange), blue = in progress */
export type StatusTone = 'success' | 'error' | 'neutral' | 'brand';

export type DataTableColumn<Row> = {
    key: keyof Row & string;
    label: string;
    align?: 'left' | 'right' | 'center';
    class?: string;
};

export type FlashToast = {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
};
