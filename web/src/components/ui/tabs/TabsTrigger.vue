<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { tabsTrigger } from "@midoneui/core/styles/tabs.styles";
import type { Api, TriggerProps } from "@zag-js/tabs";
import { inject } from "vue";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<
  TriggerProps & {
    class?: string;
    asChild?: boolean;
  }
>();

const api = inject<Api>("tabsApi");
</script>

<template>
  <Slot
    :class="cn(tabsTrigger, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getTriggerProps(props) }"
  >
    <slot v-if="asChild" />
    <button v-else>
      <slot />
    </button>
  </Slot>
</template>
