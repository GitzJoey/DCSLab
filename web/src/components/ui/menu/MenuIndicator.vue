<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { ChevronDown } from "lucide-vue-next";
import { menuIndicator } from "@midoneui/core/styles/menu.styles";
import { Slot } from "@/components/ui/slot";
import type { Api } from "@zag-js/menu";
import { inject } from "vue";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const api = inject<Api>("menuApi");
</script>

<template>
  <Slot
    :class="cn(menuIndicator, className)"
    v-bind="{ ...api?.getIndicatorProps(), ...props, ...$attrs }"
  >
    <slot v-if="$slots.default" />
    <ChevronDown v-else />
  </Slot>
</template>
