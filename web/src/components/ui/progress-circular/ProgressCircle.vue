<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { progressCircle } from "@midoneui/core/styles/progress-circular.styles";
import type { Api } from "@zag-js/progress";
import { inject } from "vue";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const api = inject<Api>("progressApi");
</script>

<template>
  <svg
    :class="cn(progressCircle, className)"
    v-bind="{ ...api?.getCircleProps(), ...props, ...$attrs }"
  >
    <slot />
  </svg>
</template>
