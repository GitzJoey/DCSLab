<script lang="ts" setup>
import { type Api } from "@zag-js/popover";
import { cn } from "@midoneui/core/utils/cn";
import { inject } from "vue";
import { popoverArrow } from "@midoneui/core/styles/popover.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const api = inject<Api>("popoverApi");
</script>

<template>
  <div
    :class="cn(popoverArrow, className)"
    v-bind="{ ...api?.getArrowProps(), ...props, ...$attrs }"
  >
    <slot />
  </div>
</template>
