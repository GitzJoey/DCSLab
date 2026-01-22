<script setup lang="ts">
import { computed, ref, watch, type InputHTMLAttributes, useAttrs, inject } from "vue";
import { twMerge } from "tailwind-merge";
import _ from "lodash";
import { formatCurrency } from "@/utils/helper";
import { type ProvideFormInline } from "./FormInline.vue";
import { type ProvideInputGroup } from "./InputGroup/InputGroup.vue";

interface FormInputCurrencyProps extends /* @vue-ignore */ InputHTMLAttributes {
  modelValue?: number | string;
  formInputSize?: "sm" | "lg";
  rounded?: boolean;
}

interface FormInputCurrencyEmit {
  (e: "update:modelValue", value: number): void;
  (e: "change", value: number): void;
}

const props = defineProps<FormInputCurrencyProps>();
const emit = defineEmits<FormInputCurrencyEmit>();
const attrs = useAttrs();
const formInline = inject<ProvideFormInline>("formInline", false);
const inputGroup = inject<ProvideInputGroup>("inputGroup", false);

const inputRef = ref<HTMLInputElement | null>(null);
const isFocused = ref(false);

const computedClass = computed(() =>
  twMerge([
    "disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent",
    "[&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent",
    "transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80",
    props.formInputSize == "sm" && "text-xs py-1.5 px-2",
    props.formInputSize == "lg" && "text-lg py-1.5 px-4",
    props.rounded && "rounded-full",
    formInline && "flex-1",
    inputGroup &&
      "rounded-none [&:not(:first-child)]:border-l-transparent first:rounded-l last:rounded-r z-10",
    typeof attrs.class === "string" && attrs.class,
    "text-right" // Currency usually right aligned
  ])
);

const displayValue = ref("");

// Watch modelValue to update displayValue when not focused
watch(() => props.modelValue, (newVal) => {
  if (!isFocused.value) {
    displayValue.value = formatCurrency(newVal ?? "");
  }
}, { immediate: true });

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  let val = target.value;
  
  // Remove non-digits and non-comma
  // Assuming Indonesian locale: Dot for thousand, Comma for decimal
  
  // Simple parsing:
  // 1. Remove dots (thousands separator)
  // 2. Replace comma with dot (decimal separator for parsing)
  
  const rawValue = val.replace(/\./g, "").replace(",", ".");
  const numberValue = parseFloat(rawValue);
  
  if (!isNaN(numberValue)) {
    emit("update:modelValue", numberValue);
  } else {
    // Handle empty or invalid input
    if (val === "" || val === "-") {
         // Maybe emit 0 or keep it as is?
         // If we emit 0, model becomes 0.
         // If we emit null?
         // Let's emit 0 for now as per previous behavior
         emit("update:modelValue", 0);
    }
  }
};

const handleFocus = () => {
  isFocused.value = true;
  // Unformat: show raw number (with comma if needed)
  if (props.modelValue !== undefined && props.modelValue !== null) {
      // Convert number to string with comma for decimal
      displayValue.value = props.modelValue.toString().replace(".", ",");
  }
};

const handleBlur = () => {
  isFocused.value = false;
  displayValue.value = formatCurrency(props.modelValue ?? "");
  // Emit change event for validation
  const rawValue = displayValue.value.replace(/\./g, "").replace(",", ".");
  const numberValue = parseFloat(rawValue);
  if (!isNaN(numberValue)) {
      emit("change", numberValue);
  }
};

</script>

<template>
  <input
    ref="inputRef"
    :class="computedClass"
    type="text"
    v-bind="_.omit(attrs, 'class')"
    v-model="displayValue"
    @input="handleInput"
    @focus="handleFocus"
    @blur="handleBlur"
  />
</template>
