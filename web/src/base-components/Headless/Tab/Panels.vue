<script setup lang="ts">
import { TabPanels as HeadlessTabPanels } from "@headlessui/vue";

interface PanelsProps
  extends /* @vue-ignore */ ExtractProps<typeof HeadlessTabPanels> {}

defineProps<PanelsProps>();
</script>

<template>
  <HeadlessTabPanels as="div">
    <slot></slot>
  </HeadlessTabPanels>
</template>
