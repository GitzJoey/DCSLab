<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { paginationNextTrigger } from "@midoneui/core/styles/pagination.styles";
import { Slot } from "@/components/ui/slot";
import type { Api } from "@zag-js/pagination";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("paginationApi");
</script>

<template>
  <Slot
    :class="cn(paginationNextTrigger, className)"
    v-bind="{ ...api?.getNextTriggerProps(), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
