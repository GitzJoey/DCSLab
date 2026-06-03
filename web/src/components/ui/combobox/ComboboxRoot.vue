<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { comboboxRoot } from "@midoneui/core/styles/combobox.styles";
import { provide, computed } from "vue";
import * as combobox from "@zag-js/combobox";
import { useMachine, normalizeProps } from "@zag-js/vue";
import type { Props } from "@zag-js/combobox";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  multiple = false,
  selectionBehavior = "clear",
  value,
  asChild = false,
  onOpenChange,
  onInputValueChange,
  onValueChange,
  open = undefined,
  ...props
} = defineProps<Partial<Props> & { asChild?: boolean; class?: string }>();

const service = useMachine(combobox.machine, {
  multiple,
  selectionBehavior,
  onOpenChange,
  onInputValueChange,
  onValueChange,
  open,
  ...props,
  id: crypto.randomUUID(),
});

const api = computed(() => combobox.connect(service, normalizeProps));

provide("comboboxApi", api);
</script>

<template>
  <Slot
    :class="cn(comboboxRoot, className)"
    :data-multiple="multiple"
    v-bind="{ ...props, ...$attrs, ...api.getRootProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
