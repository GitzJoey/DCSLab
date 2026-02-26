<script setup lang="ts">
  import { type RouterLinkProps } from 'vue-router';
  import { computed, type LiHTMLAttributes, inject } from 'vue';
  import { type ProvideBeradcrumb } from './Breadcrumb.vue';

  interface LinkProps extends /* @vue-ignore */ LiHTMLAttributes {
    to?: RouterLinkProps['to'];
    active?: boolean;
    index?: number;
  }

  const { to, active, index } = withDefaults(defineProps<LinkProps>(), {
    to: '',
    active: false,
    index: 0,
  });

  const breadcrumb = inject<ProvideBeradcrumb>('breadcrumb');

  const computedClass = computed(() => [
    index > 0 && 'relative ml-5 pl-0.5',
    breadcrumb &&
      !breadcrumb.light &&
      index > 0 &&
      "before:content-[''] before:w-[14px] before:h-[14px] before:bg-chevron-black before:transform before:rotate-[-90deg] before:bg-[length:100%] before:-ml-[1.125rem] before:absolute before:my-auto before:inset-y-0",
    breadcrumb &&
      breadcrumb.light &&
      index > 0 &&
      "before:content-[''] before:w-[14px] before:h-[14px] before:bg-chevron-white before:transform before:rotate-[-90deg] before:bg-[length:100%] before:-ml-[1.125rem] before:absolute before:my-auto before:inset-y-0",
    index > 0 && 'dark:before:bg-chevron-white',
    breadcrumb && !breadcrumb.light && active && 'text-slate-800 cursor-text dark:text-slate-400',
    breadcrumb && breadcrumb.light && active && 'text-white/70',
  ]);
</script>

<template>
  <li :class="computedClass">
    <RouterLink :to="to">
      <slot></slot>
    </RouterLink>
  </li>
</template>
