<script setup lang="ts">
import * as lucideIcons from "lucide-vue-next";
import { SVGAttributes } from "vue";

export type Icon = keyof typeof lucideIcons;

interface LucideProps extends /* @vue-ignore */ SVGAttributes {
  icon: Icon;
  title?: string;
}

const props = defineProps<LucideProps>();
</script>

<template>
  <component :is="lucideIcons[props.icon]" class="stroke-1.5" />
</template>
