<script lang="ts" setup>
import { accordionItemVariants } from "@midoneui/core/styles/accordion.styles";
import {
  boxVariants,
  type BoxVariants,
} from "@midoneui/core/styles/box.styles";
import { cn } from "@midoneui/core/utils/cn";
import { inject, provide } from "vue";
import { Slot } from "@/components/ui/slot";
import type { Api, ItemProps } from "@zag-js/accordion";

const {
  raised,
  class: className,
  asChild = false,
  ...props
} = defineProps<
  BoxVariants & ItemProps & { class?: string; asChild?: boolean }
>();

const variant = inject<"default" | "boxed">("accordionVariant", "default");
const api = inject<Api>("accordionApi");

provide("accordionItem", props);
</script>

<template>
  <Slot
    :class="
      cn([
        className,
        variant == 'boxed' ? boxVariants({ raised, className }) : '',
        accordionItemVariants({ variant, className }),
      ])
    "
    v-bind="{ ...props, ...$attrs, ...api?.getItemProps(props) }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
