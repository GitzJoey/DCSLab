<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { useAttrs, computed } from "vue";

interface HeaderProps {
  as?: string | object;
}

const { as } = withDefaults(defineProps<HeaderProps>(), {
  as: "div",
});

const attrs = useAttrs();
const computedClass = computed(() =>
  twMerge(["p-2 font-medium", typeof attrs.class === "string" && attrs.class])
);
</script>

<template>
  <component :is="as" :class="computedClass" v-bind="_.omit(attrs, 'class')">
    <slot></slot>
  </component>
</template>
