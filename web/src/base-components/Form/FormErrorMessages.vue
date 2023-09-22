<script setup lang="ts">
import { computed, toRef } from 'vue';

export interface FormErrorMessagesProps {
    messages: string | undefined
}

const props = withDefaults(defineProps<FormErrorMessagesProps>(), {
    messages: '',
});

const messages = toRef(props, 'messages');

const visible = computed(() => {
    if (messages.value.length == 0) return false;

    return true;
});
</script>

<template>
    <span v-if="visible" class="mt-1 text-danger">{{ messages }}</span>
</template>