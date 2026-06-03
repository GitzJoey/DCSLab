<script lang="ts" setup>
import * as zagSwitch from "@zag-js/switch";
import type { Props } from "@zag-js/switch";
import { cn } from "@midoneui/core/utils/cn";
import { switchRoot } from "@midoneui/core/styles/switch.styles";
import { SwitchHiddenInput } from ".";
import { normalizeProps, useMachine } from "@zag-js/vue";
import { computed, provide } from "vue";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  asChild = false,
  checked = undefined,
  ...props
} = defineProps<Partial<Props> & { class?: string; asChild?: boolean }>();

const service = useMachine(zagSwitch.machine, {
  ...props,
  checked,
  id: crypto.randomUUID(),
});
const api = computed(() => zagSwitch.connect(service, normalizeProps));

provide("switchApi", api);
</script>

<template>
  <Slot
    :class="cn(switchRoot, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getRootProps() }"
  >
    <slot v-if="asChild" />
    <label v-else>
      <slot />
      <SwitchHiddenInput />
    </label>
  </Slot>
</template>
