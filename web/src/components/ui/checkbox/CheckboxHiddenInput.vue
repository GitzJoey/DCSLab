<script lang="ts" setup>
import type { Api } from "@zag-js/checkbox";
import { cn } from "@midoneui/core/utils/cn";
import { checkboxHiddenInput } from "@midoneui/core/styles/checkbox.styles";
import { inject } from "vue";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const api = inject<Api>("checkboxApi");
</script>

<template>
  <input
    :class="cn(checkboxHiddenInput, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getHiddenInputProps() }"
  />
</template>
