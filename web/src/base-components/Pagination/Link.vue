<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { computed, LiHTMLAttributes, useAttrs } from "vue";
import Button from "../Button";

interface LinkProps extends /* @vue-ignore */ LiHTMLAttributes {
  as?: string | object;
  active?: boolean;
}

const { as, active } = withDefaults(defineProps<LinkProps>(), {
  as: "a",
  active: false,
});

const attrs = useAttrs();

const computedClass = computed(() =>
  twMerge([
    "min-w-0 sm:min-w-[40px] shadow-none font-normal flex items-center justify-center border-transparent text-slate-800 sm:mr-2 dark:text-slate-300 px-1 sm:px-3",
    active && "!box font-medium dark:bg-darkmode-400",
    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <li class="flex-1 sm:flex-initial">
    <Button :as="as" :class="computedClass" v-bind="_.omit(attrs, 'class')">
      <slot></slot>
    </Button>
  </li>
</template>
