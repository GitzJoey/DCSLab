<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { breadcrumbList } from "@midoneui/core/styles/breadcrumb.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <ol :class="cn(className, breadcrumbList)" v-bind="{ ...props, ...$attrs }">
    <slot />
  </ol>
</template>
