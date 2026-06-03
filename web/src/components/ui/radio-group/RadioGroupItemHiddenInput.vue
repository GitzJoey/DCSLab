<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { radioGroupItemHiddenInput } from "@midoneui/core/styles/radio-group.styles";
import type { Api, ItemProps } from "@zag-js/radio-group";
import { inject } from "vue";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const api = inject<Api>("radioGroupApi");
const itemProps = inject<ItemProps>("radioGroupItem");
</script>

<template>
  <input
    :class="cn(radioGroupItemHiddenInput, className)"
    v-bind="{ ...api?.getItemHiddenInputProps(itemProps!), ...props, ...$attrs }"
  />
</template>
