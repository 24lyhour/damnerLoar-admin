<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LoginForm from '@/components/auth/LoginForm.vue';
/* @chisel-passkeys */
import PasskeyVerify from '@/components/PasskeyVerify.vue';
/* @end-chisel-passkeys */

defineOptions({
    layout: {
        title: 'Login to your account',
        description: 'Use your admin work account to sign in',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Log in" />

    <div v-if="status" class="text-center text-sm font-medium text-emerald-600">
        {{ status }}
    </div>

    <!-- @chisel-passkeys -->
    <PasskeyVerify />
    <!-- @end-chisel-passkeys -->

    <LoginForm :can-reset-password="canResetPassword" />
</template>
