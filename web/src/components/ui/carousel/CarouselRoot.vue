<script lang="ts" setup>
import * as carousel from "@zag-js/carousel";
import { useMachine, normalizeProps } from "@zag-js/vue";
import type { Props } from "@zag-js/carousel";
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { carouselRoot } from "@midoneui/core/styles/carousel.styles";
import { computed, provide } from "vue";

const {
  class: className,
  defaultPage,
  slideCount,
  spacing = "2rem",
  allowMouseDrag = true,
  asChild = false,
  ...props
} = defineProps<Partial<Props> & { asChild?: boolean; class?: string }>();

const service = useMachine(carousel.machine, {
  defaultPage,
  slideCount,
  spacing,
  allowMouseDrag,
  ...props,
  id: crypto.randomUUID(),
});

const api = computed(() => carousel.connect(service, normalizeProps));

provide("carouselApi", api);
</script>

<template>
  <Slot
    :class="cn(carouselRoot, className)"
    v-bind="{ ...props, ...$attrs, ...api.getRootProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
