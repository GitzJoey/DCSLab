<script setup lang="ts">
import * as lucideIcons from "lucide-vue-next";
import { twMerge } from "tailwind-merge";
import { computed, useAttrs, type SVGAttributes } from "vue";

export type Icon = keyof typeof lucideIcons;

interface LucideProps extends /* @vue-ignore */ SVGAttributes {
  icon: Icon;
  title?: string;
}

const props = defineProps<LucideProps>();

const attrs = useAttrs();

const computedClass = computed(() =>
  twMerge([
    "stroke-1.5 w-5 h-5",
    typeof attrs.class === "string" && attrs.class,
  ]),
);
</script>

<template>
  <component :is="lucideIcons[props.icon]" :class="computedClass" />
</template>
