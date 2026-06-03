<script lang="ts" setup>
import { type Api } from "@zag-js/menu";
import { cn } from "@midoneui/core/utils/cn";
import { inject } from "vue";
import { Button } from "@/components/ui/button";
import { Slot } from "@/components/ui/slot";
import { menuTrigger } from "@midoneui/core/styles/menu.styles";
import { MenuIndicator } from ".";

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
  <Slot v-bind="{ ...api?.getTriggerProps(), ...props, ...$attrs }">
    <Button variant="ghost" v-if="!asChild" :class="cn(menuTrigger, className)">
      <slot />
      <MenuIndicator />
    </Button>
    <slot v-else />
  </Slot>
</template>
