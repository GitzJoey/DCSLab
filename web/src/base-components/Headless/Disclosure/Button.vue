<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { DisclosureButton as HeadlessDisclosureButton } from "@headlessui/vue";
import { useAttrs, computed, inject, watch } from "vue";
import { ProvideDisclosure } from "./Disclosure/Provider.vue";
import { ProvideGroup } from "./Group.vue";

interface ButtonProps
  extends /* @vue-ignore */ ExtractProps<typeof HeadlessDisclosureButton> {
  as?: string | object;
}

const { as } = withDefaults(defineProps<ButtonProps>(), {
  as: "button",
});

const disclosure = inject<ProvideDisclosure>("disclosure");
const group = inject<ProvideGroup>("group");

if (group) {
  watch(group, () => {
    group.value.selectedIndex !== disclosure?.value.index &&
      disclosure?.value.close();
  });
}

const attrs = useAttrs();
const computedClass = computed(() =>
  twMerge([
    "outline-none py-4 -my-4 font-medium w-full text-left dark:text-slate-400",
    disclosure?.value.open && "text-primary dark:text-slate-300",
    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <HeadlessDisclosureButton
    as="template"
    @click="
      () => {
        disclosure && group?.setSelectedIndex(disclosure.index);
      }
    "
  >
    <component :is="as" :class="computedClass" v-bind="_.omit(attrs, 'class')">
      <slot></slot>
    </component>
  </HeadlessDisclosureButton>
</template>
