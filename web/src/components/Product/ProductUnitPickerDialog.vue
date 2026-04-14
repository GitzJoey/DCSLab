<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Dialog } from '@/components/Base/Headless';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { FormInput, FormLabel } from '@/components/Base/Form';
import { formatCurrency } from '@/utils/helper';
import ProductImagePreview from './ProductImagePreview.vue';

type ProductUnitPickerColumn = {
  key: string;
  label: string;
  align?: 'left' | 'right';
  formatter?: 'plain' | 'number' | 'currency';
};

type DialogSize = 'sm' | 'md' | 'lg' | 'xl';

type ProductUnitPickerOption = {
  product_name: string;
  product_image_url?: string | null;
  product_unit_code?: string | null;
  product_code?: string | null;
  [key: string]: unknown;
};

type Props = {
  open: boolean;
  title: string;
  searchText: string;
  isSearching: boolean;
  options: ProductUnitPickerOption[];
  columns: ProductUnitPickerColumn[];
  searchDisabled?: boolean;
  productCodeKey?: string;
  panelClass?: string;
  size?: DialogSize;
  searchInputId?: string;
};

const props = withDefaults(defineProps<Props>(), {
  searchDisabled: false,
  productCodeKey: 'product_unit_code',
  panelClass: '',
  size: 'xl',
  searchInputId: 'product-unit-search-input',
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'search'): void;
  (e: 'after-leave'): void;
  (e: 'update:searchText', value: string): void;
  (e: 'select', value: ProductUnitPickerOption): void;
}>();

const { t } = useI18n();

const emptyColspan = computed(() => props.columns.length + 3);

const getProductCode = (option: ProductUnitPickerOption) => {
  const value = option[props.productCodeKey];
  return typeof value === 'string' ? value : '';
};

const getProductLabel = (option: ProductUnitPickerOption) => {
  const code = getProductCode(option);

  if (code) {
    return `[${code}] ${option.product_name}`;
  }

  return option.product_name;
};

const getCellClass = (align?: 'left' | 'right') =>
  align === 'right' ? 'px-3 py-2 text-right' : 'px-3 py-2 text-left';

const formatValue = (value: unknown, formatter?: 'plain' | 'number' | 'currency') => {
  if (value === undefined || value === null || value === '') {
    return '-';
  }

  if (formatter === 'currency' || formatter === 'number') {
    return formatCurrency(Number(value));
  }

  return String(value);
};
</script>

<template>
  <Dialog :size="size" :open="open" @close="emit('close')" @after-leave="emit('after-leave')" @closed="emit('after-leave')">
    <Dialog.Panel :class="panelClass">
      <div class="p-5">
        <div class="flex items-center justify-between mb-4">
          <FormLabel>
            {{ title }}
          </FormLabel>
          <button type="button" class="text-slate-500 hover:text-danger" @click="emit('close')">
            <Lucide icon="X" class="w-4 h-4" />
          </button>
        </div>
        <div class="flex items-center gap-2 mb-4">
          <FormInput
            :id="searchInputId"
            :model-value="searchText"
            type="text"
            :placeholder="t('components.search-box.placeholder.search')"
            @update:model-value="emit('update:searchText', String($event))"
            @keyup.enter="emit('search')"
          />
          <Button type="button" variant="primary" class="shadow-md" tabindex="-1" :disabled="searchDisabled" @click="emit('search')">
            <template v-if="isSearching">
              <Lucide icon="Loader" class="w-4 h-4 animate-spin" />
            </template>
            <template v-else>
              {{ t('components.buttons.search') }}
            </template>
          </Button>
        </div>
        <div class="max-h-80 overflow-auto border border-slate-200/60 dark:border-darkmode-400 rounded-md">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-100 dark:bg-darkmode-600">
              <tr>
                <th class="px-3 py-2 text-left">
                  {{ t('views.product.table.cols.image') }}
                </th>
                <th class="px-3 py-2 text-left">
                  {{ t('views.product.fields.name') }}
                </th>
                <th v-for="column in columns" :key="column.key" :class="getCellClass(column.align)">
                  {{ column.label }}
                </th>
                <th class="px-3 py-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="options.length === 0">
                <td :colspan="emptyColspan" class="px-3 py-4 text-center text-slate-500">
                  {{ t('components.data-list.data_not_found') }}
                </td>
              </tr>
              <tr
                v-for="(option, index) in options"
                :key="`${option.product_unit_id ?? option.product_code ?? option.product_name}-${index}`"
                class="border-t border-slate-200/60 dark:border-darkmode-400"
              >
                <td class="px-3 py-2">
                  <ProductImagePreview
                    :image-url="option.product_image_url"
                    wrapper-class="w-10 h-10 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in"
                    icon-class="w-4 h-4 text-slate-400"
                    :preview-title="option.product_name"
                  />
                </td>
                <td class="px-3 py-2">
                  {{ getProductLabel(option) }}
                </td>
                <td v-for="column in columns" :key="column.key" :class="getCellClass(column.align)">
                  {{ formatValue(option[column.key], column.formatter) }}
                </td>
                <td class="px-3 py-2 text-right">
                  <Button type="button" variant="primary" size="sm" @click="emit('select', option)">
                    {{ t('components.buttons.select') }}
                  </Button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
