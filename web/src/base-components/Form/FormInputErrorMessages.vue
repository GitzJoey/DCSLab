<script setup lang="ts">
import { computed, toRef } from 'vue';
import messagesEn from '../../lang/messages.en';

export interface FormInputErrorMessagesProps {
    messages: string | undefined
}

const props = withDefaults(defineProps<FormInputErrorMessagesProps>(), {
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