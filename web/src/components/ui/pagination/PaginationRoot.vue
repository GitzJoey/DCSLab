<script lang="ts" setup>
import * as pagination from "@zag-js/pagination";
import type { Props } from "@zag-js/pagination";
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { normalizeProps, useMachine } from "@zag-js/vue";
import { computed, provide } from "vue";
import { paginationRoot } from "@midoneui/core/styles/pagination.styles";

const {
  class: className,
  asChild = false,
  count,
  pageSize,
  siblingCount,
  ...props
} = defineProps<Partial<Props> & { class?: string; asChild?: boolean }>();

const service = useMachine(pagination.machine, {
  ...props,
  count,
  pageSize,
  siblingCount,
  id: crypto.randomUUID(),
});
const api = computed(() => pagination.connect(service, normalizeProps));

provide("paginationApi", api);
</script>

<template>
  <Slot
    :class="cn(paginationRoot, className)"
    v-bind="{ ...props, ...$attrs, ...api.getRootProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
