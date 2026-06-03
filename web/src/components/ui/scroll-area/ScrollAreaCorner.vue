<script lang="ts" setup>
import type { Api } from "@zag-js/scroll-area";
import { inject } from "vue";
import { cn } from "@midoneui/core/utils/cn";
import { scrollAreaCorner } from "@midoneui/core/styles/scroll-area.styles";

const { class: className, ...props } = defineProps<{ class?: string }>();

const api = inject<Api<any>>("scrollAreaApi");
</script>

<template>
  <div
    v-bind="{ ...api?.getCornerProps(), ...props }"
    :class="cn(scrollAreaCorner, className)"
  />
</template>
