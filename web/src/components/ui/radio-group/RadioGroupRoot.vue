<script lang="ts" setup>
import * as radioGroup from "@zag-js/radio-group";
import type { Props } from "@zag-js/radio-group";
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { normalizeProps, useMachine } from "@zag-js/vue";
import { computed, provide } from "vue";
import { radioGroupRoot } from "@midoneui/core/styles/radio-group.styles";
import { Dot } from "lucide-vue-next";
import { RadioGroupIndicator } from ".";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<Partial<Props> & { class?: string; asChild?: boolean }>();

const service = useMachine(radioGroup.machine, {
  ...props,
  id: crypto.randomUUID(),
});
const api = computed(() => radioGroup.connect(service, normalizeProps));

provide("radioGroupApi", api);
</script>

<template>
  <Slot
    :class="cn(radioGroupRoot, className)"
    v-bind="{ ...props, ...$attrs, ...api.getRootProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
      <RadioGroupIndicator>
        <Dot />
      </RadioGroupIndicator>
    </div>
  </Slot>
</template>
