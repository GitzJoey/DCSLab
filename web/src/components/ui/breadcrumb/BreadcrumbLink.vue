<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { breadcrumbLink } from "@midoneui/core/styles/breadcrumb.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <a :class="cn(className, breadcrumbLink)" v-bind="{ ...props, ...$attrs }">
    <slot />
  </a>
</template>
