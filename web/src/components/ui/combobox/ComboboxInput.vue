<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { comboboxInput } from "@midoneui/core/styles/combobox.styles";
import { Input } from "@/components/ui/input";
import type { Api } from "@zag-js/combobox";
import { inject } from "vue";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const api = inject<Api>("comboboxApi");
</script>

<template>
  <Input
    :class="cn(comboboxInput, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getInputProps() }"
  />
</template>
