<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { avatarImage } from "@midoneui/core/styles/avatar.styles";
import { inject } from "vue";
import type { Api } from "@zag-js/avatar";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const api = inject<Api>("avatarApi");
</script>

<template>
  <img
    :class="cn(avatarImage, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getImageProps() }"
  />
</template>
