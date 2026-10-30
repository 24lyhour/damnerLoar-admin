<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { SendHorizontal } from '@lucide/vue';
import SectionCard from '@/components/common/SectionCard.vue';
import { Button } from '@/components/ui/button';
import { FieldError } from '@/components/ui/field';
import { Textarea } from '@/components/ui/textarea';
import { formatDateTime } from '@/lib/format';
import { store } from '@/routes/merchant/merchants/notes';
import type { MerchantNote } from '../../../../../types';

defineProps<{
    merchantUuid: string;
    notes: MerchantNote[];
    /** Admin may add notes (policy "review") */
    editable: boolean;
}>();
</script>

<!-- Spec 03.2 "Internal notes": admins only, newest first. -->
<template>
    <SectionCard title="Internal notes" content-class="flex flex-col gap-3">
        <ul v-if="notes.length" class="flex max-h-80 flex-col gap-2 overflow-y-auto">
            <li v-for="note in notes" :key="note.uuid" class="rounded-lg bg-muted/60 px-3 py-2 text-sm">
                <p class="text-xs">
                    <span class="font-semibold">{{ note.author }}</span>
                    <span v-if="note.createdAt" class="text-muted-foreground">
                        · {{ formatDateTime(note.createdAt) }}
                    </span>
                </p>
                <p class="mt-0.5 whitespace-pre-line">{{ note.body }}</p>
            </li>
        </ul>
        <p v-else class="text-sm text-muted-foreground">No notes yet.</p>

        <Form v-if="editable" v-bind="store.form({ merchant: merchantUuid })" :options="{ preserveScroll: true }"
            reset-on-success class="flex flex-col gap-2" v-slot="{ errors, processing }">
            <Textarea name="body" rows="2" placeholder="Add a note…" aria-label="New internal note" required
                :aria-invalid="!!errors.body || undefined" />
            <FieldError :errors="[errors.body]" />
            <Button type="submit" size="sm" variant="outline" class="self-end" :disabled="processing">
                <SendHorizontal />
                Add note
            </Button>
        </Form>
    </SectionCard>
</template>
