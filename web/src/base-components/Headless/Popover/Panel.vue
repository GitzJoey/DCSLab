<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import {
  PopoverPanel as HeadlessPopoverPanel,
  TransitionRoot,
} from "@headlessui/vue";
import { useAttrs, computed } from "vue";

interface PanelProps extends ExtractProps<typeof HeadlessPopoverPanel> {
  as?: string | object;
  placement?:
    | "top-start"
    | "top"
    | "top-end"
    | "right-start"
    | "right"
    | "right-end"
    | "bottom-end"
    | "bottom"
    | "bottom-start"
    | "left-start"
    | "left"
    | "left-end";
}

const { as } = withDefaults(defineProps<PanelProps>(), {
  as: "div",
  placement: "bottom-end",
});

const attrs = useAttrs();
const computedClass = computed(() =>
  twMerge([
    "p-2 shadow-[0px_3px_20px_#0000000b] bg-white border-transparent rounded-md dark:bg-darkmode-600 dark:border-transparent",
    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <TransitionRoot
    as="template"
    enter="transition-all ease-linear duration-150"
    enterFrom="mt-5 invisible opacity-0 translate-y-1"
    enterTo="mt-1 visible opacity-100 translate-y-0"
    entered="mt-1"
    leave="transition-all ease-linear duration-150"
    leaveFrom="mt-1 visible opacity-100 translate-y-0"
    leaveTo="mt-5 invisible opacity-0 translate-y-1"
  >
    <div
      :class="[
        'absolute z-30',
        { 'left-0 bottom-[100%]': placement == 'top-start' },
        { 'left-[50%] translate-x-[-50%] bottom-[100%]': placement == 'top' },
        { 'right-0 bottom-[100%]': placement == 'top-end' },
        { 'left-[100%] translate-y-[-50%]': placement == 'right-start' },
        { 'left-[100%] top-[50%] translate-y-[-50%]': placement == 'right' },
        { 'left-[100%] bottom-0': placement == 'right-end' },
        { 'top-[100%] right-0': placement == 'bottom-end' },
        { 'top-[100%] left-[50%] translate-x-[-50%]': placement == 'bottom' },
        { 'top-[100%] left-0': placement == 'bottom-start' },
        { 'right-[100%] translate-y-[-50%]': placement == 'left-start' },
        { 'right-[100%] top-[50%] translate-y-[-50%]': placement == 'left' },
        { 'right-[100%] bottom-0': placement == 'left-end' },
      ]"
    >
      <HeadlessPopoverPanel
        :as="as"
        :class="computedClass"
        v-bind="_.omit(attrs, 'class')"
      >
        <slot></slot>
      </HeadlessPopoverPanel>
    </div>
  </TransitionRoot>
</template>
