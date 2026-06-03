<script lang="ts" setup>
import * as menu from "@zag-js/menu";
import type { Props } from "@zag-js/menu";
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { normalizeProps, useMachine } from "@zag-js/vue";
import { computed, provide } from "vue";
import { menuRoot } from "@midoneui/core/styles/menu.styles";

const {
  class: className,
  asChild = false,
  closeOnSelect = false,
  open = undefined,
  ...props
} = defineProps<Partial<Props> & { class?: string; asChild?: boolean }>();

const service = useMachine(menu.machine, {
  ...props,
  open,
  closeOnSelect,
  id: crypto.randomUUID(),
});
const api = computed(() => menu.connect(service, normalizeProps));

provide("menuApi", api);
</script>

<template>
  <Slot :class="cn(menuRoot, className)" v-bind="{ ...props, ...$attrs }">
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
