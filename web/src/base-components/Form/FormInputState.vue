<script setup lang="ts">
import { computed, toRef } from 'vue';
import Lucide from '../Lucide';

export interface FormInputStateProps {
    isInvalid: boolean,
    isValidating: boolean,
}

const props = withDefaults(defineProps<FormInputStateProps>(), {
    isInvalid: false,
    isValidating: false,
});

const isInvalid = toRef(props, 'isInvalid');
const isValidating = toRef(props, 'isValidating');

const iconName = computed((): "Loader" | "X" | "Asterisk" => {
    if (isValidating.value) return "Loader";
    if (isInvalid.value) return "X";

    else return "Asterisk";
});

const show = computed(() => {
    if (isValidating.value) return true;
    if (isInvalid.value) return true;

    return false;
});
</script>

<template>
    <Lucide v-if="show" :icon="iconName"
        :class="{ 'mx-1': true, 'text-danger': iconName == 'X', 'animate-spin': iconName == 'Loader' }" />
</template>