<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { switchThumb } from "@midoneui/core/styles/switch.styles";
import type { Api } from "@zag-js/switch";
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

const api = inject<Api>("switchApi");
</script>

<template>
  <Slot
    :class="cn(switchThumb, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getThumbProps() }"
  >
    <slot v-if="asChild" />
    <span v-else>
      <slot />
    </span>
  </Slot>
</template>
