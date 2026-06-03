<script lang="ts" setup>
import * as select from "@zag-js/select";
import type { Props } from "@zag-js/select";
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { normalizeProps, useMachine } from "@zag-js/vue";
import { computed, provide } from "vue";
import { selectRoot } from "@midoneui/core/styles/select.styles";
import { SelectHiddenSelect } from ".";

const {
  class: className,
  asChild = false,
  multiple = undefined,
  open = undefined,
  closeOnSelect = undefined,
  ...props
} = defineProps<Partial<Props> & { class?: string; asChild?: boolean }>();

const service = useMachine(select.machine, {
  ...props,
  multiple,
  open,
  closeOnSelect,
  id: crypto.randomUUID(),
});
const api = computed(() => select.connect(service, normalizeProps));

provide("selectApi", api);
</script>

<template>
  <Slot
    :class="cn(selectRoot, className)"
    :data-multiple="multiple"
    v-bind="{ ...props, ...$attrs, ...api.getRootProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
      <SelectHiddenSelect />
    </div>
  </Slot>
</template>
