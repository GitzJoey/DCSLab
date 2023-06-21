<script lang="ts">
type LitepickerConfig = Partial<ILPConfiguration>;
</script>

<script setup lang="ts">
import { InputHTMLAttributes, onMounted, ref, inject } from "vue";
import { setValue, init, reInit } from "./litepicker";
import LitepickerJs from "litepicker";
import { FormInput } from "../../base-components/Form";
import { ILPConfiguration } from "litepicker/dist/types/interfaces.d";

export interface LitepickerElement extends HTMLInputElement {
  litePickerInstance: LitepickerJs;
}

export interface LitepickerEmit {
  (e: "update:modelValue", value: string): void;
}

export interface LitepickerProps extends /* @vue-ignore */ InputHTMLAttributes {
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
      tempValue.value = props.modelValue;
    }
  },
};

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
    @change="(event: Event) => {
      emit('update:modelValue', (event.target as HTMLInputElement).value);
    }"
    v-litepicker-directive
  />
</template>
