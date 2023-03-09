<script setup lang="ts">
import { TabPanel as HeadlessTabPanel, TransitionRoot } from "@headlessui/vue";

interface PanelProps extends ExtractProps<typeof HeadlessTabPanel> {}

defineProps<PanelProps>();
</script>

<template>
  <HeadlessTabPanel as="template" v-slot="{ selected }">
    <TransitionRoot
      appear
      as="div"
      :show="selected"
      enter="transition-opacity duration-300"
      enterFrom="opacity-0"
      enterTo="opacity-100"
      leave="transition-opacity duration-300"
      leaveFrom="opacity-100"
      leaveTo="opacity-0"
    >
      <slot :selected="selected"></slot>
    </TransitionRoot>
  </HeadlessTabPanel>
</template>
