<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import {
  tableVariants,
  type TableVariants,
} from "@midoneui/core/styles/table.styles";
import { provide, computed } from "vue";
import TableContainer from "./TableContainer.vue";

const { class: className, ...props } = defineProps<
  TableVariants & {
    class?: string;
  }
>();

provide(
  "tableVariant",
  computed(() => ({
    variant: props.variant,
    raised: props.raised,
  }))
);
</script>

<template>
  <TableContainer>
    <table
      :class="cn(tableVariants({ variant, raised, className }), className)"
      v-bind="{ ...props }"
    >
      <slot />
    </table>
  </TableContainer>
</template>
