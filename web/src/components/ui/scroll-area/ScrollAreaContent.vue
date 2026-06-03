<script lang="ts" setup>
import type { Api } from "@zag-js/scroll-area";
import { inject } from "vue";
import { cn } from "@midoneui/core/utils/cn";
import { scrollAreaContent } from "@midoneui/core/styles/scroll-area.styles";

const { class: className, ...props } = defineProps<{ class?: string }>();

const api = inject<Api<any>>("scrollAreaApi");
</script>

<template>
  <div
    v-bind="{ ...api?.getContentProps(), ...props }"
    :class="cn(scrollAreaContent, className)"
  >
    <slot />
  </div>
</template>
