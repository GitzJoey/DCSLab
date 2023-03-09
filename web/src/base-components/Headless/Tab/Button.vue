<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import {
  withDefaults,
  computed,
  ButtonHTMLAttributes,
  useAttrs,
  inject,
} from "vue";
import { ProvideTab } from "./Tab/Provider.vue";
import { ProvideList } from "./List.vue";

interface ButtonProps extends ButtonHTMLAttributes {
  as?: string | object;
}

const { as } = withDefaults(defineProps<ButtonProps>(), {
  as: "a",
});

const attrs = useAttrs();

const tab = inject<ProvideTab>("tab");
const list = inject<ProvideList>("list");

const computedClass = computed(() =>
  twMerge([
    "cursor-pointer block appearance-none px-5 py-2.5 border border-transparent text-slate-700 dark:text-slate-400",
    tab?.selected.value && "text-slate-800 dark:text-white",

    // Default
    list?.variant == "tabs" &&
      "block border-transparent rounded-t-md dark:border-transparent",
    list?.variant == "tabs" &&
      tab?.selected.value &&
      "bg-white border-slate-200 border-b-transparent font-medium dark:bg-transparent dark:border-t-darkmode-400 dark:border-b-darkmode-600 dark:border-x-darkmode-400",
    list?.variant == "tabs" &&
      !tab?.selected.value &&
      "hover:bg-slate-100 dark:hover:bg-darkmode-400 dark:hover:border-transparent",

    // Pills
    list?.variant == "pills" && "rounded-md border-0",
    list?.variant == "pills" &&
      tab?.selected.value &&
      "bg-primary text-white font-medium",

    // Boxed tabs
    list?.variant == "boxed-tabs" &&
      "shadow-[0px_3px_20px_#0000000b] rounded-md",
    list?.variant == "boxed-tabs" &&
      tab?.selected.value &&
      "bg-primary text-white font-medium",

    // Link tabs
    list?.variant == "link-tabs" &&
      "border-b-2 border-transparent dark:border-transparent",
    list?.variant == "link-tabs" &&
      tab?.selected.value &&
      "border-b-primary font-medium dark:border-b-primary",

    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <component :is="as" :class="computedClass" v-bind="_.omit(attrs, 'class')">
    <slot></slot>
  </component>
</template>
