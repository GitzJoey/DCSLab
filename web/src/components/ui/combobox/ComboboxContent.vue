<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { comboboxContent } from "@midoneui/core/styles/combobox.styles";
import { Box } from "@/components/ui/box";
import { ComboboxPositioner } from ".";
import type { Api } from "@zag-js/combobox";
import { inject } from "vue";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("comboboxApi");
</script>

<template>
  <Teleport to="body">
    <ComboboxPositioner>
      <Slot v-bind="{ ...props, ...$attrs, ...api?.getContentProps() }">
        <slot v-if="asChild" />
        <Box v-else raised="single" :class="cn(comboboxContent, className)">
          <div><slot /></div>
        </Box>
      </Slot>
    </ComboboxPositioner>
  </Teleport>
</template>
