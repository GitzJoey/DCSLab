<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import { CardState } from '@/types/enums/CardState';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import {
  FormErrorMessages,
  FormInput,
  FormInputCode,
  FormInputCurrency,
  FormInputDateTimeAuto,
  FormLabel,
  FormSwitch,
  FormTextarea,
} from '@/components/Base/Form';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { Dialog } from '@/components/Base/Headless';
import AssetService from '@/services/AssetService';
import AssetAdjustmentService from '@/services/AssetAdjustmentService';
import CacheService from '@/services/CacheService';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type {
  AssetAdjustmentInItemNestedStoreRequest,
  AssetAdjustmentOutItemNestedStoreRequest,
} from '@/types/services/asset-adjustment/AssetAdjustmentRequest';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';

type AssetAdjustmentInItemFormItem = {
  qty: AssetAdjustmentInItemNestedStoreRequest['qty'];
  asset_id: AssetAdjustmentInItemNestedStoreRequest['asset_id'];
  asset_code?: string | null;
  asset_name?: string | null;
  asset_category_name?: string | null;
  asset_unit_name?: string | null;
  remarks: AssetAdjustmentInItemNestedStoreRequest['remarks'];
  serials: { serial: string }[];
};

type AssetAdjustmentOutItemFormItem = {
  qty: AssetAdjustmentOutItemNestedStoreRequest['qty'];
  asset_id: AssetAdjustmentOutItemNestedStoreRequest['asset_id'];
  asset_code?: string | null;
  asset_name?: string | null;
  asset_category_name?: string | null;
  asset_unit_name?: string | null;
  remarks: AssetAdjustmentOutItemNestedStoreRequest['remarks'];
  serials: { serial: string }[];
};

type AssetOption = {
  asset_id: string;
  asset_code: string;
  asset_name: string;
  asset_category_name: string;
  asset_unit_name: string;
};

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const assetAdjustmentService = new AssetAdjustmentService();
const assetAdjustmentForm = assetAdjustmentService.useAssetAdjustmentCreateForm();
const assetService = new AssetService();
const cacheService = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.asset_adjustment.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.asset_adjustment.field_groups.asset_adjustment_data', state: CardState.Expanded },
  { title: 'views.asset_adjustment.field_groups.in_items', state: CardState.Collapsed },
  { title: 'views.asset_adjustment.field_groups.out_items', state: CardState.Collapsed },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const showAssetModal = ref<boolean>(false);
const assetSearchText = ref<string>('');
const isSearchingAsset = ref<boolean>(false);
const assetOptions = ref<Array<AssetOption>>([]);
const assetSelectionTarget = ref<'in' | 'out'>('in');
const editingInAssetIndex = ref<number | null>(null);
const editingOutAssetIndex = ref<number | null>(null);
const inItemQtyToFocus = ref<number | null>(null);
const outItemQtyToFocus = ref<number | null>(null);

const inItemsRemarksExpanded = ref<boolean[]>([]);
const outItemsRemarksExpanded = ref<boolean[]>([]);

const inItemsForm = computed<AssetAdjustmentInItemFormItem[]>(
  () => assetAdjustmentForm.in_items as AssetAdjustmentInItemFormItem[],
);
const outItemsForm = computed<AssetAdjustmentOutItemFormItem[]>(
  () => assetAdjustmentForm.out_items as AssetAdjustmentOutItemFormItem[],
);

const handleExpandCard = (index: number) => {
  cards.value[index].state =
    cards.value[index].state === CardState.Collapsed ? CardState.Expanded : CardState.Collapsed;
};

watch(showAssetModal, (open) => {
  if (!open) return;
  nextTick(() => {
    const el = document.getElementById('asset-search-input') as HTMLInputElement | null;
    el?.focus();
  });
});

watch(
  assetAdjustmentForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('ASSET_ADJUSTMENT_CREATE', newValue.data());
  }, 500),
  { deep: true },
);

onMounted(() => {
  emits('mode-state', ViewMode.FORM_CREATE);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  loadFromCache();

  assetAdjustmentForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });
});

