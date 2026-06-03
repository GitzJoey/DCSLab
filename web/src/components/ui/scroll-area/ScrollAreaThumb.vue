<script lang="ts" setup>
import type { Api, ScrollbarProps } from "@zag-js/scroll-area";
import { inject } from "vue";
import { cn } from "@midoneui/core/utils/cn";
import { scrollAreaThumb } from "@midoneui/core/styles/scroll-area.styles";

const { class: className, ...props } = defineProps<
  ScrollbarProps & { class?: string }
>();

const api = inject<Api<any>>("scrollAreaApi");
</script>

<template>
  <div
    v-bind="{ ...api?.getThumbProps(props), ...props }"
    :class="cn(scrollAreaThumb, className)"
  />
</template>
