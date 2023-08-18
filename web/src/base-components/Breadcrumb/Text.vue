<script setup lang="ts">
import { computed, LiHTMLAttributes, inject } from "vue";
import { ProvideBreadcrumb } from "./Breadcrumb.vue";

interface LinkProps extends /* @vue-ignore */ LiHTMLAttributes {
  active?: boolean;
  index?: number;
}

const { to, active, index } = withDefaults(defineProps<LinkProps>(), {
  to: "",
  active: false,
  index: 0,
});

const breadcrumb = inject<ProvideBreadcrumb>("breadcrumb");

const computedClass = computed(() => [
  index > 0 && "relative ml-5 pl-0.5",
  breadcrumb &&
  !breadcrumb.light &&
  index > 0 &&
  "before:content-[''] before:w-[14px] before:h-[14px] before:bg-bredcrumb-chevron-dark before:bg-[length:100%] before:-ml-[1.125rem] before:absolute before:my-auto before:inset-y-0",
  breadcrumb &&
  breadcrumb.light &&
  index > 0 &&
  "before:content-[''] before:w-[14px] before:h-[14px] before:bg-bredcrumb-chevron-light before:bg-[length:100%] before:-ml-[1.125rem] before:absolute before:my-auto before:inset-y-0",
  index > 0 && "dark:before:bg-bredcrumb-chevron-darkmode",
  breadcrumb &&
  !breadcrumb.light &&
  active &&
  "text-slate-800 cursor-text dark:text-slate-400",
  breadcrumb && breadcrumb.light && active && "text-white/70",
]);
</script>

<template>
  <li :class="computedClass">
    <slot></slot>
  </li>
</template>
