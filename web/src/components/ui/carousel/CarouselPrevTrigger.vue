<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { Button } from "@/components/ui/button";
import { ArrowLeft } from "lucide-vue-next";
import { carouselPrevTrigger } from "@midoneui/core/styles/carousel.styles";
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
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getPrevTriggerProps() }">
    <slot v-if="asChild" />
    <Button variant="ghost" v-else :class="cn(carouselPrevTrigger, className)">
      <slot v-if="$slots.default" />
      <ArrowLeft v-else />
    </Button>
  </Slot>
</template>
