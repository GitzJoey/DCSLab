<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { selectItemIndicator } from "@midoneui/core/styles/select.styles";
import { Slot } from "@/components/ui/slot";
import type { Api, ItemProps } from "@zag-js/select";
import { Check } from "lucide-vue-next";
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
const item = inject<ItemProps>("selectItem");
</script>

<template>
  <Slot
    :class="cn(selectItemIndicator, className)"
    v-bind="{ ...api?.getItemIndicatorProps(item!), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot v-if="$slots.default" />
      <Check v-else class="size-3.5" />
    </div>
  </Slot>
</template>
