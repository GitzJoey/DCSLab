<script lang="ts">
  export default {
    inheritAttrs: false,
  };
</script>

<script setup lang="ts">
  import _ from 'lodash';
  import { twMerge } from 'tailwind-merge';
  import { computed, type InputHTMLAttributes, useAttrs, inject } from 'vue';
  import { type ProvideFormInline } from './FormInline.vue';
  import { type ProvideInputGroup } from './InputGroup/InputGroup.vue';

  export interface FormInputCodeProps extends /* @vue-ignore */ InputHTMLAttributes {
    value?: InputHTMLAttributes['value'];
    modelValue?: InputHTMLAttributes['value'];
    formInputSize?: 'sm' | 'lg';
    rounded?: boolean;
  }

  interface FormInputCodeEmit {
    (e: 'setAuto'): void;
    (e: 'update:modelValue', value: string): void;
  }

  const props = defineProps<FormInputCodeProps>();
  const attrs = useAttrs();
  const formInline = inject<ProvideFormInline>('formInline', false);
  const inputGroup = inject<ProvideInputGroup>('inputGroup', false);

  const emit = defineEmits<FormInputCodeEmit>();

  const disabledInput = computed(() => {
    if (props.modelValue == '_AUTO_') return true;
    else return false;
  });

  const computedClass = computed(() =>
    twMerge([
      'disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent',
      '[&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent',
      'transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80',
      props.formInputSize == 'sm' && 'text-xs py-1.5 px-2',
      props.formInputSize == 'lg' && 'text-lg py-1.5 px-4',
      props.rounded && 'rounded-full',
      formInline && 'flex-1',
      inputGroup &&
        'rounded-none [&:not(:first-child)]:border-l-transparent first:rounded-l last:rounded-r z-10',
      typeof attrs.class === 'string' && attrs.class,
    ]),
  );

  const localValue = computed({
    get() {
      return props.modelValue === undefined ? props.value : props.modelValue;
    },
    set(newValue) {
      emit('update:modelValue', newValue);
    },
  });

  const handleClickAutoButton = () => {
    emit('setAuto');
  };
</script>

<template>
  <div class="flex items-center gap-2">
    <div class="flex-1">
      <input
        :disabled="disabledInput"
        :class="computedClass"
        :type="props.type"
        v-bind="_.omit(attrs, 'class')"
        v-model="localValue"
      />
    </div>
    <button
      type="button"
      class="px-3 py-2.5 text-xs font-medium border border-slate-200 rounded-md bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap"
      @click="handleClickAutoButton"
    >
      Auto
    </button>
  </div>
</template>
