<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { selectHiddenSelect } from "@midoneui/core/styles/select.styles";
import type { Api } from "@zag-js/select";
import { inject } from "vue";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const api = inject<Api>("selectApi");
</script>

<template>
  <select
    :class="cn(selectHiddenSelect, className)"
    v-bind="{ ...api?.getHiddenSelectProps(), ...props, ...$attrs }"
  />
</template>
