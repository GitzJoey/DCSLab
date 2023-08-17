<script setup lang="ts">
import { computed, provide, ComputedRef } from "vue";

export type ProvideTab = {
  selected: ComputedRef<boolean>;
};

interface ProviderProps {
  selected: boolean;
}

const props = withDefaults(defineProps<ProviderProps>(), {
  selected: false,
});

provide<ProvideTab>("tab", {
  selected: computed(() => props.selected),
});
</script>

<template>
  <slot></slot>
</template>
