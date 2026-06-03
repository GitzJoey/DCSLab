<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { datePickerRoot } from "@midoneui/core/styles/datepicker.styles";
import { provide, computed } from "vue";
import * as datepicker from "@zag-js/date-picker";
import type { Props } from "@zag-js/date-picker";
import { useMachine, normalizeProps } from "@zag-js/vue";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  asChild = false,
  open = undefined,
  ...props
} = defineProps<
  Partial<Props> & {
    class?: string;
    asChild?: boolean;
  }
>();

const service = useMachine(datepicker.machine, {
  ...props,
  open,
  id: crypto.randomUUID(),
});

const api = computed(() => datepicker.connect(service, normalizeProps));

provide("datepickerApi", api);
</script>

<template>
  <Slot
    :class="cn(datePickerRoot, className)"
    v-bind="{ ...props, ...$attrs, ...api.getRootProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
