<template>
  <input v-picker-directive="{ props, emit }" type="text" :value="modelValue" />
</template>

<script setup>
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
});

const emit = defineEmits();
</script>

<style scoped>
textarea {
  margin-left: 1000000px;
}
</style>
