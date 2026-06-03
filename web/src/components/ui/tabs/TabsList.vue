<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { tabsList } from "@midoneui/core/styles/tabs.styles";
import type { Api } from "@zag-js/tabs";
import { inject } from "vue";
import { Slot } from "@/components/ui/slot";
import { TabsIndicator } from ".";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("tabsApi");
</script>

<template>
  <Slot
    :class="cn(tabsList, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getListProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
      <TabsIndicator />
    </div>
  </Slot>
</template>
