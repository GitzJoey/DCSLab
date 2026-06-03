<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import {
  TooltipRoot,
  TooltipTrigger,
  TooltipPositioner,
  TooltipContent,
} from "@/components/ui/tooltip";
import {
  badgeVariants,
  type BadgeVariants,
} from "@midoneui/core/styles/badge.styles";

const {
  class: className,
  look,
  variant,
  content,
  ...props
} = defineProps<
  BadgeVariants & {
    class?: string;
    content?: string;
  }
>();
</script>

<template>
  <TooltipRoot :disabled="!content">
    <TooltipTrigger as-child>
      <span
        :class="cn(badgeVariants({ look, variant, className }))"
        v-bind="{ ...props, ...$attrs }"
      >
        <slot />
      </span>
    </TooltipTrigger>
    <TooltipPositioner>
      <TooltipContent>{{ content }}</TooltipContent>
    </TooltipPositioner>
  </TooltipRoot>
</template>
