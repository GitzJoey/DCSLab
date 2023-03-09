<script setup lang="ts">
import { inject } from "vue";
import { Tab as HeadlessTab } from "@headlessui/vue";
import Provider from "./Provider.vue";
import { ProvideList } from "../List.vue";

interface TabProps extends ExtractProps<typeof HeadlessTab> {
  fullWidth?: boolean;
}

const { fullWidth } = withDefaults(defineProps<TabProps>(), {
  fullWidth: true,
});

const list = inject<ProvideList>("list");
</script>

<template>
  <HeadlessTab as="template" v-slot="{ selected }">
    <li
      :class="[
        'focus-visible:outline-none',
        { 'flex-1': fullWidth },
        { '-mb-px': list && list.variant == 'tabs' },
      ]"
    >
      <Provider :selected="selected">
        <slot :selected="selected"></slot>
      </Provider>
    </li>
  </HeadlessTab>
</template>
