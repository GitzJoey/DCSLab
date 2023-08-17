<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import {
  DisclosurePanel as HeadlessDisclosurePanel,
  TransitionRoot,
} from "@headlessui/vue";
import { useAttrs, computed } from "vue";

interface PanelProps
  extends /* @vue-ignore */ ExtractProps<typeof HeadlessDisclosurePanel> {
  as?: string | object;
}

const { as } = withDefaults(defineProps<PanelProps>(), {
  as: "div",
});

const attrs = useAttrs();
const computedClass = computed(() =>
  twMerge([
    "mt-3 text-slate-700 leading-relaxed dark:text-slate-400",
    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <TransitionRoot
    as="template"
    enter="overflow-hidden transition-all linear duration-[400ms]"
    enterFrom="mt-0 max-h-0 invisible opacity-0"
    enterTo="mt-3 max-h-[2000px] visible opacity-100"
    entered="mt-3"
    leave="overflow-hidden transition-all linear duration-500"
    leaveFrom="mt-3 max-h-[2000px] visible opacity-100"
    leaveTo="mt-0 max-h-0 invisible opacity-0"
  >
    <HeadlessDisclosurePanel as="template">
      <component
        :is="as"
        :class="computedClass"
        v-bind="_.omit(attrs, 'class')"
      >
        <slot></slot>
      </component>
    </HeadlessDisclosurePanel>
  </TransitionRoot>
</template>
