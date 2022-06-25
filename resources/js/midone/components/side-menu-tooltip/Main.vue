<template>
  <Tippy :tag="tag" :options="{ placement: 'left', }" ref-key="sideMenuTooltipRef">
    <slot></slot>
  </Tippy>
</template>

<script setup>
import { provide, ref, onMounted } from "vue";
import dom from "@left4code/tw-starter/dist/js/dom";

const props = defineProps({
  tag: {
    type: String,
    default: "span",
  },
});

const tippyRef = ref();

provide("bind[sideMenuTooltipRef]", (el) => {
  tippyRef.value = el;
});

const toggleTooltip = () => {
  const el = tippyRef.value;
  if (dom(window).width() <= 1260) {
    el._tippy.enable();
  } else {
    el._tippy.disable();
  }
};

const initTooltipEvent = () => {
  window.addEventListener("resize", () => {
    toggleTooltip();
  });
};

onMounted(() => {
  toggleTooltip();
  initTooltipEvent();
});
</script>
