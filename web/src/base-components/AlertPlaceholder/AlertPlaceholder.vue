<script setup lang="ts">
import { computed } from "vue";
import Alert from "../Alert";
import Lucide from "../Lucide";

export interface AlertPlaceholderType {
    [key: string]: string[]
}

export interface AlertPlaceholderProps {
    alertType: string,
    messages: Record<string, string> | null,
    title: string,
}

const props = withDefaults(defineProps<AlertPlaceholderProps>(), {
    alertType: 'danger',
    messages: null,
    title: 'An unexpected error occurred.'
});

const computedVariant = computed(() => {
    if (props.alertType == 'danger') return 'soft-danger';
    if (props.alertType == 'success') return 'soft-success';
    if (props.alertType == 'warning') return 'soft-warning';
    if (props.alertType == 'pending') return 'soft-pending';
    if (props.alertType == 'dark') return 'soft-dark';
});
</script>

<template>
    <div v-if="messages != null" class="mt-4">
        <Alert v-slot="{ dismiss }" :variant="computedVariant" class="flex items-center mb-2">
            <div class="flex flex-col">
                <div class="flex items-center">
                    <Lucide icon="AlertCircle" class="w-6 h-6 mr-2" />
                    {{ title }}
                    <Alert.DismissButton type="button" class="text-white" aria-label="Close" @click="dismiss">
                        <Lucide icon="X" class="w-4 h-4" />
                    </Alert.DismissButton>
                </div>
                <div class="mt-3 ml-12">
                    <ul class="list-disc">
                        <template v-for="e in messages">
                            <li v-for="(ee, eeIdx) in e" :key="eeIdx" class="ml-5">{{ ee }}</li>
                        </template>
                    </ul>
                </div>
            </div>
        </Alert>
    </div>
</template>