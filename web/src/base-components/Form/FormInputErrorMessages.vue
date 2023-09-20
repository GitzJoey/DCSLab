<script setup lang="ts">
import { computed, toRef } from 'vue';

export interface FormInputErrorMessagesProps {
    messages: Array<string>,
}

const props = withDefaults(defineProps<FormInputErrorMessagesProps>(), {
    messages: () => { return []; },
});
</script>

<template>
    <div class="mt-1">
        <span class="text-danger">
            asdf
        </span>
        <br />
        <span class="text-danger">
            asdf
        </span>
    </div>
</template>