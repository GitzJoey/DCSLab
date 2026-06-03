<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { switchHiddenInput } from "@midoneui/core/styles/switch.styles";
import type { Api } from "@zag-js/switch";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("switchApi");
</script>

<template>
  <input
    :class="cn(switchHiddenInput, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getHiddenInputProps() }"
  />
</template>
