<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Field,
    FieldDescription,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    canResetPassword: boolean;
}>();
</script>

<!-- shadcn-vue login-02 form, wired to Fortify's login route. -->
<template>
    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
    >
        <FieldGroup>
            <Field :data-invalid="!!errors.email || undefined">
                <FieldLabel for="email">Email</FieldLabel>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    v-focus
                    autocomplete="email"
                    placeholder="you@damnerlaor.com"
                    :aria-invalid="!!errors.email || undefined"
                />
                <FieldError :errors="[errors.email]" />
            </Field>

            <Field :data-invalid="!!errors.password || undefined">
                <div class="flex items-center">
                    <FieldLabel for="password">Password</FieldLabel>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="ml-auto text-sm"
                    >
                        Forgot your password?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    :aria-invalid="!!errors.password || undefined"
                />
                <FieldError :errors="[errors.password]" />
            </Field>

            <Field orientation="horizontal">
                <Checkbox id="remember" name="remember" />
                <FieldLabel for="remember" class="font-normal">
                    Remember this device
                </FieldLabel>
            </Field>

            <Field>
                <Button
                    type="submit"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Login
                </Button>
                <!-- @chisel-registration -->
                <FieldDescription class="text-center">
                    Don't have an account?
                    <TextLink :href="register()">Sign up</TextLink>
                </FieldDescription>
                <!-- @end-chisel-registration -->
            </Field>
        </FieldGroup>
    </Form>
</template>
