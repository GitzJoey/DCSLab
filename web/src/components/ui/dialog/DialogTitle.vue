<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { dialogTitle } from "@midoneui/core/styles/dialog.styles";
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
    :class="cn(dialogTitle, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getTitleProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
