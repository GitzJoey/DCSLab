<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { textarea } from "@midoneui/core/styles/textarea.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <textarea :class="cn(textarea, className)" v-bind="{ ...props }" />
</template>
