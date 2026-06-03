<script lang="ts" setup>
import { accordionRootVariants } from "@midoneui/core/styles/accordion.styles";
import { cn } from "@midoneui/core/utils/cn";
import { provide, computed } from "vue";
import * as accordion from "@zag-js/accordion";
import type { Props } from "@zag-js/accordion";
import { useMachine, normalizeProps } from "@zag-js/vue";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  variant = "default",
  asChild = false,
  collapsible = true,
  ...props
} = defineProps<
  Partial<Props> & {
    class?: string;
    variant?: "default" | "boxed";
    asChild?: boolean;
  }
>();

const service = useMachine(accordion.machine, {
  collapsible,
  ...props,
  id: crypto.randomUUID(),
});

const api = computed(() => accordion.connect(service, normalizeProps));

provide("accordionVariant", variant);
provide("accordionApi", api);
</script>

<template>
  <Slot
    :class="cn([className, accordionRootVariants({ variant, className })])"
    v-bind="{ ...props, ...$attrs, ...api.getRootProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
