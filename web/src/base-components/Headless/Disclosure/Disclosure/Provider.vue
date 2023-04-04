<script setup lang="ts">
import { computed, provide, ComputedRef } from "vue";

export type ProvideDisclosure = ComputedRef<{
  open: boolean;
  close: () => void;
  index: number;
}>;

interface ProviderProps {
  open: boolean;
  close: (ref?: HTMLElement) => void;
  index: number;
}

const props = withDefaults(defineProps<ProviderProps>(), {
  open: false,
  close: () => {},
  index: 0,
});

provide<ProvideDisclosure>(
  "disclosure",
  computed(() => {
    return {
      open: props.open,
      close: props.close,
      index: props.index,
    };
  })
);
</script>

<template>
  <slot></slot>
</template>
