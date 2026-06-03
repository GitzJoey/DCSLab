<script lang="ts" setup>
import * as tabs from "@zag-js/tabs";
import type { Props } from "@zag-js/tabs";
import { cn } from "@midoneui/core/utils/cn";
import { tabsRoot } from "@midoneui/core/styles/tabs.styles";
import { normalizeProps, useMachine } from "@zag-js/vue";
import { computed, provide } from "vue";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<Partial<Props> & { class?: string; asChild?: boolean }>();

const service = useMachine(tabs.machine, {
  ...props,
  id: crypto.randomUUID(),
});
const api = computed(() => tabs.connect(service, normalizeProps));

provide("tabsApi", api);
</script>

<template>
  <Slot
    :class="cn(tabsRoot, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getRootProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
