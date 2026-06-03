<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { dialogPositioner } from "@midoneui/core/styles/dialog.styles";
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

const api = inject<Api>("dialogApi");
</script>

<template>
  <Slot
    :class="cn(dialogPositioner, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getPositionerProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
