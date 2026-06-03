<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { switchLabel } from "@midoneui/core/styles/switch.styles";
import { label } from "@midoneui/core/styles/label.styles";
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
    :class="cn([label, switchLabel, className])"
    v-bind="{ ...props, ...$attrs, ...api?.getLabelProps() }"
  >
    <slot v-if="asChild" />
    <span v-else>
      <slot />
    </span>
  </Slot>
</template>
