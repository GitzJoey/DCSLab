<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { sheetDescription } from "@midoneui/core/styles/sheet.styles";
import type { Api } from "@zag-js/dialog";
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

const api = inject<Api>("sheetApi");
</script>

<template>
  <Slot
    :class="cn(sheetDescription, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getDescriptionProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
