<script lang="ts" setup>
import * as tooltip from "@zag-js/tooltip";
import type { Props } from "@zag-js/tooltip";
import { normalizeProps, useMachine } from "@zag-js/vue";
import { computed, provide } from "vue";

const {
  class: className,
  asChild = false,
  open = undefined,
  disabled = false,
  ...props
} = defineProps<Partial<Props> & { class?: string; asChild?: boolean }>();

const service = useMachine(tooltip.machine, {
  ...props,
  positioning: {
    placement: "top",
    offset: { mainAxis: 10 },
  },
  closeDelay: 0,
  openDelay: 0,
  open,
  disabled,
  id: crypto.randomUUID(),
});
const api = computed(() => tooltip.connect(service, normalizeProps));

provide("tooltipApi", api);
</script>

<template>
  <slot />
</template>
