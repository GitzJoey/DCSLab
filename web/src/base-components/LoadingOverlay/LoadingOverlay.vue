<script setup lang="ts">

interface LoadingOverlayProps {
    visible: boolean,
    transparent: boolean,
}

const props = withDefaults(defineProps<LoadingOverlayProps>(), {
    visible: false,
    transparent: false,
});
</script>

<template>
    <div :class="{ 'loading-container': props.visible }">
        <slot></slot>
        <div v-if="props.visible" :class="{ 'loading-overlay': true, 'loading-overlay-gray': !props.transparent }"></div>
    </div>
</template>