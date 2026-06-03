<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { toastDescription } from "@midoneui/core/styles/toast.styles";
import { Slot } from "@/components/ui/slot";
import type { Api } from "@zag-js/toast";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("toastApi");
</script>

<template>
  <Slot
    :class="cn(toastDescription, className)"
    v-bind="{ ...api?.getDescriptionProps(), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
