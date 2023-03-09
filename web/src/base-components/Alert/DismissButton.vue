<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { withDefaults, computed, ButtonHTMLAttributes, useAttrs } from "vue";

interface DismissButtonProps extends ButtonHTMLAttributes {
  as?: string | object;
}

const { as } = withDefaults(defineProps<DismissButtonProps>(), {
  as: "button",
});

const attrs = useAttrs();

const computedClass = computed(() =>
  twMerge([
    "text-slate-800 py-2 px-3 absolute right-0 my-auto mr-2",
    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <component
    :is="as"
    type="button"
    aria-label="Close"
    :class="computedClass"
    v-bind="_.omit(attrs, 'class')"
  >
    <slot></slot>
  </component>
</template>
