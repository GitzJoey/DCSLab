<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { computed, InputHTMLAttributes, useAttrs, watch, ref } from "vue";
import FormCheck from "../FormCheck";

interface InputProps extends InputHTMLAttributes {
  modelValue?: InputHTMLAttributes["value"];
  type: "checkbox";
}

interface InputEmit {
  (e: "update:modelValue", value: string): void;
}

const props = defineProps<InputProps>();

const attrs = useAttrs();

const computedClass = computed(() =>
  twMerge([
    // Default
    "w-[38px] h-[24px] p-px rounded-full relative",
    "before:w-[20px] before:h-[20px] before:shadow-[1px_1px_3px_rgba(0,0,0,0.25)] before:transition-[margin-left] before:duration-200 before:ease-in-out before:absolute before:inset-y-0 before:my-auto before:rounded-full before:dark:bg-darkmode-600",

    // On checked
    "checked:bg-primary checked:border-primary checked:bg-none",
    "before:checked:ml-[14px] before:checked:bg-white",

    typeof attrs.class === "string" && attrs.class,
  ])
);

const localValue = ref(props.modelValue);
const emit = defineEmits<InputEmit>();

watch(localValue, () => {
  emit("update:modelValue", localValue.value);
});
</script>

<template>
  <FormCheck.Input
    :type="props.type"
    :class="computedClass"
    v-bind="_.omit(attrs, 'class')"
    v-model="localValue"
  />
</template>
