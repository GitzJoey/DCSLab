<script setup lang="ts">
import { toRef } from "vue";

interface LoadingOverlayProps {
    visible: boolean,
    transparent: boolean,
}

const props = withDefaults(defineProps<LoadingOverlayProps>(), {
    visible: false,
    transparent: false,
});

const visible = toRef(props, 'visible');
const transparent = toRef(props, 'transparent');
</script>

<template>
    <div :class="{ 'loading-container': visible }">
        <slot></slot>
        <div v-if="visible" :class="{ 'loading-overlay': true, 'loading-overlay-gray': !transparent }"></div>
    </div>
</template>