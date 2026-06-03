<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { paginationEllipsis } from "@midoneui/core/styles/pagination.styles";
import { Slot } from "@/components/ui/slot";
import type { Api, EllipsisProps } from "@zag-js/pagination";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<
  EllipsisProps & {
    class?: string;
    asChild?: boolean;
  }
>();

const api = inject<Api>("paginationApi");
</script>

<template>
  <Slot
    :class="cn(paginationEllipsis, className)"
    v-bind="{ ...api?.getEllipsisProps(props), ...props, ...$attrs }"
  >
    <div v-if="!$slots.default">…</div>
    <template v-else>
      <slot v-if="asChild" />
      <div v-else>
        <slot />
      </div>
    </template>
  </Slot>
</template>
