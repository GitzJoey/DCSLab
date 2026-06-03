<script lang="ts" setup>
import type { Api } from "@zag-js/avatar";
import { cn } from "@midoneui/core/utils/cn";
import { avatarFallback } from "@midoneui/core/styles/avatar.styles";
import { inject } from "vue";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  asChild?: boolean;
  class?: string;
}>();

const api = inject<Api>("avatarApi");
</script>

<template>
  <Slot
    :class="cn(avatarFallback, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getFallbackProps() }"
  >
    <slot v-if="asChild" />
    <span v-else>
      <slot />
    </span>
  </Slot>
</template>
