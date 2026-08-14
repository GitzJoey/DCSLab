<script lang="ts">
  export default {
    inheritAttrs: false,
  };
</script>

<script setup lang="ts">
  import _ from 'lodash';
  import { twMerge } from 'tailwind-merge';
  import { computed, nextTick, onBeforeUnmount, ref, watch, type CSSProperties, type InputHTMLAttributes, useAttrs, inject } from 'vue';
  import { type ProvideFormInline } from './FormInline.vue';
  import { type ProvideInputGroup } from './InputGroup/InputGroup.vue';
  import Lucide from '@/components/Base/Lucide';

  export interface FormSelectSearchOption {
    value: string | number;
    label: string;
  }

  export interface FormSelectSearchProps extends /* @vue-ignore */ InputHTMLAttributes {
    modelValue?: string | number | null;
    options?: FormSelectSearchOption[];
    formInputSize?: 'sm' | 'lg';
    rounded?: boolean;
    // when true a selected value no longer locks the input: clicking reopens
    // the list and typing starts a fresh search (the label is restored on blur)
    reselectable?: boolean;
  }

  export interface FormSelectSearchEmit {
    (e: 'update:modelValue', value: string | number | null): void;
    (e: 'change', value: string | number | null): void;
    (e: 'update:search', value: string): void;
    (e: 'search', value: string): void;
    (e: 'clear'): void;
    (e: 'enter', value: string): void;
  }

  const props = defineProps<FormSelectSearchProps>();

  const emit = defineEmits<FormSelectSearchEmit>();
  const attrs = useAttrs();
  const formInline = inject<ProvideFormInline>('formInline', false);
  const inputGroup = inject<ProvideInputGroup>('inputGroup', false);

  const wrapperRef = ref<HTMLDivElement | null>(null);
  const inputRef = ref<HTMLInputElement | null>(null);
  const listId = `form-select-search-list-${Math.random().toString(36).slice(2)}`;
  const isFocused = ref(false);
  const isOpen = ref(false);
  const highlightedIndex = ref(-1);
  const displayValue = ref('');
  const dropdownStyle = ref<CSSProperties>({
    top: '0px',
    left: '0px',
    width: '0px',
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
      inputGroup && 'rounded-none [&:not(:first-child)]:border-l-transparent first:rounded-l last:rounded-r z-10',
      'pr-8',
      typeof attrs.class === 'string' && attrs.class,
    ]),
  );

  const selectedOption = computed<FormSelectSearchOption | null>(() => {
    if (!props.options || props.modelValue === undefined || props.modelValue === null) {
      return null;
    }

    return props.options.find((option) => option.value === props.modelValue) ?? null;
  });

  const displayedOptions = computed<FormSelectSearchOption[]>(() => props.options ?? []);

  const isLocked = computed(() => !props.reselectable && selectedOption.value !== null);

  const hasValue = computed(() => props.modelValue !== undefined && props.modelValue !== null && props.modelValue !== '');

  watch(
    () => [props.modelValue, props.options],
    () => {
      if (!isFocused.value) {
        displayValue.value = selectedOption.value ? selectedOption.value.label : '';
      }
    },
    { immediate: true },
  );

  const emitSearchDebounced = _.debounce((value: string) => {
    emit('update:search', value);
    emit('search', value);
  }, 300);

  const updateDropdownPosition = () => {
    const target = wrapperRef.value ?? inputRef.value;
    if (!target) return;

    const rect = target.getBoundingClientRect();
    dropdownStyle.value = {
      top: `${rect.bottom + 4}px`,
      left: `${rect.left}px`,
      width: `${rect.width}px`,
    };
  };

  const attachDropdownListeners = () => {
    window.addEventListener('resize', updateDropdownPosition);
    window.addEventListener('scroll', updateDropdownPosition, true);
  };

  const detachDropdownListeners = () => {
    window.removeEventListener('resize', updateDropdownPosition);
    window.removeEventListener('scroll', updateDropdownPosition, true);
  };

  const handleInput = (event: Event) => {
    if (isLocked.value) return;
    const target = event.target as HTMLInputElement;
    const value = target.value;

    displayValue.value = value;
    isOpen.value = true;
    highlightedIndex.value = -1;
    updateDropdownPosition();
    emitSearchDebounced(value);
  };

  const handleFocus = async () => {
    if (isLocked.value) return;
    isFocused.value = true;
    isOpen.value = true;
    if (props.reselectable && selectedOption.value) {
      displayValue.value = '';
    }
    await nextTick();
    updateDropdownPosition();
  };

  const handleBlur = () => {
    isFocused.value = false;
    isOpen.value = false;
    highlightedIndex.value = -1;
    displayValue.value = selectedOption.value ? selectedOption.value.label : displayValue.value;
  };

  const scrollHighlightedIntoView = () => {
    const list = document.getElementById(listId);
    const el = list?.children[highlightedIndex.value] as HTMLElement | undefined;
    el?.scrollIntoView({ block: 'nearest' });
  };

  const handleKeydown = (event: KeyboardEvent) => {
    if (isLocked.value) return;

    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault();
      if (!isOpen.value) {
        isOpen.value = true;
        updateDropdownPosition();
        return;
      }
      const max = displayedOptions.value.length - 1;
      if (max < 0) return;
      highlightedIndex.value =
        event.key === 'ArrowDown'
          ? Math.min(highlightedIndex.value + 1, max)
          : Math.max(highlightedIndex.value - 1, 0);
      scrollHighlightedIntoView();
      return;
    }

    if (event.key === 'Enter') {
      // never let Enter fall through to a form submit from inside the dropdown
      event.preventDefault();
      const highlighted = isOpen.value ? displayedOptions.value[highlightedIndex.value] : undefined;
      if (highlighted) {
        handleSelect(highlighted);
        return;
      }
      // barcode-scanner path: let the parent resolve an exact match for the raw text
      emit('enter', displayValue.value.trim());
      return;
    }

    if (event.key === 'Escape') {
      isOpen.value = false;
      highlightedIndex.value = -1;
    }
  };

  const handleSelect = (option: FormSelectSearchOption) => {
    emit('update:modelValue', option.value);
    emit('change', option.value);
    displayValue.value = option.label;
    isOpen.value = false;
    isFocused.value = false;
  };

  const handleClear = () => {
    emit('update:modelValue', null);
    emit('change', null);
    displayValue.value = '';
    isOpen.value = false;
    isFocused.value = false;
    emit('update:search', '');
    emit('search', '');
    emit('clear');
  };

  watch(isOpen, async (open) => {
    if (open) {
      await nextTick();
      updateDropdownPosition();
      attachDropdownListeners();
      return;
    }

    detachDropdownListeners();
  });

  onBeforeUnmount(() => {
    detachDropdownListeners();
    emitSearchDebounced.cancel();
  });

  defineExpose({
    focus: () => inputRef.value?.focus(),
    blur: () => inputRef.value?.blur(),
  });
