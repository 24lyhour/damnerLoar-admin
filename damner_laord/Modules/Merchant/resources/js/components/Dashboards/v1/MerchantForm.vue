<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { DamnerFormField } from '@/components/DamnerFormModal';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { EnumOption } from '@/types';
import type { MerchantListItem, MerchantTypeValue } from '../../../types';

const props = withDefaults(
    defineProps<{
        /** null when inviting a new merchant */
        merchant?: MerchantListItem | null;
        errors: Record<string, string>;
        /** Invite only: type choice and its default commission */
        types?: EnumOption<MerchantTypeValue>[];
        defaultCommission?: Partial<Record<MerchantTypeValue, number>>;
    }>(),
    {
        merchant: null,
        types: () => [],
        defaultCommission: () => ({}),
    },
);

const isInvite = computed(() => props.merchant === null);

const type = ref<MerchantTypeValue>(
    props.merchant?.type.value ?? 'bus_operator',
);
const commission = ref<string>(
    String(
        props.merchant?.commissionPercent ??
            props.defaultCommission[type.value] ??
            '',
    ),
);

// Inviting: follow the platform default for the chosen type (spec 11.1).
watch(type, (value) => {
    if (isInvite.value && props.defaultCommission[value] !== undefined) {
        commission.value = String(props.defaultCommission[value]);
    }
});
</script>

<!--
    Merchant fields, shared by the Invite and Edit modal pages
    (universe: HotelForm inside ModalForm). Field names match
    InviteMerchantRequest / UpdateMerchantRequest.
-->
<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <DamnerFormField
            v-if="isInvite"
            v-slot="field"
            name="type"
            label="Type"
            :error="errors.type"
            required
            class="sm:col-span-2"
        >
            <Select v-model="type" :name="field.name" required>
                <SelectTrigger
                    :id="field.id"
                    class="w-full"
                    :aria-invalid="field['aria-invalid']"
                >
                    <SelectValue placeholder="Choose a type" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="option in types"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </DamnerFormField>

        <DamnerFormField
            v-slot="field"
            name="name"
            label="Display name"
            :error="errors.name"
            required
            class="sm:col-span-2"
        >
            <Input v-bind="field" :default-value="merchant?.name ?? ''" />
        </DamnerFormField>

        <DamnerFormField
            v-slot="field"
            name="legal_name"
            label="Legal name"
            :error="errors.legal_name"
        >
            <Input v-bind="field" :default-value="merchant?.legalName ?? ''" />
        </DamnerFormField>

        <DamnerFormField
            v-slot="field"
            name="contact_name"
            label="Contact person"
            :error="errors.contact_name"
        >
            <Input
                v-bind="field"
                :default-value="merchant?.contactName ?? ''"
            />
        </DamnerFormField>

        <DamnerFormField
            v-slot="field"
            name="email"
            label="Email"
            :hint="isInvite ? 'The invite is sent here.' : undefined"
            :error="errors.email"
            :required="isInvite"
        >
            <Input
                v-bind="field"
                type="email"
                :default-value="merchant?.email ?? ''"
            />
        </DamnerFormField>

        <DamnerFormField
            v-slot="field"
            name="phone"
            label="Phone"
            :error="errors.phone"
        >
            <Input
                v-bind="field"
                type="tel"
                placeholder="+855 23 999 333"
                :default-value="merchant?.phone ?? ''"
            />
        </DamnerFormField>

        <DamnerFormField
            v-slot="field"
            name="website"
            label="Website"
            :error="errors.website"
            class="sm:col-span-2"
        >
            <Input v-bind="field" :default-value="merchant?.website ?? ''" />
        </DamnerFormField>

        <DamnerFormField
            v-slot="field"
            name="address"
            label="Address"
            :error="errors.address"
            class="sm:col-span-2"
        >
            <Input v-bind="field" :default-value="merchant?.address ?? ''" />
        </DamnerFormField>

        <DamnerFormField
            v-slot="field"
            name="city"
            label="City"
            :error="errors.city"
        >
            <Input v-bind="field" :default-value="merchant?.city ?? ''" />
        </DamnerFormField>

        <DamnerFormField
            v-slot="field"
            name="province"
            label="Province"
            :error="errors.province"
        >
            <Input v-bind="field" :default-value="merchant?.province ?? ''" />
        </DamnerFormField>

        <DamnerFormField
            v-slot="field"
            name="commission_percent"
            label="Commission (%)"
            hint="Overrides the platform default (bus 12 %, hotel 15 %)."
            :error="errors.commission_percent"
            required
        >
            <Input
                v-bind="field"
                v-model="commission"
                type="number"
                inputmode="decimal"
                min="0"
                max="50"
                step="0.01"
            />
        </DamnerFormField>
    </div>
</template>
