<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { sheetTrigger } from "@midoneui/core/styles/sheet.styles";
import { Button } from "@/components/ui/button";
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
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getTriggerProps() }">
    <Button
      variant="secondary"
      look="outline"
      v-if="!asChild"
      :class="cn(sheetTrigger, className)"
    >
      <slot />
    </Button>
    <slot v-else />
  </Slot>
</template>
