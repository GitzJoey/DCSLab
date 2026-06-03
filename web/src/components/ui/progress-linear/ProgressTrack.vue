<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { progressTrack } from "@midoneui/core/styles/progress-linear.styles";
import type { Api } from "@zag-js/progress";
import { inject } from "vue";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const api = inject<Api>("progressApi");
</script>

<template>
  <div
    :class="cn(progressTrack, className)"
    v-bind="{ ...api?.getTrackProps(), ...props, ...$attrs }"
  >
    <slot />
  </div>
</template>
