<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { selectIndicator } from "@midoneui/core/styles/select.styles";
import { ChevronDownIcon } from "lucide-vue-next";
import { Slot } from "@/components/ui/slot";
import type { Api } from "@zag-js/select";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("selectApi");
</script>

<template>
  <Slot
    :class="cn(selectIndicator, className)"
    v-bind="{ ...api?.getIndicatorProps(), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot v-if="$slots.default" />
      <ChevronDownIcon v-else class="size-3.5" />
    </div>
  </Slot>
</template>