const setCode = () => {
  assetAdjustmentForm.forgetError('code');
  assetAdjustmentForm.setData({
    code: assetAdjustmentForm.code === '_AUTO_' ? '' : '_AUTO_',
  });
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('ASSET_ADJUSTMENT_CREATE') as Record<string, unknown>;
  if (!data) return;
  assetAdjustmentForm.setData(data);
};

const searchAssets = async () => {
  if (!selectedUserLocation.value) return;

  isSearchingAsset.value = true;

  const result = await assetService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search: assetSearchText.value,
    asset_category_id: undefined,
    asset_unit_id: undefined,
    status: 1,
    include_id: undefined,
    refresh: true,
    limit: 20,
  });

  isSearchingAsset.value = false;

  if (result.success && result.data) {
    assetOptions.value = result.data.data.map((item) => ({
      asset_id: item.id,
      asset_code: item.code,
      asset_name: item.name,
      asset_category_name: item.asset_category?.name ?? '',
      asset_unit_name: item.asset_unit?.name ?? '',
    }));
  } else {
    assetOptions.value = [];
  }
};

const selectAssetForIn = (option: AssetOption) => {
  const baseData: Partial<AssetAdjustmentInItemFormItem> = {
    asset_id: option.asset_id,
    asset_code: option.asset_code,
    asset_name: option.asset_name,
    asset_category_name: option.asset_category_name,
    asset_unit_name: option.asset_unit_name,
    serials: [],
  };

  let targetIndex: number;

  if (editingInAssetIndex.value === null) {
    const item: AssetAdjustmentInItemFormItem = {
      qty: 0,
      remarks: '',
      ...baseData,
    } as AssetAdjustmentInItemFormItem;

    assetAdjustmentForm.in_items.push(item);
    inItemsRemarksExpanded.value.push(false);
    targetIndex = assetAdjustmentForm.in_items.length - 1;
  } else {
    const index = editingInAssetIndex.value;
    const current = assetAdjustmentForm.in_items[index] as AssetAdjustmentInItemFormItem;
    assetAdjustmentForm.in_items[index] = { ...current, ...baseData };
    targetIndex = index;
  }

  showAssetModal.value = false;
  editingInAssetIndex.value = null;
  inItemQtyToFocus.value = targetIndex;
};

const selectAssetForOut = (option: AssetOption) => {
  const baseData: Partial<AssetAdjustmentOutItemFormItem> = {
    asset_id: option.asset_id,
    asset_code: option.asset_code,
    asset_name: option.asset_name,
    asset_category_name: option.asset_category_name,
    asset_unit_name: option.asset_unit_name,
    serials: [],
  };

  let targetIndex: number;

  if (editingOutAssetIndex.value === null) {
    const item: AssetAdjustmentOutItemFormItem = {
      qty: 0,
      remarks: '',
      ...baseData,
    } as AssetAdjustmentOutItemFormItem;

    assetAdjustmentForm.out_items.push(item);
    outItemsRemarksExpanded.value.push(false);
    targetIndex = assetAdjustmentForm.out_items.length - 1;
  } else {
    const index = editingOutAssetIndex.value;
    const current = assetAdjustmentForm.out_items[index] as AssetAdjustmentOutItemFormItem;
    assetAdjustmentForm.out_items[index] = { ...current, ...baseData };
    targetIndex = index;
  }

  showAssetModal.value = false;
  editingOutAssetIndex.value = null;
  outItemQtyToFocus.value = targetIndex;
};

const selectAsset = (option: AssetOption) => {
  if (assetSelectionTarget.value === 'out') {
    return selectAssetForOut(option);
  }

  return selectAssetForIn(option);
};

