<script lang="ts">
export default {
  inheritAttrs: false,
};

type Size = "sm" | "md" | "lg" | "xl";
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { Dialog as HeadlessDialog, TransitionRoot } from "@headlessui/vue";
import { provide, useAttrs, computed, ref, Ref } from "vue";

export type ProvideSlideover = {
  open: boolean;
  zoom: Ref<boolean>;
  size?: Size;
};

interface SlideoverProps extends ExtractProps<typeof HeadlessDialog> {
  size?: Size;
  open: boolean;
  staticBackdrop?: boolean;
}

const props = withDefaults(defineProps<SlideoverProps>(), {
  as: "div",
  open: false,
  size: "md",
});

const { as, onClose, staticBackdrop, size } = props;
const open = computed(() => props.open);

const attrs = useAttrs();
const computedClass = computed(() =>
  twMerge(["relative z-[60]", typeof attrs.class === "string" && attrs.class])
);

const zoom = ref(false);
const emit = defineEmits<{
  (e: "close", value: boolean): void;
}>();

const handleClose = (value: boolean) => {
  if (!staticBackdrop) {
    onClose && onClose(value);
    emit("close", value);
  } else {
    zoom.value = true;
    setTimeout(() => {
      zoom.value = false;
    }, 300);
  }
};

provide<ProvideSlideover>("slideover", {
  open: open.value,
  zoom: zoom,
  size: size,
});
</script>

<template>
  <TransitionRoot appear as="template" :show="open">
    <HeadlessDialog
      :as="as"
      @close="
        (value) => {
          handleClose(value);
        }
      "
      :class="computedClass"
      v-bind="_.omit(attrs, 'class', 'onClose')"
    >
      <slot></slot>
    </HeadlessDialog>
  </TransitionRoot>
</template>
