<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { progressCircleRange } from "@midoneui/core/styles/progress-circular.styles";
import type { Api } from "@zag-js/progress";
import { inject } from "vue";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const api = inject<Api>("progressApi");
</script>

<template>
  <circle
    :class="cn(progressCircleRange, className)"
    v-bind="{ ...api?.getCircleRangeProps(), ...props, ...$attrs }"
  />
</template>
