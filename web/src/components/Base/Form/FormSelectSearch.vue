<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { computed, ref, watch, type InputHTMLAttributes, useAttrs, inject } from "vue";
import { type ProvideFormInline } from "./FormInline.vue";
import { type ProvideInputGroup } from "./InputGroup/InputGroup.vue";

export interface FormSelectSearchOption {
  value: string | number;
  label: string;
}

export interface FormSelectSearchProps extends /* @vue-ignore */ InputHTMLAttributes {
  modelValue?: string | number | null;
  options?: FormSelectSearchOption[];
  formInputSize?: "sm" | "lg";
  rounded?: boolean;
}

export interface FormSelectSearchEmit {
  (e: "update:modelValue", value: string | number | null): void;
  (e: "change", value: string | number | null): void;
  (e: "update:search", value: string): void;
  (e: "search", value: string): void;
}

const props = defineProps<FormSelectSearchProps>();

const emit = defineEmits<FormSelectSearchEmit>();
const attrs = useAttrs();
const formInline = inject<ProvideFormInline>("formInline", false);
const inputGroup = inject<ProvideInputGroup>("inputGroup", false);

const inputRef = ref<HTMLInputElement | null>(null);
const isFocused = ref(false);
const isOpen = ref(false);
const displayValue = ref("");

const computedClass = computed(() =>
  twMerge([
    "disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent",
    "[&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent",
    "transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80",
    props.formInputSize == "sm" && "text-xs py-1.5 px-2",
    props.formInputSize == "lg" && "text-lg py-1.5 px-4",
    props.rounded && "rounded-full",
    formInline && "flex-1",
    inputGroup && "rounded-none [&:not(:first-child)]:border-l-transparent first:rounded-l last:rounded-r z-10",
    typeof attrs.class === "string" && attrs.class,
  ]),
);

const selectedOption = computed<FormSelectSearchOption | null>(() => {
  if (!props.options || props.modelValue === undefined || props.modelValue === null) {
    return null;
  }

  return props.options.find((option) => option.value === props.modelValue) ?? null;
});

const displayedOptions = computed<FormSelectSearchOption[]>(() => props.options ?? []);

watch(
  () => [props.modelValue, props.options],
  () => {
    if (!isFocused.value) {
      displayValue.value = selectedOption.value ? selectedOption.value.label : "";
    }
  },
  { immediate: true },
);

const emitSearchDebounced = _.debounce((value: string) => {
  emit("update:search", value);
  emit("search", value);
}, 300);

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const value = target.value;

  displayValue.value = value;
  isOpen.value = true;
  emitSearchDebounced(value);
};

const handleFocus = () => {
  isFocused.value = true;
  isOpen.value = true;
};

const handleBlur = () => {
  isFocused.value = false;
  isOpen.value = false;
  displayValue.value = selectedOption.value ? selectedOption.value.label : displayValue.value;
};

const handleSelect = (option: FormSelectSearchOption) => {
  emit("update:modelValue", option.value);
  emit("change", option.value);
  displayValue.value = option.label;
  isOpen.value = false;
  isFocused.value = false;
};
</script>

<template>
  <div class="relative">
    <input
      ref="inputRef"
      :class="computedClass"
      type="text"
      v-bind="_.omit(attrs, 'class')"
      v-model="displayValue"
      @focus="handleFocus"
      @blur="handleBlur"
      @input="handleInput"
    />
    <ul
      v-if="isOpen && displayedOptions.length > 0"
      class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md border border-slate-200 bg-white text-sm shadow-lg dark:border-slate-600 dark:bg-darkmode-800"
    >
      <li
        v-for="option in displayedOptions"
        :key="option.value"
        class="cursor-pointer px-3 py-2 hover:bg-slate-100 dark:hover:bg-darkmode-700"
        @mousedown.prevent="handleSelect(option)"
      >
        {{ option.label }}
      </li>
    </ul>
  </div>
</template>
