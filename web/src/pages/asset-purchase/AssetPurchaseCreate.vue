<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import type { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
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
  FormSelectSearch,
  FormSwitch,
  FormTextarea,
} from '@/components/Base/Form';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { Dialog } from '@/components/Base/Headless';
import AssetService from '@/services/AssetService';
import AssetPurchaseService from '@/services/AssetPurchaseService';
import CacheService from '@/services/CacheService';
import SupplierService from '@/services/SupplierService';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { AssetPurchaseItemNestedStoreRequest } from '@/types/services/asset-purchase/AssetPurchaseRequest';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';

type AssetPurchaseItemFormItem = {
  qty: AssetPurchaseItemNestedStoreRequest['qty'];
  asset_id: AssetPurchaseItemNestedStoreRequest['asset_id'];
  asset_code?: string | null;
  asset_name?: string | null;
  asset_category_name?: string | null;
  asset_unit_name?: string | null;
  unit_price: AssetPurchaseItemNestedStoreRequest['unit_price'];
  remarks: AssetPurchaseItemNestedStoreRequest['remarks'];
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

const assetPurchaseService = new AssetPurchaseService();
const assetPurchaseForm = assetPurchaseService.useAssetPurchaseCreateForm();
const assetService = new AssetService();
const supplierService = new SupplierService();
const cacheService = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.asset_purchase.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.asset_purchase.field_groups.asset_purchase_data', state: CardState.Expanded },
  { title: 'views.asset_purchase.field_groups.items', state: CardState.Expanded },
  { title: 'views.asset_purchase.field_groups.summary', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const supplierDDL = ref<Array<DropDownOption> | null>(null);
const supplierSearch = ref('');
const supplierOptions = computed(() =>
  (supplierDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const showAssetModal = ref<boolean>(false);
const assetSearchText = ref<string>('');
const isSearchingAsset = ref<boolean>(false);
const assetOptions = ref<Array<AssetOption>>([]);
const editingAssetIndex = ref<number | null>(null);
const itemQtyToFocus = ref<number | null>(null);
const itemsRemarksExpanded = ref<boolean[]>([]);

const itemsForm = computed<AssetPurchaseItemFormItem[]>(
  () => assetPurchaseForm.items as AssetPurchaseItemFormItem[],
);

const toNumber = (value: unknown): number => {
  const parsed = Number(value ?? 0);
  return Number.isFinite(parsed) ? parsed : 0;
};

const roundAmount = (value: number): number => {
  return Math.round((value + Number.EPSILON) * 100000000) / 100000000;
};

const itemSubtotal = (item: AssetPurchaseItemFormItem): number => {
  return roundAmount(toNumber(item.qty) * toNumber(item.unit_price));
};

const itemTotal = computed(() => {
  return roundAmount(itemsForm.value.reduce((sum, item) => sum + itemSubtotal(item), 0));
});

const itemAllocatedAdditionalCost = (index: number): number => {
  const additionalCost = toNumber(assetPurchaseForm.additional_cost);
  const total = itemTotal.value;

  if (total <= 0 || additionalCost === 0) {
    return 0;
  }

  if (index === itemsForm.value.length - 1) {
    const allocatedBefore = itemsForm.value
      .slice(0, index)
      .reduce((sum, item, currentIndex) => sum + itemAllocatedAdditionalCost(currentIndex), 0);
    return roundAmount(additionalCost - allocatedBefore);
  }

  return roundAmount((additionalCost * itemSubtotal(itemsForm.value[index])) / total);
};

const itemSubtotalAfterAdditionalCost = (index: number): number => {
  return roundAmount(itemSubtotal(itemsForm.value[index]) + itemAllocatedAdditionalCost(index));
};

const itemUnitAcquisitionCost = (index: number): number => {
  const qty = toNumber(itemsForm.value[index]?.qty);
  if (qty <= 0) return 0;
  return roundAmount(itemSubtotalAfterAdditionalCost(index) / qty);
};

const amountPayable = computed(() => {
  return roundAmount(itemTotal.value + toNumber(assetPurchaseForm.additional_cost) + toNumber(assetPurchaseForm.rounding));
});

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
  assetPurchaseForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('ASSET_PURCHASE_CREATE', newValue.data());
  }, 500),
  { deep: true },
);

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_CREATE);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  loadFromCache();

  assetPurchaseForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });

  await loadSupplierDDL();
});

