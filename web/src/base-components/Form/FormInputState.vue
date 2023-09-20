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

const show = computed(() => {
    if (validating.value) return true;
    if (invalid.value) return true;

    return false;
});
</script>

<template>
    <Lucide v-if="show" :icon="iconName"
        :class="{ 'mx-1': true, 'text-danger': iconName == 'X', 'animate-spin': iconName == 'Loader' }" />
</template>