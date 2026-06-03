<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { selectValueText } from "@midoneui/core/styles/select.styles";
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
  placeholder?: string;
}>();

const api = inject<Api>("selectApi");
</script>

<template>
  <Slot
    :class="cn(selectValueText, className)"
    v-bind="{ ...api?.getValueTextProps(), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>{{ api?.valueAsString || props.placeholder }}</div>
  </Slot>
</template>
