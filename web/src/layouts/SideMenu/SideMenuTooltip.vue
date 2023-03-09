<script setup lang="ts">
import _ from "lodash";
import { onMounted, withDefaults, ref, provide } from "vue";
import Tippy, { ProvideTippy } from "../../base-components/Tippy";
import { PopperElement } from "tippy.js";

interface SideMenuTooltipProps {
  refKey?: string;
  as?: string | object;
  content: string;
}

const toggleTooltip = (el: PopperElement) => {
  if (window.innerWidth <= 1260) {
    el._tippy?.enable();
  } else {
    el._tippy?.disable();
  }
};

const initTooltipEvent = (tippyRef: PopperElement) => {
  window.addEventListener("resize", () => {
    toggleTooltip(tippyRef);
  });
};

const props = withDefaults(defineProps<SideMenuTooltipProps>(), {
  as: "a",
});

const tippyRef = ref<PopperElement>();

provide<ProvideTippy>("bind[sideMenuTooltipRef]", (el) => {
  tippyRef.value = el;
});

onMounted(() => {
  if (tippyRef.value) {
    toggleTooltip(tippyRef.value);
    initTooltipEvent(tippyRef.value);
  }
});
</script>

<template>
  <Tippy
    :as="props.as"
    :content="props.content"
    :options="{
      placement: 'left',
    }"
    refKey="sideMenuTooltipRef"
  >
    <slot></slot>
  </Tippy>
</template>