const handleAssetModalAfterLeave = () => {
  const inIndex = inItemQtyToFocus.value;
  const outIndex = outItemQtyToFocus.value;

  inItemQtyToFocus.value = null;
  outItemQtyToFocus.value = null;

  if (inIndex === null && outIndex === null) return;

  nextTick(() => {
    let el: HTMLInputElement | null = null;

    if (inIndex !== null) {
      el = document.getElementById(`in-asset-qty-${inIndex}`) as HTMLInputElement | null;
    } else if (outIndex !== null) {
      el = document.getElementById(`out-asset-qty-${outIndex}`) as HTMLInputElement | null;
    }

    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const addInAsset = () => {
  assetSearchText.value = '';
  assetOptions.value = [];
  isSearchingAsset.value = false;
  assetSelectionTarget.value = 'in';
  editingInAssetIndex.value = null;
  showAssetModal.value = true;
};

const changeInAsset = (index: number) => {
  assetSearchText.value = '';
  assetOptions.value = [];
  isSearchingAsset.value = false;
  assetSelectionTarget.value = 'in';
  editingInAssetIndex.value = index;
  showAssetModal.value = true;
};

const addOutAsset = () => {
  assetSearchText.value = '';
  assetOptions.value = [];
  isSearchingAsset.value = false;
  assetSelectionTarget.value = 'out';
  editingOutAssetIndex.value = null;
  showAssetModal.value = true;
};

const changeOutAsset = (index: number) => {
  assetSearchText.value = '';
  assetOptions.value = [];
  isSearchingAsset.value = false;
  assetSelectionTarget.value = 'out';
  editingOutAssetIndex.value = index;
  showAssetModal.value = true;
};

const addInAssetSerial = (index: number) => {
  const item = inItemsForm.value[index];
  if (!item) return;
  item.serials.push({ serial: '' });
  assetAdjustmentForm.validate(`in_items.${index}.serials` as any);
};

const removeInAssetSerial = (index: number, serialIndex: number) => {
  const item = inItemsForm.value[index];
  if (!item) return;
  item.serials.splice(serialIndex, 1);
  assetAdjustmentForm.validate(`in_items.${index}.serials` as any);
};

const addOutAssetSerial = (index: number) => {
  const item = outItemsForm.value[index];
  if (!item) return;
  item.serials.push({ serial: '' });
  assetAdjustmentForm.validate(`out_items.${index}.serials` as any);
};

const removeOutAssetSerial = (index: number, serialIndex: number) => {
  const item = outItemsForm.value[index];
  if (!item) return;
  item.serials.splice(serialIndex, 1);
  assetAdjustmentForm.validate(`out_items.${index}.serials` as any);
};

const toggleInAssetRemarks = (index: number) => {
  inItemsRemarksExpanded.value[index] = !(inItemsRemarksExpanded.value[index] ?? false);
};

const toggleOutAssetRemarks = (index: number) => {
  outItemsRemarksExpanded.value[index] = !(outItemsRemarksExpanded.value[index] ?? false);
};

const removeInAsset = (index: number) => {
  assetAdjustmentForm.in_items.splice(index, 1);
  inItemsRemarksExpanded.value.splice(index, 1);
};

const removeOutAsset = (index: number) => {
  assetAdjustmentForm.out_items.splice(index, 1);
  outItemsRemarksExpanded.value.splice(index, 1);
};

const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const showAlertPlaceholder = (
  pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark',
  pTitle: string,
  pAlertList: Record<string, Array<string>> | null,
) => {
  const ap: AlertPlaceholderProps = {
    alertType: pAlertType,
    title: pTitle,
    alertList: pAlertList,
  };
  emits('show-alertplaceholder', ap);
};

const resetForm = () => {
  assetAdjustmentForm.reset();
  assetAdjustmentForm.setErrors({});
  inItemsRemarksExpanded.value = [];
  outItemsRemarksExpanded.value = [];
  assetAdjustmentForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    code: '_AUTO_',
    date: '_AUTO_',
    remarks: '',
    is_posted: true,
    in_items: [],
    out_items: [],
  });
};

const onSubmit = async () => {
  if (assetAdjustmentForm.hasErrors) {
    const firstErrorKey = Object.keys(assetAdjustmentForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  const originalInItems = assetAdjustmentForm.in_items as AssetAdjustmentInItemFormItem[];
  const cleanedInItems: AssetAdjustmentInItemNestedStoreRequest[] = originalInItems.map((item) => ({
    qty: item.qty,
    asset_id: item.asset_id,
    remarks: item.remarks,
    serials: item.serials.map((serialItem) => ({ serial: serialItem.serial })),
  }));

  const originalOutItems = assetAdjustmentForm.out_items as AssetAdjustmentOutItemFormItem[];
  const cleanedOutItems: AssetAdjustmentOutItemNestedStoreRequest[] = originalOutItems.map((item) => ({
    qty: item.qty,
    asset_id: item.asset_id,
    remarks: item.remarks,
    serials: item.serials.map((serialItem) => ({ serial: serialItem.serial })),
  }));

  const backupInItems = [...originalInItems];
  const backupOutItems = [...originalOutItems];

  assetAdjustmentForm.in_items = cleanedInItems as any;
  assetAdjustmentForm.out_items = cleanedOutItems as any;

  emits('loading-state', true);

  try {
    await assetAdjustmentForm.submit();
    resetForm();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    router.push({ name: 'side-menu-asset-adjustment-list' });
  } catch (error) {
    assetAdjustmentForm.in_items = backupInItems as any;
    assetAdjustmentForm.out_items = backupOutItems as any;
    showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
  } finally {
    emits('loading-state', false);
  }
};
</script>

<template>
  <form id="assetAdjustmentForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.company.code }}
                <br />
                {{ selectedUserLocation.company.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="assetAdjustmentForm.company_id" />
            </div>
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="assetAdjustmentForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel :class="{ 'text-danger': assetAdjustmentForm.invalid('code') }">
                {{ t('views.asset_adjustment.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="assetAdjustmentForm.code"
                :class="{ 'border-danger': assetAdjustmentForm.invalid('code') }"
                :placeholder="t('views.asset_adjustment.fields.code')"
                @set-auto="setCode"
                @change="assetAdjustmentForm.validate('code')"
              />
              <FormErrorMessages :messages="assetAdjustmentForm.errors.code" />
            </div>
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel :class="{ 'text-danger': assetAdjustmentForm.invalid('date') }">
                {{ t('views.asset_adjustment.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="assetAdjustmentForm.date"
                :class="{ 'border-danger': assetAdjustmentForm.invalid('date') }"
                :placeholder="t('views.asset_adjustment.fields.date')"
                @change="assetAdjustmentForm.validate('date')"
              />
              <FormErrorMessages :messages="assetAdjustmentForm.errors.date" />
            </div>
            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': assetAdjustmentForm.invalid('remarks') }">
                {{ t('views.asset_adjustment.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="assetAdjustmentForm.remarks"
                rows="3"
                :class="{ 'border-danger': assetAdjustmentForm.invalid('remarks') }"
                :placeholder="t('views.asset_adjustment.fields.remarks')"
                @change="assetAdjustmentForm.validate('remarks')"
              />
              <FormErrorMessages :messages="assetAdjustmentForm.errors.remarks" />
            </div>
            <div class="col-span-12">
              <FormLabel class="pr-5">
                {{ t('views.asset_adjustment.fields.is_posted') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input v-model="assetAdjustmentForm.is_posted" type="checkbox" />
              </FormSwitch>
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5">
          <div v-if="assetAdjustmentForm.in_items.length === 0" class="text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else class="space-y-5">
            <div
              v-for="(item, index) in inItemsForm"
              :key="index"
              class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <div class="font-medium text-sm">
                  {{ t('views.asset_adjustment_in_item.page_title') }} #{{ index + 1 }}
                </div>
                <div class="flex items-center gap-2">
                  <Button type="button" class="text-xs text-slate-500 hover:text-primary" @click="toggleInAssetRemarks(index)">
                    {{ inItemsRemarksExpanded[index] ? '▲' : '▼' }}
                  </Button>
                  <Button type="button" variant="outline-secondary" @click="removeInAsset(index)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': assetAdjustmentForm.invalid(`in_items.${index}.qty` as any) }">
                    {{ t('views.asset_adjustment_in_item.fields.qty') }}
                  </FormLabel>
                  <FormInputCurrency
                    :id="`in-asset-qty-${index}`"
                    v-model="item.qty"
                    :class="['text-right', { 'border-danger': assetAdjustmentForm.invalid(`in_items.${index}.qty` as any) }]"
                    @change="
                      assetAdjustmentForm.validate(`in_items.${index}.qty` as any);
                      assetAdjustmentForm.validate(`in_items.${index}.serials` as any);
                    "
                  />
                  <FormErrorMessages :messages="(assetAdjustmentForm.errors as any)[`in_items.${index}.qty`]" />
                </div>
                <div class="col-span-12 lg:col-span-10">
                  <FormLabel :class="{ 'text-danger': assetAdjustmentForm.invalid(`in_items.${index}.asset_id` as any) }">
                    {{ t('views.asset_adjustment.table.cols.asset') }}
                  </FormLabel>
                  <div class="flex items-center gap-2">
                    <div class="flex-1">
                      <div class="form-control border rounded-md px-3 py-2 bg-slate-50 dark:bg-darkmode-800">
                        {{ item.asset_name || '-' }}
                      </div>
                    </div>
                    <Button type="button" variant="outline-secondary" tabindex="-1" @click="changeInAsset(index)">
                      <Lucide icon="Search" class="w-4 h-4" />
                    </Button>
                  </div>
                  <FormErrorMessages :messages="(assetAdjustmentForm.errors as any)[`in_items.${index}.asset_id`]" />
                  <div class="text-sm text-slate-500 font-bold mt-1">
                    [{{ item.asset_code }}] {{ item.asset_category_name }}{{ item.asset_unit_name ? ` | ${item.asset_unit_name}` : '' }}
                  </div>
                </div>
                <div class="col-span-12">
                  <div class="flex items-center justify-between mb-2">
                    <FormLabel :class="{ 'text-danger': assetAdjustmentForm.invalid(`in_items.${index}.serials` as any) }">
                      {{ t('views.product.fields.serial_number') }}
                    </FormLabel>
                    <Button type="button" size="sm" variant="outline-primary" @click="addInAssetSerial(index)">
                      <Lucide icon="Plus" class="w-3 h-3 mr-1" />
                      {{ t('components.buttons.create') }}
                    </Button>
                  </div>
                  <div v-if="item.serials.length === 0" class="text-slate-500 text-xs italic">
                    {{ t('components.data-list.data_not_found') }}
                  </div>
                  <div v-else class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <div v-for="(serial, serialIndex) in item.serials" :key="serialIndex" class="flex gap-2">
                      <FormInput
                        v-model="serial.serial"
                        :placeholder="t('views.product.fields.serial_number')"
                        :class="{ 'border-danger': assetAdjustmentForm.invalid(`in_items.${index}.serials.${serialIndex}.serial` as any) }"
                        @change="
                          assetAdjustmentForm.validate(`in_items.${index}.serials.${serialIndex}.serial` as any);
                          assetAdjustmentForm.validate(`in_items.${index}.serials` as any);
                        "
                      />
                      <Button type="button" variant="outline-secondary" @click="removeInAssetSerial(index, serialIndex)">
                        <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                      </Button>
                    </div>
                  </div>
                  <FormErrorMessages :messages="(assetAdjustmentForm.errors as any)[`in_items.${index}.serials`]" />
                </div>
                <div v-if="inItemsRemarksExpanded[index]" class="col-span-12">
                  <FormLabel :class="{ 'text-danger': assetAdjustmentForm.invalid(`in_items.${index}.remarks` as any) }">
                    {{ t('views.asset_adjustment_in_item.fields.remarks') }}
                  </FormLabel>
                  <FormTextarea
                    rows="2"
                    v-model="assetAdjustmentForm.in_items[index].remarks"
                    :class="{ 'border-danger': assetAdjustmentForm.invalid(`in_items.${index}.remarks` as any) }"
                    @change="assetAdjustmentForm.validate(`in_items.${index}.remarks` as any)"
                  />
                  <FormErrorMessages :messages="(assetAdjustmentForm.errors as any)[`in_items.${index}.remarks`]" />
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between mt-4">
            <FormLabel></FormLabel>
            <Button type="button" variant="primary" class="shadow-md" @click="addInAsset">
              {{ t('views.asset_adjustment_in_item.actions.create') }}
            </Button>
          </div>
        </div>
      </template>

      <template #card-items-3>
        <div class="p-5">
          <div v-if="assetAdjustmentForm.out_items.length === 0" class="text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else class="space-y-5">
            <div
              v-for="(item, index) in outItemsForm"
              :key="index"
              class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <div class="font-medium text-sm">
                  {{ t('views.asset_adjustment_out_item.page_title') }} #{{ index + 1 }}
                </div>
                <div class="flex items-center gap-2">
                  <Button type="button" class="text-xs text-slate-500 hover:text-primary" @click="toggleOutAssetRemarks(index)">
                    {{ outItemsRemarksExpanded[index] ? '▲' : '▼' }}
                  </Button>
                  <Button type="button" variant="outline-secondary" @click="removeOutAsset(index)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': assetAdjustmentForm.invalid(`out_items.${index}.qty` as any) }">
                    {{ t('views.asset_adjustment_out_item.fields.qty') }}
                  </FormLabel>
                  <FormInputCurrency
                    :id="`out-asset-qty-${index}`"
                    v-model="item.qty"
                    :class="['text-right', { 'border-danger': assetAdjustmentForm.invalid(`out_items.${index}.qty` as any) }]"
                    @change="
                      assetAdjustmentForm.validate(`out_items.${index}.qty` as any);
                      assetAdjustmentForm.validate(`out_items.${index}.serials` as any);
                    "
                  />
                  <FormErrorMessages :messages="(assetAdjustmentForm.errors as any)[`out_items.${index}.qty`]" />
                </div>
                <div class="col-span-12 lg:col-span-10">
                  <FormLabel :class="{ 'text-danger': assetAdjustmentForm.invalid(`out_items.${index}.asset_id` as any) }">
                    {{ t('views.asset_adjustment.table.cols.asset') }}
                  </FormLabel>
                  <div class="flex items-center gap-2">
                    <div class="flex-1">
                      <div class="form-control border rounded-md px-3 py-2 bg-slate-50 dark:bg-darkmode-800">
                        {{ item.asset_name || '-' }}
                      </div>
                    </div>
                    <Button type="button" variant="outline-secondary" tabindex="-1" @click="changeOutAsset(index)">
                      <Lucide icon="Search" class="w-4 h-4" />
                    </Button>
                  </div>
                  <FormErrorMessages :messages="(assetAdjustmentForm.errors as any)[`out_items.${index}.asset_id`]" />
                  <div class="text-sm text-slate-500 font-bold mt-1">
                    [{{ item.asset_code }}] {{ item.asset_category_name }}{{ item.asset_unit_name ? ` | ${item.asset_unit_name}` : '' }}
                  </div>
                </div>
                <div class="col-span-12">
                  <div class="flex items-center justify-between mb-2">
                    <FormLabel :class="{ 'text-danger': assetAdjustmentForm.invalid(`out_items.${index}.serials` as any) }">
                      {{ t('views.product.fields.serial_number') }}
                    </FormLabel>
                    <Button type="button" size="sm" variant="outline-primary" @click="addOutAssetSerial(index)">
                      <Lucide icon="Plus" class="w-3 h-3 mr-1" />
                      {{ t('components.buttons.create') }}
                    </Button>
                  </div>
                  <div v-if="item.serials.length === 0" class="text-slate-500 text-xs italic">
                    {{ t('components.data-list.data_not_found') }}
                  </div>
                  <div v-else class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <div v-for="(serial, serialIndex) in item.serials" :key="serialIndex" class="flex gap-2">
                      <FormInput
                        v-model="serial.serial"
                        :placeholder="t('views.product.fields.serial_number')"
                        :class="{ 'border-danger': assetAdjustmentForm.invalid(`out_items.${index}.serials.${serialIndex}.serial` as any) }"
                        @change="
                          assetAdjustmentForm.validate(`out_items.${index}.serials.${serialIndex}.serial` as any);
                          assetAdjustmentForm.validate(`out_items.${index}.serials` as any);
                        "
                      />
                      <Button type="button" variant="outline-secondary" @click="removeOutAssetSerial(index, serialIndex)">
                        <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                      </Button>
                    </div>
                  </div>
                  <FormErrorMessages :messages="(assetAdjustmentForm.errors as any)[`out_items.${index}.serials`]" />
                </div>
                <div v-if="outItemsRemarksExpanded[index]" class="col-span-12">
                  <FormLabel :class="{ 'text-danger': assetAdjustmentForm.invalid(`out_items.${index}.remarks` as any) }">
                    {{ t('views.asset_adjustment_out_item.fields.remarks') }}
                  </FormLabel>
                  <FormTextarea
                    rows="2"
                    v-model="assetAdjustmentForm.out_items[index].remarks"
                    :class="{ 'border-danger': assetAdjustmentForm.invalid(`out_items.${index}.remarks` as any) }"
                    @change="assetAdjustmentForm.validate(`out_items.${index}.remarks` as any)"
                  />
                  <FormErrorMessages :messages="(assetAdjustmentForm.errors as any)[`out_items.${index}.remarks`]" />
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between mt-4">
            <FormLabel></FormLabel>
            <Button type="button" variant="primary" class="shadow-md" @click="addOutAsset">
              {{ t('views.asset_adjustment_out_item.actions.create') }}
            </Button>
          </div>
        </div>
      </template>

      <template #card-items-button>
        <div class="flex gap-4 p-5">
          <Button
            type="submit"
            href="#"
            variant="primary"
            class="w-28 shadow-md"
            :disabled="assetAdjustmentForm.validating || assetAdjustmentForm.hasErrors"
          >
            <Lucide v-if="assetAdjustmentForm.validating" icon="Loader" class="animate-spin" />
            <template v-else>
              {{ t('components.buttons.submit') }}
            </template>
          </Button>
          <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md" @click="resetForm">
            {{ t('components.buttons.reset') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>

    <Dialog
      size="xl"
      :open="showAssetModal"
      @close="
        () => {
          showAssetModal = false;
        }
      "
      @after-leave="handleAssetModalAfterLeave"
    >
      <Dialog.Panel>
        <div class="p-5">
          <div class="flex items-center justify-between mb-4">
            <FormLabel>
              {{ t('views.asset_adjustment.table.title') }}
            </FormLabel>
            <button type="button" class="text-slate-500 hover:text-danger" @click="showAssetModal = false">
              <Lucide icon="X" class="w-4 h-4" />
            </button>
          </div>

          <div class="flex items-center gap-2 mb-4">
            <FormInput
              id="asset-search-input"
              v-model="assetSearchText"
              type="text"
              :placeholder="t('components.search-box.placeholder.search')"
              @keyup.enter="searchAssets"
            />
            <Button type="button" variant="primary" class="shadow-md" @click="searchAssets" :disabled="isSearchingAsset">
              <template v-if="isSearchingAsset">
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
                  <th class="px-3 py-2 text-left">{{ t('views.asset.table.cols.code') }}</th>
                  <th class="px-3 py-2 text-left">{{ t('views.asset.table.cols.name') }}</th>
                  <th class="px-3 py-2 text-left">{{ t('views.asset.table.cols.asset_category') }}</th>
                  <th class="px-3 py-2 text-left">{{ t('views.asset.table.cols.asset_unit') }}</th>
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="assetOptions.length === 0">
                  <td colspan="5" class="px-3 py-4 text-center text-slate-500">
                    {{ t('components.data-list.data_not_found') }}
                  </td>
                </tr>
                <tr
                  v-for="(option, optionIndex) in assetOptions"
                  :key="optionIndex"
                  class="border-t border-slate-200/60 dark:border-darkmode-400"
                >
                  <td class="px-3 py-2">{{ option.asset_code }}</td>
                  <td class="px-3 py-2">{{ option.asset_name }}</td>
                  <td class="px-3 py-2">{{ option.asset_category_name }}</td>
                  <td class="px-3 py-2">{{ option.asset_unit_name }}</td>
                  <td class="px-3 py-2 text-right">
                    <Button type="button" variant="primary" size="sm" @click="selectAsset(option)">
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
  </form>
</template>
