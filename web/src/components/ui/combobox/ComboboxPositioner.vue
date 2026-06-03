<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { comboboxPositioner } from "@midoneui/core/styles/combobox.styles";
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
  <Slot
    :class="cn(comboboxPositioner, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getPositionerProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
