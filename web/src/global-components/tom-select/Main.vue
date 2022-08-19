<template>
  <select
    v-select-directive="{ props, emit, computedOptions }"
    class="tom-select"
  >
    <slot></slot>
  </select>
</template>

<script setup>
import { computed, watch, toRaw, ref, onMounted, inject } from "vue";
import { setValue, init, updateValue } from "./index";
import dom from "@left4code/tw-starter/dist/js/dom";

const vSelectDirective = {
  mounted(el, { value }) {
    // Clone the select element to prevent tom select remove the original element
    const clonedEl = dom(el).clone().insertAfter(el)[0];
    dom(el).attr("hidden", true);

    // Initialize tom select
    setValue(clonedEl, value.props);
    init(el, clonedEl, value.props, value.emit, value.computedOptions);

    // Attach instance
    tomSelectRef.value = clonedEl;
  },
  updated(el, { value }) {
    const clonedEl = dom(el).next()[0];
    const modelValue = toRaw(value.props.modelValue);
    updateValue(
      el,
      clonedEl,
      modelValue,
      value.props,
      value.emit,
      value.computedOptions
    );

    // Attach instance
    tomSelectRef.value = clonedEl;
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
    type: [String, Number, Array],
    default: "",
  },
  refKey: {
    type: String,
    default: null,
  },
});

const emit = defineEmits();

const tomSelectRef = ref();
const bindInstance = () => {
  if (props.refKey) {
    const bind = inject(`bind[${props.refKey}]`);
    if (bind) {
      bind(tomSelectRef.value);
    }
  }
};

// Compute all default options
const computedOptions = computed(() => {
  let options = {
    ...props.options,
    plugins: {
      dropdown_input: {},
      ...props.options.plugins,
    },
  };

  if (Array.isArray(props.modelValue)) {
    options = {
      persist: false,
      create: true,
      onDelete: function (values) {
        return confirm(
          values.length > 1
            ? "Are you sure you want to remove these " +
                values.length +
                " items?"
            : 'Are you sure you want to remove "' + values[0] + '"?'
        );
      },
      ...options,
      plugins: {
        remove_button: {
          title: "Remove this item",
        },
        ...options.plugins,
      },
    };
  }

  return options;
});

// Watch value change
watch(
  computed(() => props.modelValue),
  () => {
    emit("change");
  }
);

onMounted(() => {
  bindInstance();
});
</script>