const setCode = () => {
  assetPurchaseForm.forgetError('code');
  assetPurchaseForm.setData({
    code: assetPurchaseForm.code === '_AUTO_' ? '' : '_AUTO_',
  });
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('ASSET_PURCHASE_CREATE') as Record<string, unknown>;
  if (!data) return;
  assetPurchaseForm.setData(data);
};

const loadSupplierDDL = async (search = supplierSearch.value) => {
  if (!selectedUserLocation.value) return;

  const result = await supplierService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: 1,
    include_id: assetPurchaseForm.supplier_id ?? undefined,
    refresh: true,
    limit: 20,
  });

  if (result.success && result.data) {
    supplierDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: `[${item.code}] ${item.name}`,
    })) as DropDownOption[];
  } else {
    supplierDDL.value = [];
  }
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

const selectAsset = (option: AssetOption) => {
  const baseData: Partial<AssetPurchaseItemFormItem> = {
    asset_id: option.asset_id,
    asset_code: option.asset_code,
    asset_name: option.asset_name,
    asset_category_name: option.asset_category_name,
    asset_unit_name: option.asset_unit_name,
    serials: [],
  };

  let targetIndex: number;

  if (editingAssetIndex.value === null) {
    const item: AssetPurchaseItemFormItem = {
      qty: 0,
      unit_price: 0,
      remarks: '',
      ...baseData,
    } as AssetPurchaseItemFormItem;

    assetPurchaseForm.items.push(item);
    itemsRemarksExpanded.value.push(false);
    targetIndex = assetPurchaseForm.items.length - 1;
  } else {
    const index = editingAssetIndex.value;
    const current = assetPurchaseForm.items[index] as AssetPurchaseItemFormItem;
    assetPurchaseForm.items[index] = { ...current, ...baseData };
    targetIndex = index;
  }

  showAssetModal.value = false;
  editingAssetIndex.value = null;
  itemQtyToFocus.value = targetIndex;
};

