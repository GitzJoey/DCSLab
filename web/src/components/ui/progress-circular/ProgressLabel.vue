<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { progressLabel } from "@midoneui/core/styles/progress-circular.styles";
import { Label } from "@/components/ui/label";
import { Slot } from "@/components/ui/slot";
import type { Api } from "@zag-js/progress";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("progressApi");
</script>

<template>
  <Slot v-bind="{ ...api?.getLabelProps(), ...props, ...$attrs }">
    <Label v-if="!asChild" :class="cn(progressLabel, className)">
      <slot />
    </Label>
    <slot v-else />
  </Slot>
</template>
