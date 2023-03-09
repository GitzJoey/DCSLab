<script setup lang="ts">
import { TabGroup as HeadlessTabGroup } from "@headlessui/vue";

interface GroupProps extends ExtractProps<typeof HeadlessTabGroup> {}

defineProps<GroupProps>();
</script>

<template>
  <HeadlessTabGroup as="div">
    <slot></slot>
  </HeadlessTabGroup>
</template>
