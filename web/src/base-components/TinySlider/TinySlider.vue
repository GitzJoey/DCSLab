<script setup lang="ts">
import _ from "lodash";
import { init, reInit } from "./tiny-slider";
import {
  TinySliderInstance,
  TinySliderSettings,
} from "tiny-slider/src/tiny-slider";
import { HTMLAttributes, ref, onMounted, inject } from "vue";

export interface TinySliderElement extends HTMLDivElement {
  tns: TinySliderInstance;
}

export type ProvideTinySlider = (el: TinySliderElement) => void;

export interface TinySliderProps extends /* @vue-ignore */ HTMLAttributes {
  refKey?: string;
  options?: TinySliderSettings;
}

const props = defineProps<TinySliderProps>();

const sliderRef = ref<TinySliderElement>();

const bindInstance = (el: TinySliderElement) => {
  if (props.refKey) {
    const bind = inject<ProvideTinySlider>(`bind[${props.refKey}]`, () => {});
    if (bind) {
      bind(el);
    }
  }
};

const vSliderDirective = {
  mounted(el: TinySliderElement) {
    init(el, props);
  },
  updated(el: TinySliderElement) {
    reInit(el);
  },
};

onMounted(() => {
  if (sliderRef.value) {
    bindInstance(sliderRef.value);
  }
});
</script>

<template>
  <div class="tiny-slider" v-slider-directive ref="sliderRef">
    <slot></slot>
  </div>
</template>
