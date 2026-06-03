<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { Button } from "@/components/ui/button";
import { ArrowRight } from "lucide-vue-next";
import { carouselNextTrigger } from "@midoneui/core/styles/carousel.styles";
import type { Api } from "@zag-js/carousel";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("carouselApi");
</script>

<template>
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getNextTriggerProps() }">
    <slot v-if="asChild" />
    <Button variant="ghost" v-else :class="cn(carouselNextTrigger, className)">
      <slot v-if="$slots.default" />
      <ArrowRight v-else />
    </Button>
  </Slot>
</template>
