<script lang="ts" setup>
import * as popover from "@zag-js/popover";
import type { Props } from "@zag-js/popover";
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { normalizeProps, useMachine } from "@zag-js/vue";
import { computed, provide } from "vue";
import { popoverRoot } from "@midoneui/core/styles/popover.styles";

const {
  class: className,
  asChild = false,
  open = undefined,
  closeOnInteractOutside = undefined,
  ...props
} = defineProps<Partial<Props> & { class?: string; asChild?: boolean }>();

const service = useMachine(popover.machine, {
  ...props,
  open,
  closeOnInteractOutside,
  id: crypto.randomUUID(),
});
const api = computed(() => popover.connect(service, normalizeProps));

provide("popoverApi", api);
</script>

<template>
  <Slot :class="cn(popoverRoot, className)" v-bind="{ ...props, ...$attrs }">
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
