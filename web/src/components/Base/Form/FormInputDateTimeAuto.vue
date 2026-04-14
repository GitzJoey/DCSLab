<script setup lang="ts">
import { computed, ref, watch, type InputHTMLAttributes, useAttrs, inject, onBeforeUnmount } from 'vue';
import { twMerge } from 'tailwind-merge';
import _ from 'lodash';
import { formatDate } from '@/utils/helper';
import { type ProvideFormInline } from './FormInline.vue';
import { type ProvideInputGroup } from './InputGroup/InputGroup.vue';

interface FormInputDateTimeAutoProps extends /* @vue-ignore */ InputHTMLAttributes {
  modelValue?: string | null;
  formInputSize?: 'sm' | 'lg';
  rounded?: boolean;
}

interface FormInputDateTimeAutoEmit {
  (e: 'update:modelValue', value: string | null): void;
  (e: 'change', value: string | null): void;
}

const props = defineProps<FormInputDateTimeAutoProps>();
const emit = defineEmits<FormInputDateTimeAutoEmit>();
const attrs = useAttrs();
const formInline = inject<ProvideFormInline>('formInline', false);
const inputGroup = inject<ProvideInputGroup>('inputGroup', false);

const AUTO_VALUE = '_AUTO_';

const inputRef = ref<HTMLInputElement | null>(null);
const isFocused = ref(false);
const displayValue = ref('');

const isAutoMode = computed(() => props.modelValue === AUTO_VALUE);

const computedClass = computed(() =>
  twMerge([
    'disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent',
    '[&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent',
    'transition duration-200 ease-in-out w-full min-w-0 text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80',
    props.formInputSize == 'sm' && 'text-xs py-1.5 px-2',
    props.formInputSize == 'lg' && 'text-lg py-1.5 px-4',
    props.rounded && 'rounded-full',
    formInline && 'flex-1',
    inputGroup && 'rounded-none [&:not(:first-child)]:border-l-transparent first:rounded-l last:rounded-r z-10',
    typeof attrs.class === 'string' && attrs.class,
  ]),
);

const isDisabled = computed(() => {
  const attrDisabled = (attrs as any).disabled as boolean | undefined;
  return !!attrDisabled || isAutoMode.value;
});

watch(
  () => props.modelValue,
  (newVal) => {
    if (isFocused.value) return;
    if (!newVal || newVal === AUTO_VALUE) {
      displayValue.value = '';
      return;
    }
    displayValue.value = formatDate(newVal, 'YYYY-MM-DDTHH:mm:ss');
  },
  { immediate: true },
);

let autoTimer: number | null = null;

const stopAutoTimer = () => {
  if (autoTimer !== null) {
    window.clearInterval(autoTimer);
    autoTimer = null;
  }
};

const startAutoTimer = () => {
  stopAutoTimer();
  autoTimer = window.setInterval(() => {
    if (!isAutoMode.value) return;
    const now = new Date().toString();
    displayValue.value = formatDate(now, 'YYYY-MM-DDTHH:mm:ss');
  }, 1000);
};

watch(
  isAutoMode,
  (auto) => {
    if (auto) {
      startAutoTimer();
    } else {
      stopAutoTimer();
      const current = props.modelValue;
      if (!current || current === AUTO_VALUE) {
        displayValue.value = '';
      } else {
        displayValue.value = formatDate(current, 'YYYY-MM-DDTHH:mm:ss');
      }
    }
  },
  { immediate: true },
);

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  displayValue.value = target.value;
};

const handleChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const value = target.value;
  if (!value) {
    emit('update:modelValue', null);
    emit('change', null);
    return;
  }
  const [datePart, timePartRaw] = value.split('T');
  let timePart = timePartRaw ?? '';
  if (timePart && timePart.split(':').length === 2) {
    timePart = `${timePart}:00`;
  }
  const normalized = `${datePart} ${timePart}`;
  emit('update:modelValue', normalized);
  emit('change', normalized);
};

const toggleAuto = () => {
  if (isAutoMode.value) {
    emit('update:modelValue', null);
    emit('change', null);
  } else {
    emit('update:modelValue', AUTO_VALUE);
    emit('change', AUTO_VALUE);
  }
};

onBeforeUnmount(() => {
  stopAutoTimer();
});

const handleFocus = () => {
  isFocused.value = true;
};

const handleBlur = () => {
  isFocused.value = false;
};
</script>

<template>
  <div class="flex min-w-0 items-center gap-2">
    <div class="min-w-0 flex-1">
      <input
        ref="inputRef"
        :class="computedClass"
        type="datetime-local"
        step="1"
        v-bind="_.omit(attrs, 'class')"
        v-model="displayValue"
        :disabled="isDisabled"
        @input="handleInput"
        @change="handleChange"
        @focus="handleFocus"
        @blur="handleBlur"
      />
    </div>
    <button
      type="button"
      class="px-3 py-2.5 text-xs font-medium border border-slate-200 rounded-md bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap"
      @click="toggleAuto"
    >
      Auto
    </button>
  </div>
</template>
