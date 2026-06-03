<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { sliderHiddenInput } from "@midoneui/core/styles/slider.styles";
import type { Api, ThumbProps } from "@zag-js/slider";
import { inject } from "vue";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const api = inject<Api>("sliderApi");
const thumbProps = inject<ThumbProps>("sliderThumb");
</script>

<template>
  <input
    :class="cn(sliderHiddenInput, className)"
    v-bind="{ ...api?.getHiddenInputProps(thumbProps!), ...props, ...$attrs }"
  />
</template>