</script>

<template>
  <div ref="wrapperRef" class="relative">
    <input
      ref="inputRef"
      :class="computedClass"
      type="text"
      v-bind="_.omit(attrs, 'class')"
      v-model="displayValue"
      :readonly="isLocked"
      @focus="handleFocus"
      @blur="handleBlur"
      @input="handleInput"
      @keydown="handleKeydown"
    />
    <button
      v-if="hasValue"
      type="button"
      class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-danger"
      @mousedown.prevent.stop="handleClear"
    >
      <Lucide icon="X" class="w-4 h-4" />
    </button>
    <Teleport to="body">
      <ul
        v-if="isOpen && displayedOptions.length > 0"
        :id="listId"
        :style="dropdownStyle"
        class="fixed z-[9999] max-h-60 overflow-auto rounded-md border border-slate-200 bg-white text-sm shadow-lg dark:border-slate-600 dark:bg-darkmode-800"
      >
        <li
          v-for="(option, optionIndex) in displayedOptions"
          :key="option.value"
          class="cursor-pointer px-3 py-2 hover:bg-slate-100 dark:hover:bg-darkmode-700"
          :class="{ 'bg-slate-100 dark:bg-darkmode-700': optionIndex === highlightedIndex }"
          @mousedown.prevent="handleSelect(option)"
          @mouseenter="highlightedIndex = optionIndex"
        >
          {{ option.label }}
        </li>
      </ul>
    </Teleport>
  </div>
</template>
