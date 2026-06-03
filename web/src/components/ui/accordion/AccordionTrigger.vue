<script lang="ts" setup>
import { ChevronDownIcon } from "lucide-vue-next";
import {
  accordionTrigger,
  accordionItemIndicator,
} from "@midoneui/core/styles/accordion.styles";
import { cn } from "@midoneui/core/utils/cn";
import { inject } from "vue";
import { Slot } from "@/components/ui/slot";
import type { Api, ItemProps } from "@zag-js/accordion";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("accordionApi");
const item = inject<ItemProps>("accordionItem");
</script>

<template>
  <Slot
    :class="cn([className, accordionTrigger])"
    v-bind="{ ...props, ...$attrs, ...api?.getItemTriggerProps(item!) }"
  >
    <slot v-if="asChild" />
    <button v-else>
      <slot />
      <div
        v-bind="api?.getItemIndicatorProps(item!)"
        :class="cn(accordionItemIndicator)"
      >
        <ChevronDownIcon />
      </div>
    </button>
  </Slot>
</template>
