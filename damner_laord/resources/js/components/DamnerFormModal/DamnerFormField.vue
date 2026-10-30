<script setup lang="ts">
import { computed, useId } from 'vue';
import type { HTMLAttributes } from 'vue';
import {
    Field,
    FieldDescription,
    FieldError,
    FieldLabel,
} from '@/components/ui/field';
import { cn } from '@/lib/utils';
import type { DamnerFormFieldBindings } from './types';

const props = withDefaults(
    defineProps<{
        /** Submitted field name, also used for the error lookup */
        name: string;
        label: string;
        error?: string;
        hint?: string;
        required?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        error: undefined,
        hint: undefined,
        required: false,
        class: undefined,
    },
);

defineSlots<{
    default: (props: DamnerFormFieldBindings) => unknown;
}>();

const id = `field-${props.name}-${useId()}`;
const hintId = `${id}-hint`;

// One object: on <slot>, a `:name` attribute would pick the slot, not pass data.
const bindings = computed<DamnerFormFieldBindings>(() => ({
    id,
    name: props.name,
    required: props.required,
    'aria-invalid': props.error ? true : undefined,
    'aria-describedby': props.hint ? hintId : undefined,
}));
</script>

<!--
    Label + control + hint + error, wired together for screen readers.
    The control goes in the slot and receives id / name / aria attributes.
-->
<template>
    <Field :class="cn(props.class)" :data-invalid="error ? true : undefined">
        <FieldLabel :for="id">
            {{ label }}
            <span v-if="required" class="text-destructive" aria-hidden="true"
                >*</span
            >
        </FieldLabel>
        <slot v-bind="bindings" />
        <FieldDescription v-if="hint" :id="hintId">{{ hint }}</FieldDescription>
        <FieldError :errors="[error]" />
    </Field>
</template>
