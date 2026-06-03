<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { dialogCloseTrigger } from "@midoneui/core/styles/dialog.styles";
import { Button } from "@/components/ui/button";
import type { Api } from "@zag-js/dialog";
import {
  buttonVariants,
  type ButtonVariants,
} from "@midoneui/core/styles/button.styles";
import { X } from "lucide-vue-next";
import { inject } from "vue";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  look = "outline",
  variant = "secondary",
  size,
  asChild = false,
  ...props
} = defineProps<
  ButtonVariants & {
    class?: string;
    asChild?: boolean;
  }
>();

const api = inject<Api>("dialogApi");
</script>

<template>
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getCloseTriggerProps() }">
    <Button
      variant="ghost"
      v-if="!$slots.default"
      :class="cn(dialogCloseTrigger, className)"
      v-bind="{ ...props }"
    >
      <X class="size-4" />
    </Button>
    <template v-else>
      <slot v-if="asChild" />
      <Button
        v-else
        :class="
          cn(buttonVariants({ look, variant, size, className }), className)
        "
      >
        <slot />
      </Button>
    </template>
  </Slot>
</template>
