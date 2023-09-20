<script setup lang="ts">
import { computed, toRef } from 'vue';
import Lucide from '../Lucide';

export interface FormInputStateProps {
    invalid: boolean,
    validating: boolean,
}

const props = withDefaults(defineProps<FormInputStateProps>(), {
    invalid: false,
    validating: false,
});

const invalid = toRef(props, 'invalid');
const validating = toRef(props, 'validating');

const iconName = computed((): "Loader" | "X" | "Check" => {
    if (validating.value) return "Loader";

    if (invalid.value) {
        return "X";
    } else {
        return "Check";
    }
});
</script>

<template>
    <Lucide :icon="iconName" :class="{ 'mx-1': true, 'animate-spin': iconName == 'Loader' }" />
</template>