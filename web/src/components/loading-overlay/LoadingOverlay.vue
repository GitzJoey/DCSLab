<script setup lang="ts">
import { toRef } from "vue";
import Spinner from "@/assets/images/spinner-big.gif";

interface LoadingOverlayProps {
    visible: boolean;
    transparent: boolean;
}

const props = withDefaults(defineProps<LoadingOverlayProps>(), {
    visible: false,
    transparent: false,
});

const visible = toRef(props, 'visible');
const transparent = toRef(props, 'transparent');
</script>

<template>
    <div :class="{ 'relative z-0 p-2 rounded-lg': visible }">
        <slot></slot>

        <div v-if="visible" :class="[
            'absolute inset-0 flex items-center justify-center bg-no-repeat bg-center z-150 opacity-50',
            { 'bg-gray-200': !transparent }
        ]">
            <img :src="Spinner" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-151"
                alt="DCSLab" />
        </div>
    </div>
</template>