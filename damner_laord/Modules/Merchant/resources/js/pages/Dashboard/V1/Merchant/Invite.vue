<script setup lang="ts">
import { DamnerFormModal } from '@/components/DamnerFormModal';
import { store } from '@/routes/merchant/merchants';
import MerchantForm from '../../../../components/Dashboards/v1/MerchantForm.vue';
import type { MerchantCreateProps } from '../../../../types';

// Rendered inside an InertiaUI modal (MerchantController@create).
defineProps<MerchantCreateProps>();
</script>

<template>
    <DamnerFormModal
        :action="store.form()"
        title="Invite merchant"
        description="Creates the merchant as Pending and emails them to finish their application in the merchant app."
        submit-label="Send invite"
        size="lg"
    >
        <template #default="{ errors }">
            <MerchantForm
                :errors="errors"
                :types="types"
                :default-commission="defaultCommission"
            />
        </template>
    </DamnerFormModal>
</template>