const handleAssetModalAfterLeave = () => {
  const index = itemQtyToFocus.value;
  itemQtyToFocus.value = null;
  if (index === null) return;

  nextTick(() => {
    const el = document.getElementById(`asset-purchase-qty-${index}`) as HTMLInputElement | null;
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const addAsset = () => {
  assetSearchText.value = '';
  assetOptions.value = [];
  isSearchingAsset.value = false;
  editingAssetIndex.value = null;
  showAssetModal.value = true;
};

const changeAsset = (index: number) => {
  assetSearchText.value = '';
  assetOptions.value = [];
  isSearchingAsset.value = false;
  editingAssetIndex.value = index;
  showAssetModal.value = true;
};

const addAssetSerial = (index: number) => {
  const item = itemsForm.value[index];
  if (!item) return;
  item.serials.push({ serial: '' });
  assetPurchaseForm.validate(`items.${index}.serials` as any);
};

const removeAssetSerial = (index: number, serialIndex: number) => {
  const item = itemsForm.value[index];
  if (!item) return;
  item.serials.splice(serialIndex, 1);
  assetPurchaseForm.validate(`items.${index}.serials` as any);
};

const toggleAssetRemarks = (index: number) => {
  itemsRemarksExpanded.value[index] = !(itemsRemarksExpanded.value[index] ?? false);
};

const removeAsset = (index: number) => {
  assetPurchaseForm.items.splice(index, 1);
  itemsRemarksExpanded.value.splice(index, 1);

  Object.keys(assetPurchaseForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      assetPurchaseForm.forgetError(key as any);
    }
  });
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

const onSubmit = async () => {
  const originalItems = assetPurchaseForm.items as AssetPurchaseItemFormItem[];
  const cleanedItems = originalItems.map((item) => ({
    qty: item.qty,
    asset_id: item.asset_id,
    unit_price: item.unit_price,
    remarks: item.remarks,
    serials: item.serials.map((serialItem) => ({ serial: serialItem.serial })),
  }));

  assetPurchaseForm.items = cleanedItems as any;

  emits('loading-state', true);

  try {
    await assetPurchaseForm.submit();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    router.push({ name: 'side-menu-asset-purchase-list' });
  } catch (error) {
    assetPurchaseForm.items = originalItems as any;
    showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
  } finally {
    emits('loading-state', false);
  }
};
</script>

<template>
  <form id="assetPurchaseForm" @submit.prevent="onSubmit">
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
              <FormInput type="hidden" v-model="assetPurchaseForm.company_id" />
            </div>
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="assetPurchaseForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel :class="{ 'text-danger': assetPurchaseForm.invalid('code') }">
                {{ t('views.asset_purchase.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="assetPurchaseForm.code"
                :class="{ 'border-danger': assetPurchaseForm.invalid('code') }"
                :placeholder="t('views.asset_purchase.fields.code')"
                @set-auto="setCode"
                @change="assetPurchaseForm.validate('code')"
              />
              <FormErrorMessages :messages="assetPurchaseForm.errors.code" />
            </div>
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel :class="{ 'text-danger': assetPurchaseForm.invalid('date') }">
                {{ t('views.asset_purchase.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="assetPurchaseForm.date"
                :class="{ 'border-danger': assetPurchaseForm.invalid('date') }"
                :placeholder="t('views.asset_purchase.fields.date')"
                @change="assetPurchaseForm.validate('date')"
              />
              <FormErrorMessages :messages="assetPurchaseForm.errors.date" />
            </div>
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel :class="{ 'text-danger': assetPurchaseForm.invalid('due_days') }">
                {{ t('views.asset_purchase.fields.due_days') }}
              </FormLabel>
              <FormInput
                v-model="assetPurchaseForm.due_days"
                type="number"
                min="0"
                :class="{ 'border-danger': assetPurchaseForm.invalid('due_days') }"
                @change="assetPurchaseForm.validate('due_days')"
              />
              <FormErrorMessages :messages="assetPurchaseForm.errors.due_days" />
            </div>
            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': assetPurchaseForm.invalid('supplier_id') }">
                {{ t('views.asset_purchase.fields.supplier_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="assetPurchaseForm.supplier_id"
                v-model:search="supplierSearch"
                :options="supplierOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': assetPurchaseForm.invalid('supplier_id') }"
                @search="loadSupplierDDL"
                @change="assetPurchaseForm.validate('supplier_id')"
              />
              <FormErrorMessages :messages="assetPurchaseForm.errors.supplier_id" />
            </div>
            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': assetPurchaseForm.invalid('remarks') }">
                {{ t('views.asset_purchase.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="assetPurchaseForm.remarks"
                rows="2"
                :class="{ 'border-danger': assetPurchaseForm.invalid('remarks') }"
                :placeholder="t('views.asset_purchase.fields.remarks')"
                @change="assetPurchaseForm.validate('remarks')"
              />
              <FormErrorMessages :messages="assetPurchaseForm.errors.remarks" />
            </div>
            <div class="col-span-12">
              <FormLabel class="pr-5">
                {{ t('views.asset_purchase.fields.is_posted') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input v-model="assetPurchaseForm.is_posted" type="checkbox" />
              </FormSwitch>
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5">
          <div v-if="assetPurchaseForm.items.length === 0" class="text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else class="space-y-5">
            <div
              v-for="(item, index) in itemsForm"
              :key="index"
              class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <div class="font-medium text-sm">
                  {{ t('views.asset_purchase_item.page_title') }} #{{ index + 1 }}
                </div>
                <div class="flex items-center gap-2">
                  <Button type="button" class="text-xs text-slate-500 hover:text-primary" @click="toggleAssetRemarks(index)">
                    {{ itemsRemarksExpanded[index] ? '▲' : '▼' }}
                  </Button>
                  <Button type="button" variant="outline-secondary" @click="removeAsset(index)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-7">
                  <FormLabel :class="{ 'text-danger': assetPurchaseForm.invalid(`items.${index}.asset_id` as any) }">
                    {{ t('views.asset_purchase.table.cols.asset') }}
                  </FormLabel>
                  <div class="flex items-center gap-2">
                    <div class="flex-1">
                      <div class="form-control border rounded-md px-3 py-2 bg-slate-50 dark:bg-darkmode-800">
                        {{ item.asset_name || '-' }}
                      </div>
                    </div>
                    <Button type="button" variant="outline-secondary" tabindex="-1" @click="changeAsset(index)">
                      <Lucide icon="Search" class="w-4 h-4" />
                    </Button>
                  </div>
                  <FormErrorMessages :messages="(assetPurchaseForm.errors as any)[`items.${index}.asset_id`]" />
                  <div class="text-sm text-slate-500 font-bold mt-1">
                    [{{ item.asset_code }}] {{ item.asset_category_name }}{{ item.asset_unit_name ? ` | ${item.asset_unit_name}` : '' }}
                  </div>
                </div>
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': assetPurchaseForm.invalid(`items.${index}.qty` as any) }">
                    {{ t('views.asset_purchase_item.fields.qty') }}
                  </FormLabel>
                  <FormInputCurrency
                    :id="`asset-purchase-qty-${index}`"
                    v-model="item.qty"
                    :class="['text-right', { 'border-danger': assetPurchaseForm.invalid(`items.${index}.qty` as any) }]"
                    @change="
                      assetPurchaseForm.validate(`items.${index}.qty` as any);
                      assetPurchaseForm.validate(`items.${index}.serials` as any);
                    "
                  />
                  <FormErrorMessages :messages="(assetPurchaseForm.errors as any)[`items.${index}.qty`]" />
                </div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': assetPurchaseForm.invalid(`items.${index}.unit_price` as any) }">
                    {{ t('views.asset_purchase_item.fields.unit_price') }}
                  </FormLabel>
                  <FormInputCurrency
                    v-model="item.unit_price"
                    :class="['text-right', { 'border-danger': assetPurchaseForm.invalid(`items.${index}.unit_price` as any) }]"
                    @change="assetPurchaseForm.validate(`items.${index}.unit_price` as any)"
                  />
                  <FormErrorMessages :messages="(assetPurchaseForm.errors as any)[`items.${index}.unit_price`]" />
                </div>

                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.asset_purchase_item.fields.subtotal') }}</FormLabel>
                  <FormInputCurrency :model-value="itemSubtotal(item)" disabled />
                </div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.asset_purchase.summary.allocated_additional_cost') }}</FormLabel>
                  <FormInputCurrency :model-value="itemAllocatedAdditionalCost(index)" disabled />
                </div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.asset_purchase.summary.subtotal_after_additional_cost') }}</FormLabel>
                  <FormInputCurrency :model-value="itemSubtotalAfterAdditionalCost(index)" disabled />
                </div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.asset_purchase.summary.unit_acquisition_cost') }}</FormLabel>
                  <FormInputCurrency :model-value="itemUnitAcquisitionCost(index)" disabled />
                </div>

                <div class="col-span-12">
                  <div class="flex items-center justify-between mb-2">
                    <FormLabel :class="{ 'text-danger': assetPurchaseForm.invalid(`items.${index}.serials` as any) }">
                      {{ t('views.product.fields.serial_number') }}
                    </FormLabel>
                    <Button type="button" size="sm" variant="outline-primary" @click="addAssetSerial(index)">
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
                        :class="{ 'border-danger': assetPurchaseForm.invalid(`items.${index}.serials.${serialIndex}.serial` as any) }"
                        @change="
                          assetPurchaseForm.validate(`items.${index}.serials.${serialIndex}.serial` as any);
                          assetPurchaseForm.validate(`items.${index}.serials` as any);
                        "
                      />
                      <Button type="button" variant="outline-secondary" @click="removeAssetSerial(index, serialIndex)">
                        <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                      </Button>
                    </div>
                  </div>
                  <FormErrorMessages :messages="(assetPurchaseForm.errors as any)[`items.${index}.serials`]" />
                  <FormErrorMessages
                    v-for="(_, serialIndex) in item.serials"
                    :key="`asset-purchase-serial-error-${index}-${serialIndex}`"
                    :messages="(assetPurchaseForm.errors as any)[`items.${index}.serials.${serialIndex}.serial`]"
                  />
                </div>

                <div v-if="itemsRemarksExpanded[index]" class="col-span-12">
                  <FormLabel :class="{ 'text-danger': assetPurchaseForm.invalid(`items.${index}.remarks` as any) }">
                    {{ t('views.asset_purchase_item.fields.remarks') }}
                  </FormLabel>
                  <FormTextarea
                    rows="2"
                    v-model="assetPurchaseForm.items[index].remarks"
                    :class="{ 'border-danger': assetPurchaseForm.invalid(`items.${index}.remarks` as any) }"
                    @change="assetPurchaseForm.validate(`items.${index}.remarks` as any)"
                  />
                  <FormErrorMessages :messages="(assetPurchaseForm.errors as any)[`items.${index}.remarks`]" />
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between mt-4">
            <FormLabel></FormLabel>
            <Button type="button" variant="primary" class="shadow-md" @click="addAsset">
              {{ t('views.asset_purchase_item.actions.create') }}
            </Button>
          </div>
        </div>
      </template>

      <template #card-items-3>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': assetPurchaseForm.invalid('additional_cost') }">
                {{ t('views.asset_purchase.fields.additional_cost') }}
              </FormLabel>
              <FormInputCurrency
                v-model="assetPurchaseForm.additional_cost"
                :class="{ 'border-danger': assetPurchaseForm.invalid('additional_cost') }"
                @change="assetPurchaseForm.validate('additional_cost')"
              />
              <FormErrorMessages :messages="assetPurchaseForm.errors.additional_cost" />
            </div>
            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': assetPurchaseForm.invalid('rounding') }">
                {{ t('views.asset_purchase.fields.rounding') }}
              </FormLabel>
              <FormInputCurrency
                v-model="assetPurchaseForm.rounding"
                :class="{ 'border-danger': assetPurchaseForm.invalid('rounding') }"
                @change="assetPurchaseForm.validate('rounding')"
              />
              <FormErrorMessages :messages="assetPurchaseForm.errors.rounding" />
            </div>
            <div class="col-span-12 lg:col-span-4">
              <FormLabel>{{ t('views.asset_purchase.fields.item_total') }}</FormLabel>
              <FormInputCurrency :model-value="itemTotal" disabled />
            </div>
            <div class="col-span-12 lg:col-span-4">
              <FormLabel>{{ t('views.asset_purchase.fields.amount_payable') }}</FormLabel>
              <FormInputCurrency :model-value="amountPayable" disabled />
            </div>
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
            :disabled="assetPurchaseForm.validating || assetPurchaseForm.hasErrors"
          >
            <Lucide v-if="assetPurchaseForm.validating" icon="Loader" class="animate-spin" />
            <template v-else>
              {{ t('components.buttons.submit') }}
            </template>
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
              {{ t('views.asset_purchase.table.title') }}
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
