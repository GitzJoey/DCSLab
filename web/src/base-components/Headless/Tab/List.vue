<script lang="ts">
export default {
  inheritAttrs: false,
};

type Variant = "tabs" | "pills" | "boxed-tabs" | "link-tabs";
</script>

<script setup lang="ts">
import _ from "lodash";
import { provide, useAttrs, computed } from "vue";
import { twMerge } from "tailwind-merge";
import { TabList as HeadlessTabList } from "@headlessui/vue";

export type ProvideList = {
  variant?: Variant;
};

interface ListProps
  extends /* @vue-ignore */ ExtractProps<typeof HeadlessTabList> {
  variant?: Variant;
}

const { variant } = withDefaults(defineProps<ListProps>(), {
  variant: "tabs",
});

const attrs = useAttrs();
const computedClass = computed(() =>
  twMerge([
    variant == "tabs" && "border-b border-slate-200 dark:border-darkmode-400",
    "w-full flex",
    typeof attrs.class === "string" && attrs.class,
  ])
);

provide<ProvideList>("list", {
  variant: variant,
});
</script>

<template>
  <HeadlessTabList
    as="ul"
    :class="computedClass"
    v-bind="_.omit(attrs, 'class')"
  >
    <slot></slot>
  </HeadlessTabList>
</template>
