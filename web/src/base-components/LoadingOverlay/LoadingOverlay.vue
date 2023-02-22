<script setup lang="ts">
import { toRef, computed } from "vue";

const props = defineProps({
    visible: {type: Boolean, default: false},
    transparent: {type: Boolean, default: false}
});

const visible = toRef(props, 'visible');
const transparent = toRef(props, 'transparent');
</script>

<template>
    <div :class="{ 'loading-container': visible }">
        <slot></slot>
        <div :class="{ 'loading-overlay': true, 'loading-overlay-gray': !transparent }" v-if="visible"></div>
    </div>
</template>