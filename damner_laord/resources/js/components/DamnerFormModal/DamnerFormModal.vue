<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import type {
    FormComponentOptions,
    FormComponentProps,
    FormComponentSlotProps,
} from '@inertiajs/core';
import { Modal } from '@inertiaui/modal-vue';
import { CircleAlert, X } from '@lucide/vue';
import { computed, useTemplateRef } from 'vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import type { RouteFormDefinition } from '@/wayfinder';
import type { DamnerFormModalPlacement, DamnerFormModalSize } from './types';

const props = withDefaults(
    defineProps<{
        /** Wayfinder form, e.g. update.form({ merchant: uuid }) */
        action: RouteFormDefinition<
            'get' | 'post' | 'put' | 'patch' | 'delete'
        >;
        title: string;
        description?: string;
        submitLabel?: string;
        cancelLabel?: string;
        /** Red submit button for destructive actions (spec p.3) */
        destructive?: boolean;
        size?: DamnerFormModalSize;
        /** Right-hand slideover (default) or centred modal */
        placement?: DamnerFormModalPlacement;
        /** Close after a successful save */
        closeOnSuccess?: boolean;
        /** Clear the fields after a successful save */
        resetOnSuccess?: boolean;
        /** Server errors not tied to a field, shown in a banner (e.g. "status") */
        formErrorKeys?: string[];
        /** Change the data before it is sent */
        transform?: FormComponentProps['transform'];
        options?: FormComponentOptions;
    }>(),
    {
        description: undefined,
        submitLabel: 'Save',
        cancelLabel: 'Cancel',
        destructive: false,
        size: 'md',
        placement: 'right',
        closeOnSuccess: true,
        resetOnSuccess: false,
        formErrorKeys: () => [],
        transform: undefined,
        options: () => ({ preserveScroll: true }),
    },
);

const emit = defineEmits<{
    success: [];
    error: [];
}>();

defineSlots<{
    /** The fields. Gets errors, processing, isDirty, reset… from Inertia's <Form> */
    default: (props: FormComponentSlotProps) => unknown;
    /** Replace the Cancel / Submit buttons */
    footer?: (props: FormComponentSlotProps & { close: () => void }) => unknown;
}>();

const modal = useTemplateRef<{ close: () => void }>('modal');

/** InertiaUI max-width keys for each size. */
const maxWidth: Record<DamnerFormModalSize, string> = {
    sm: 'md',
    md: 'lg',
    lg: 'xl',
    xl: '3xl',
};

const formProps = computed(() =>
    props.transform
        ? { ...props.action, transform: props.transform }
        : props.action,
);

function close(): void {
    modal.value?.close();
}

function onSuccess(): void {
    emit('success');

    if (props.closeOnSuccess) {
        close();
    }
}

function formErrors(errors: Record<string, string>): string[] {
    return props.formErrorKeys
        .map((key) => errors[key])
        .filter((message): message is string => Boolean(message));
}
</script>

<!--
    Form inside an InertiaUI modal (universe's "ModalForm"). Use it as the
    root of a modal page that a controller returns with Inertia::modal():

    // Controller
    return Inertia::modal('merchant::Dashboard/V1/Merchant/Edit', $data)
        ->baseRoute('merchant.merchants.show', $merchant);

    // Edit.vue
    <DamnerFormModal :action="update.form({ merchant: merchant.uuid })" title="Edit">
        <template #default="{ errors }">
            <DamnerFormField v-slot="field" name="name" label="Name" :error="errors.name">
                <Input v-bind="field" :default-value="merchant.name" />
            </DamnerFormField>
        </template>
    </DamnerFormModal>

    Open it with <ModalLink :href="edit({ merchant: uuid })">. Opens as a
    right slideover by default; persistent (app.ts putConfig): only the X,
    Cancel or a successful save closes it. Header and footer stay put while
    the fields scroll.
-->
<template>
    <Modal
        ref="modal"
        :slideover="placement === 'right'"
        :max-width="maxWidth[size]"
    >
        <Form
            v-bind="formProps"
            :options="options"
            :reset-on-success="resetOnSuccess"
            class="flex max-h-screen min-h-0 flex-col"
            :class="placement === 'right' ? 'h-screen' : 'max-h-[90vh]'"
            @success="onSuccess"
            @error="emit('error')"
            v-slot="slotProps"
        >
            <header class="flex items-start gap-4 border-b px-6 py-4">
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-semibold">{{ title }}</h2>
                    <p
                        v-if="description"
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        {{ description }}
                    </p>
                </div>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    aria-label="Close"
                    @click="close"
                >
                    <X />
                </Button>
            </header>

            <div
                class="flex min-h-0 flex-1 flex-col gap-4 overflow-y-auto px-6 py-5"
            >
                <Alert
                    v-if="formErrors(slotProps.errors).length"
                    variant="destructive"
                >
                    <CircleAlert />
                    <AlertDescription>
                        <p
                            v-for="message in formErrors(slotProps.errors)"
                            :key="message"
                        >
                            {{ message }}
                        </p>
                    </AlertDescription>
                </Alert>

                <slot v-bind="slotProps" />
            </div>

            <footer class="flex justify-end gap-2 border-t px-6 py-4">
                <slot name="footer" v-bind="{ ...slotProps, close }">
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="slotProps.processing"
                        @click="close"
                    >
                        {{ cancelLabel }}
                    </Button>
                    <Button
                        type="submit"
                        :variant="destructive ? 'destructive' : 'default'"
                        :disabled="slotProps.processing"
                    >
                        <Spinner v-if="slotProps.processing" />
                        {{ submitLabel }}
                    </Button>
                </slot>
            </footer>
        </Form>
    </Modal>
</template>
