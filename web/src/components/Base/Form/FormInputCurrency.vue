<script setup lang="ts">
  import { computed, ref, watch, type InputHTMLAttributes, useAttrs, inject } from 'vue';
  import { twMerge } from 'tailwind-merge';
  import _ from 'lodash';
  import { formatCurrency } from '@/utils/helper';
  import { type ProvideFormInline } from './FormInline.vue';
  import { type ProvideInputGroup } from './InputGroup/InputGroup.vue';

  interface FormInputCurrencyProps extends /* @vue-ignore */ InputHTMLAttributes {
    modelValue?: number | string;
    formInputSize?: 'sm' | 'lg';
    rounded?: boolean;
    allowNegative?: boolean;
  }

  interface FormInputCurrencyEmit {
    (e: 'update:modelValue', value: number): void;
    (e: 'change', value: number): void;
  }

  const props = withDefaults(defineProps<FormInputCurrencyProps>(), {
    allowNegative: true,
  });
  const emit = defineEmits<FormInputCurrencyEmit>();
  const attrs = useAttrs();
  const formInline = inject<ProvideFormInline>('formInline', false);
  const inputGroup = inject<ProvideInputGroup>('inputGroup', false);

  const inputRef = ref<HTMLInputElement | null>(null);
  const isFocused = ref(false);

  const computedClass = computed(() =>
    twMerge([
      'disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent',
      '[&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent',
      'transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80',
      props.formInputSize == 'sm' && 'text-xs py-1.5 px-2',
      props.formInputSize == 'lg' && 'text-lg py-1.5 px-4',
      props.rounded && 'rounded-full',
      formInline && 'flex-1',
      inputGroup && 'rounded-none [&:not(:first-child)]:border-l-transparent first:rounded-l last:rounded-r z-10',
      typeof attrs.class === 'string' && attrs.class,
      'text-right', // Currency usually right aligned
    ]),
  );

  const displayValue = ref('');

  // Watch modelValue to update displayValue when not focused
  watch(
    () => props.modelValue,
    (newVal) => {
      if (!isFocused.value) {
        displayValue.value = formatCurrency(newVal ?? '');
      }
    },
    { immediate: true },
  );

  const parseCurrencyInput = (value: string) => {
    const trimmedValue = value.trim();
    if (trimmedValue === '' || trimmedValue === '-') return 0;

    const isNegative = trimmedValue.startsWith('-') && props.allowNegative;
    const unsignedValue = trimmedValue.replace(/-/g, '').replace(/\s/g, '');
    const separators = unsignedValue.match(/[.,]/g) ?? [];

    let normalizedValue = unsignedValue;

    if (separators.length > 0) {
      const lastDotIndex = unsignedValue.lastIndexOf('.');
      const lastCommaIndex = unsignedValue.lastIndexOf(',');
      const decimalIndex = Math.max(lastDotIndex, lastCommaIndex);
      const decimalSeparator = decimalIndex >= 0 ? unsignedValue[decimalIndex] : '';
      const separatorCount = separators.length;

      if (separatorCount === 1 && decimalIndex >= 0) {
        const decimalPartLength = unsignedValue.length - decimalIndex - 1;
        normalizedValue = decimalPartLength === 3
          ? unsignedValue.replace(/[.,]/g, '')
          : unsignedValue.replace(decimalSeparator, '#DECIMAL#').replace(/[.,]/g, '').replace('#DECIMAL#', '.');
      } else if (separatorCount > 1) {
        const parts = unsignedValue.split(decimalSeparator);
        const groupsAfterFirst = parts.slice(1);
        const isThousandsPattern = groupsAfterFirst.every((part) => part.length === 3);

        normalizedValue = isThousandsPattern
          ? unsignedValue.replace(/[.,]/g, '')
          : unsignedValue.replace(decimalSeparator, '#DECIMAL#').replace(/[.,]/g, '').replace('#DECIMAL#', '.');
      }
    }

    const parsedValue = Number(normalizedValue);
    if (Number.isNaN(parsedValue)) return 0;

    return isNegative ? parsedValue * -1 : parsedValue;
  };

  const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    let val = target.value;

    if (!props.allowNegative) {
      val = val.replace(/-/g, '');
      displayValue.value = val;
    }

    emit('update:modelValue', parseCurrencyInput(val));
  };

  const handleFocus = () => {
    isFocused.value = true;
    if (props.modelValue !== undefined && props.modelValue !== null) {
      displayValue.value = props.modelValue.toString().replace('.', ',');
    }
  };

  const handleBlur = () => {
    isFocused.value = false;
    const normalizedValue = props.allowNegative
      ? Number(props.modelValue ?? 0)
      : Math.max(Number(props.modelValue ?? 0), 0);

    if (normalizedValue !== Number(props.modelValue ?? 0)) {
      emit('update:modelValue', normalizedValue);
    }

    displayValue.value = formatCurrency(normalizedValue);
    emit('change', normalizedValue);
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
