<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { input } from "@midoneui/core/styles/input.styles";

const {
  class: className,
  type,
  ...props
} = defineProps<{
  class?: string;
  type?: string;
}>();
</script>

<template>
  <input :type="type" :class="cn(input, className)" v-bind="{ ...props }" />
</template>
