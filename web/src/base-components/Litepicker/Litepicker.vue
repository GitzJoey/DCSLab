<script lang="ts">
type LitepickerConfig = Partial<ILPConfiguration>;
</script>

<script setup lang="ts">
import { InputHTMLAttributes, onMounted, ref, watch, inject } from "vue";
import { setValue, init, reInit } from "./litepicker";
import LitepickerJs from "litepicker";
import { FormInput } from "../../base-components/Form";
import { ILPConfiguration } from "litepicker/dist/types/interfaces";

export interface LitepickerElement extends HTMLInputElement {
  litePickerInstance: LitepickerJs;
}

export interface LitepickerEmit {
  (e: "update:modelValue", value: string): void;
}

export interface LitepickerProps extends InputHTMLAttributes {
  options: {
    format?: string | undefined;
  } & LitepickerConfig;
  modelValue: string;
  refKey?: string;
}

export type ProvideLitepicker = (el: LitepickerElement) => void;

const props = defineProps<LitepickerProps>();

const litepickerRef = ref<LitepickerElement>();

const tempValue = ref(props.modelValue);

const emit = defineEmits<LitepickerEmit>();

const vLitepickerDirective = {
  mounted(el: LitepickerElement) {
    setValue(props, emit);
    if (el !== null) {
      init(el, props, emit);
    }
  },
  updated(el: LitepickerElement) {
    if (tempValue.value !== props.modelValue && el !== null) {
      reInit(el, props, emit);
    }
  },
};

watch(props, () => {
  tempValue.value = props.modelValue;
});

const bindInstance = (el: LitepickerElement) => {
  if (props.refKey) {
    const bind = inject<ProvideLitepicker>(`bind[${props.refKey}]`);
    if (bind) {
      bind(el);
    }
  }
};

onMounted(() => {
  if (litepickerRef.value) {
    bindInstance(litepickerRef.value);
  }
});
</script>

<template>
  <FormInput
    ref="litepickerRef"
    type="text"
    :value="props.modelValue"
    @change="(event) => {
      emit('update:modelValue', (event.target as HTMLSelectElement).value);
    }"
    v-litepicker-directive
  />
</template>
