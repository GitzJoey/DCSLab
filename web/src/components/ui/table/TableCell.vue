<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { tableCellVariants } from "@midoneui/core/styles/table.styles";
import { inject } from "vue";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const variant = inject<
  | {
      variant?: "default" | "boxed" | null;
      raised?: "single" | "double" | null;
    }
  | undefined
>("tableVariant", undefined);
</script>

<template>
  <td
    :class="cn(tableCellVariants({ ...variant, className }), className)"
    v-bind="{ ...props }"
  >
    <slot />
  </td>
</template>
