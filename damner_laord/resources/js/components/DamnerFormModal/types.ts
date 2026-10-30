export type DamnerFormModalSize = 'sm' | 'md' | 'lg' | 'xl';

/** "right" = drawer from the right edge (default, spec p.2); "center" = dialog */
export type DamnerFormModalPlacement = 'right' | 'center';

/** What DamnerFormField hands to its control: <Input v-bind="field" /> */
export type DamnerFormFieldBindings = {
    id: string;
    name: string;
    required: boolean;
    'aria-invalid': true | undefined;
    'aria-describedby': string | undefined;
};
