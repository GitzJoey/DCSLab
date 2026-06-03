<script lang="ts" setup>
import { type Api } from "@zag-js/menu";
import { cn } from "@midoneui/core/utils/cn";
import { inject } from "vue";
import { Box } from "@/components/ui/box";
import { Slot } from "@/components/ui/slot";
import { menuContent } from "@midoneui/core/styles/menu.styles";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("menuApi");
</script>

<template>
  <Slot
    :class="cn(menuContent, className)"
    v-bind="{ ...api?.getContentProps(), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <Box v-else raised="single" :class="cn(menuContent, className)">
      <div><slot /></div>
    </Box>
  </Slot>
</template>
