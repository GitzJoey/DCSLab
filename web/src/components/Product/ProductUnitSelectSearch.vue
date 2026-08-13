<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import FormSelectSearch from '@/components/Base/Form/FormSelectSearch.vue';

export interface ProductUnitSelectOption {
  product_unit_id: string;
  product_unit_code: string;
  product_name: string;
  product_image_url?: string | null;
  unit_name: string;
  base_unit_name: string;
  conversion_value: number;
  is_use_serial_number: boolean;
  purchase_order_item_id?: string | null;
}

export interface ProductUnitSelectSearchProps {
  modelValue: string | null;
  fetchOptions: (search: string) => Promise<ProductUnitSelectOption[]>;
  disabled?: boolean;
  invalid?: boolean;
  placeholder?: string;
  initialOption?: ProductUnitSelectOption | null;
}

export interface ProductUnitSelectSearchEmit {
  (e: 'update:modelValue', value: string | null): void;
  (e: 'select', option: ProductUnitSelectOption | null): void;
}

const props = defineProps<ProductUnitSelectSearchProps>();
const emit = defineEmits<ProductUnitSelectSearchEmit>();

// each instance owns its options and search state so every row stays independent
const options = ref<Array<ProductUnitSelectOption>>(props.initialOption ? [props.initialOption] : []);
const hasFetched = ref<boolean>(false);
let requestCounter = 0;

const buildOptionLabel = (option: ProductUnitSelectOption) =>
  `${option.product_unit_code} · ${option.product_name} · ${option.unit_name}`;

// the selected entry drops the unit suffix: the row's Unit column already shows it
const selectOptions = computed(() =>
  options.value.map((option) => ({
    value: option.product_unit_id,
    label:
      option.product_unit_id === props.modelValue
        ? `${option.product_unit_code} · ${option.product_name}`
        : buildOptionLabel(option),
  })),
);

const selectClass = computed(() => (props.invalid ? 'border-danger' : ''));

const findOption = (productUnitId: string | number | null | undefined) =>
  options.value.find((option) => option.product_unit_id === productUnitId) ?? null;

watch(
  () => props.initialOption,
  (initialOption) => {
    if (!initialOption?.product_unit_id) return;
    if (findOption(initialOption.product_unit_id)) return;
    options.value = [initialOption, ...options.value];
  },
);

const loadOptions = async (search: string) => {
  hasFetched.value = true;
  const requestId = ++requestCounter;

  const result = await props.fetchOptions(search);

  // ignore out-of-order responses so a stale search never overwrites a newer one
  if (requestId !== requestCounter) return;

  const merged = [...result];

  // keep the currently selected option resolvable so its label stays visible
  if (props.modelValue) {
    const selected = findOption(props.modelValue);
    if (selected && !merged.some((option) => option.product_unit_id === selected.product_unit_id)) {
      merged.unshift(selected);
    }
  }

  options.value = merged;
};

const handleSearch = (search: string) => {
  void loadOptions(search);
};

const handleFocus = () => {
  // lazy first load: nothing is fetched on mount, only when the user opens the
  // field — including when a value is already selected, so a click behaves
  // like a regular dropdown. Cached per instance afterwards.
  if (hasFetched.value) return;
  void loadOptions('');
};

const handleUpdateModelValue = (value: string | number | null) => {
  emit('update:modelValue', value === null || value === '' ? null : String(value));
};

// the change listener also matches the native input onChange type, hence the Event guard
const handleChange = (value: string | number | null | Event) => {
  if (value instanceof Event) return;

  if (value === null || value === '') {
    emit('select', null);
    return;
  }

  emit('select', findOption(String(value)));
};
</script>

<template>
  <FormSelectSearch
    :model-value="modelValue"
    reselectable
    :options="selectOptions"
    :disabled="disabled"
    :placeholder="placeholder"
    :class="selectClass"
    @update:model-value="handleUpdateModelValue"
    @change="handleChange"
    @focus="handleFocus"
    @search="handleSearch"
  />
</template>
