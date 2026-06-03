<script lang="ts" setup>
import { type Api } from "@zag-js/popover";
import { cn } from "@midoneui/core/utils/cn";
import { inject } from "vue";
import { Slot } from "@/components/ui/slot";
import { popoverTitle } from "@midoneui/core/styles/popover.styles";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("popoverApi");
</script>

<template>
  <Slot
    :class="cn(popoverTitle, className)"
    v-bind="{ ...api?.getTitleProps(), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
