<script setup lang="ts">
import { HTMLAttributes, useSlots, provide } from "vue";

export type ProvideBreadcrumb = {
  light?: boolean;
};

interface BreadcrumbProps extends /* @vue-ignore */ HTMLAttributes {
  light?: boolean;
}

const slots = useSlots();

const { light } = defineProps<BreadcrumbProps>();

provide<ProvideBreadcrumb>("breadcrumb", {
  light: light,
});
</script>

<template>
  <nav class="flex" aria-label="breadcrumb">
    <ol
      :class="[
        'flex items-center text-primary dark:text-slate-300',
        { 'text-white/90': light },
      ]"
    >
      <component
        v-for="(item, key) in slots.default && slots.default()"
        :is="item"
        :index="key"
      />
    </ol>
  </nav>
</template>
