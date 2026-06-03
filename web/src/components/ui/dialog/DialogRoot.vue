<script lang="ts" setup>
import { provide, computed } from "vue";
import * as dialog from "@zag-js/dialog";
import type { Props } from "@zag-js/dialog";
import { useMachine, normalizeProps } from "@zag-js/vue";

const {
  class: className,
  asChild = false,
  open = undefined,
  closeOnInteractOutside = undefined,
  ...props
} = defineProps<
  Partial<Props> & {
    class?: string;
    asChild?: boolean;
  }
>();

const service = useMachine(dialog.machine, {
  ...props,
  get open() {
    return open;
  },
  closeOnInteractOutside,
  id: crypto.randomUUID(),
});

const api = computed(() => dialog.connect(service, normalizeProps));

provide("dialogApi", api);
</script>

<template>
  <slot />
</template>
