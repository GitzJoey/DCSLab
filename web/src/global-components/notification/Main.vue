<template>
  <div
    ref="toastifyRef"
    v-toastify-directive="{ props, emit }"
    class="toastify-content hidden"
  >
    <slot></slot>
  </div>
</template>

<script setup>
import { inject, onMounted, ref } from "vue";
import { init, reInit } from "./index";

const vToastifyDirective = {
  mounted(el, { value }) {
    init(el, value.props);
  },
  updated(el) {
    reInit(el);
  },
};

const props = defineProps({
  options: {
    type: Object,
    default: () => ({}),
  },
  refKey: {
    type: String,
    default: null,
  },
});

const emit = defineEmits();

const toastifyRef = ref();
const bindInstance = () => {
  if (props.refKey) {
    const bind = inject(`bind[${props.refKey}]`);
    if (bind) {
      bind(toastifyRef.value);
    }
  }
};

onMounted(() => {
  bindInstance();
});
</script>
