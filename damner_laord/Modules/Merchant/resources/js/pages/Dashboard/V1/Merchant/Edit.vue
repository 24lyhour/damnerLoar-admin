<script setup lang="ts">
import { DamnerFormModal } from '@/components/DamnerFormModal';
import { update } from '@/routes/merchant/merchants';
import MerchantForm from '../../../../components/Dashboards/v1/MerchantForm.vue';
import type { MerchantModalProps } from '../../../../types';

// Rendered inside an InertiaUI modal (MerchantController@edit).
defineProps<MerchantModalProps>();
</script>

<template>
    <DamnerFormModal
        :action="update.form({ merchant: merchant.uuid })"
        title="Edit merchant details"
        :description="`Changes apply to ${merchant.name} straight away.`"
        submit-label="Save changes"
        size="lg"
    >
        <template #default="{ errors }">
            <MerchantForm :merchant="merchant" :errors="errors" />
        </template>
    </DamnerFormModal>
</template>
