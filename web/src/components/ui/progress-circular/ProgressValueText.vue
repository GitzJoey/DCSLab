<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { progressValueText } from "@midoneui/core/styles/progress-circular.styles";
import type { Api } from "@zag-js/progress";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("progressApi");
</script>

<template>
  <div
    :class="cn(progressValueText, className)"
    v-bind="{ ...api?.getValueTextProps(), ...props, ...$attrs }"
  >
    {{ api?.valueAsString }}
  </div>
</template>
