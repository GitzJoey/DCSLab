<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { selectItemGroup } from "@midoneui/core/styles/select.styles";
import { Slot } from "@/components/ui/slot";
import type { Api } from "@zag-js/select";
import { provide, inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("selectApi");
const itemGroupId = { id: crypto.randomUUID() };

provide("selectItemGroup", props);
</script>

<template>
  <Slot
    :class="cn(selectItemGroup, className)"
    v-bind="{ ...api?.getItemGroupProps(itemGroupId), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else><slot /></div>
  </Slot>
</template>
