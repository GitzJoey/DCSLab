<template>
  <input
    ref="litepickerRef"
    v-picker-directive="{ props, emit }"
    type="text"
    :value="modelValue"
  />
</template>

<script setup>
import { inject, onMounted, ref } from "vue";
import { setValue, init, reInit } from "./index";

const vPickerDirective = {
  mounted(el, { value }) {
    setValue(value.props, value.emit);
    init(el, value.props, value.emit);
  },
  updated(el, { oldValue, value }) {
    if (oldValue.props.modelValue !== value.props.modelValue) {
      reInit(el, value.props, value.emit);
    }
  },
};

const props = defineProps({
  options: {
    type: Object,
    default() {
      return {};
    },
  },
  modelValue: {
    type: String,
    default: "",
  },
  refKey: {
    type: String,
    default: null,
  },
});

const emit = defineEmits();

const litepickerRef = ref();
const bindInstance = () => {
  if (props.refKey) {
    const bind = inject(`bind[${props.refKey}]`);
    if (bind) {
      bind(litepickerRef.value);
    }
  }
};

onMounted(() => {
  bindInstance();
});
</script>

<style scoped>
textarea {
  margin-left: 1000000px;
}
</style>
