<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { convertErrorTypeToAlertListType, formatCurrency } from '@/utils/helper';
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
  FormSelectSearch,
  FormTextarea,
} from '@/components/Base/Form';
import PurchaseAdditionalCostService from '@/services/PurchaseAdditionalCostService';
import PurchaseService from '@/services/PurchaseService';
import PurchaseAdditionalCostCategoryService from '@/services/PurchaseAdditionalCostCategoryService';
import CashAccountService from '@/services/CashAccountService';
import CacheService from '@/services/CacheService';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { type DropDownOption } from '@/types/models/DropDownOption';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { type Purchase } from '@/types/models/Purchase';
import { type PurchaseAdditionalCostCategory } from '@/types/models/PurchaseAdditionalCostCategory';
import { type CashAccount } from '@/types/models/CashAccount';

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const purchaseAdditionalCostService = new PurchaseAdditionalCostService();
const purchaseService = new PurchaseService();
const purchaseAdditionalCostCategoryService = new PurchaseAdditionalCostCategoryService();
const cashAccountService = new CashAccountService();
const cacheServices = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.purchase_additional_cost.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.purchase_additional_cost.field_groups.purchase_additional_cost_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const purchaseAdditionalCostForm = purchaseAdditionalCostService.usePurchaseAdditionalCostCreateForm();

const purchaseDDL = ref<Array<DropDownOption> | null>(null);
const purchaseSearch = ref<string>('');
const purchaseOptions = computed(() =>
  (purchaseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const categoryDDL = ref<Array<DropDownOption> | null>(null);
const categorySearch = ref<string>('');
const categoryOptions = computed(() =>
  (categoryDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const paidImmediatelyCashAccountDDL = ref<Array<DropDownOption> | null>(null);
const paidImmediatelyCashAccountSearch = ref<string>('');
const paidImmediatelyCashAccountOptions = computed(() =>
  (paidImmediatelyCashAccountDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const amountTotalPreview = computed(() =>
  Number(purchaseAdditionalCostForm.amount_paid_immediately ?? 0) + Number(purchaseAdditionalCostForm.amount_payable ?? 0),
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
  setLocationData();

  await Promise.all([loadPurchaseDDL(), loadCategoryDDL(), loadPaidImmediatelyCashAccountDDL()]);
});

const setLocationData = () => {
  purchaseAdditionalCostForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });
};

const loadFromCache = () => {
  const data = cacheServices.getLastEntity('PURCHASE_ADDITIONAL_COST_CREATE') as Record<string, unknown>;

  if (!data) return;

  purchaseAdditionalCostForm.setData(data);
};

const loadPurchaseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await purchaseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: undefined,
    end_date: undefined,
    supplier_id: undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    purchaseDDL.value = result.data.data.map((item: Purchase) => ({
      code: item.id,
      name: item.supplier?.name ? `${item.code} - ${item.supplier.name}` : item.code,
    }));
  }
};

const loadCategoryDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await purchaseAdditionalCostCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: purchaseAdditionalCostForm.purchase_additional_cost_category_id || undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    categoryDDL.value = result.data.data.map((item: PurchaseAdditionalCostCategory) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadPaidImmediatelyCashAccountDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await cashAccountService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    is_bank: null,
    search,
    include_id: purchaseAdditionalCostForm.paid_immediately_cash_account_id || undefined,
    with_remaining_balance: null,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    paidImmediatelyCashAccountDDL.value = result.data.data.map((item: CashAccount) => ({
      code: item.id,
      name: `${item.code} - ${item.name}`,
    }));
  }
};

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const setCode = () => {
  purchaseAdditionalCostForm.forgetError('code');

  if (purchaseAdditionalCostForm.code === '_AUTO_') {
    purchaseAdditionalCostForm.setData({ code: '' });
    return;
  }

  purchaseAdditionalCostForm.setData({ code: '_AUTO_' });
};

const clearPurchase = () => {
  purchaseAdditionalCostForm.setData({ purchase_id: '' });
  purchaseAdditionalCostForm.forgetError('purchase_id');
};

const clearCategory = () => {
  purchaseAdditionalCostForm.setData({ purchase_additional_cost_category_id: '' });
  purchaseAdditionalCostForm.forgetError('purchase_additional_cost_category_id');
};

const clearPaidImmediatelyCashAccount = () => {
  purchaseAdditionalCostForm.setData({ paid_immediately_cash_account_id: '' });
  purchaseAdditionalCostForm.forgetError('paid_immediately_cash_account_id');
};

const onSubmit = async () => {
  if (purchaseAdditionalCostForm.hasErrors) {
    const firstErrorKey = Object.keys(purchaseAdditionalCostForm.errors)[0];

    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }

    return;
  }

  emits('loading-state', true);

  await purchaseAdditionalCostForm
    .submit()
    .then(() => {
      cacheServices.removeLastEntity('PURCHASE_ADDITIONAL_COST_CREATE');
      resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-purchase-additional-cost-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = () => {
  purchaseAdditionalCostForm.reset();
  purchaseAdditionalCostForm.setErrors({});
  setLocationData();
  purchaseSearch.value = '';
  categorySearch.value = '';
  paidImmediatelyCashAccountSearch.value = '';
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

watch(
  purchaseAdditionalCostForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity('PURCHASE_ADDITIONAL_COST_CREATE', newValue.data());
  }, 500),
  { deep: true },
);
</script>

<template>
  <form id="purchaseAdditionalCostForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.company.code }}
                <br />
                {{ selectedUserLocation.company.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="purchaseAdditionalCostForm.company_id" />
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="purchaseAdditionalCostForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostForm.invalid('purchase_id') }">
                {{ t('views.purchase_additional_cost.fields.purchase') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseAdditionalCostForm.purchase_id"
                v-model:search="purchaseSearch"
                :options="purchaseOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': purchaseAdditionalCostForm.invalid('purchase_id') }"
                @change="purchaseAdditionalCostForm.validate('purchase_id')"
                @search="loadPurchaseDDL"
                @clear="clearPurchase"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostForm.errors.purchase_id" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostForm.invalid('purchase_additional_cost_category_id') }">
                {{ t('views.purchase_additional_cost.fields.category') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseAdditionalCostForm.purchase_additional_cost_category_id"
                v-model:search="categorySearch"
                :options="categoryOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': purchaseAdditionalCostForm.invalid('purchase_additional_cost_category_id') }"
                @change="purchaseAdditionalCostForm.validate('purchase_additional_cost_category_id')"
                @search="loadCategoryDDL"
                @clear="clearCategory"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostForm.errors.purchase_additional_cost_category_id" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostForm.invalid('code') }">
                {{ t('views.purchase_additional_cost.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="purchaseAdditionalCostForm.code"
                :class="{ 'border-danger': purchaseAdditionalCostForm.invalid('code') }"
                :placeholder="t('views.purchase_additional_cost.fields.code')"
                @set-auto="setCode"
                @change="purchaseAdditionalCostForm.validate('code')"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostForm.errors.code" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostForm.invalid('date') }">
                {{ t('views.purchase_additional_cost.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="purchaseAdditionalCostForm.date"
                :class="{ 'border-danger': purchaseAdditionalCostForm.invalid('date') }"
                :placeholder="t('views.purchase_additional_cost.fields.date')"
                @change="purchaseAdditionalCostForm.validate('date')"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostForm.errors.date" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostForm.invalid('due_days') }">
                {{ t('views.purchase_additional_cost.fields.due_days') }}
              </FormLabel>
              <FormInput
                v-model="purchaseAdditionalCostForm.due_days"
                type="number"
                min="0"
                :class="{ 'border-danger': purchaseAdditionalCostForm.invalid('due_days') }"
                :placeholder="t('views.purchase_additional_cost.fields.due_days')"
                @change="purchaseAdditionalCostForm.validate('due_days')"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostForm.errors.due_days" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostForm.invalid('paid_immediately_cash_account_id') }">
                {{ t('views.purchase_additional_cost.fields.paid_immediately_cash_account') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseAdditionalCostForm.paid_immediately_cash_account_id"
                v-model:search="paidImmediatelyCashAccountSearch"
                :options="paidImmediatelyCashAccountOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': purchaseAdditionalCostForm.invalid('paid_immediately_cash_account_id') }"
                @change="purchaseAdditionalCostForm.validate('paid_immediately_cash_account_id')"
                @search="loadPaidImmediatelyCashAccountDDL"
                @clear="clearPaidImmediatelyCashAccount"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostForm.errors.paid_immediately_cash_account_id" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostForm.invalid('amount_paid_immediately') }">
                {{ t('views.purchase_additional_cost.fields.amount_paid_immediately') }}
              </FormLabel>
              <FormInputCurrency
                v-model="purchaseAdditionalCostForm.amount_paid_immediately"
                :allow-negative="false"
                :class="{ 'border-danger': purchaseAdditionalCostForm.invalid('amount_paid_immediately') }"
                :placeholder="t('views.purchase_additional_cost.fields.amount_paid_immediately')"
                @change="purchaseAdditionalCostForm.validate('amount_paid_immediately')"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostForm.errors.amount_paid_immediately" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostForm.invalid('amount_payable') }">
                {{ t('views.purchase_additional_cost.fields.amount_payable') }}
              </FormLabel>
              <FormInputCurrency
                v-model="purchaseAdditionalCostForm.amount_payable"
                :allow-negative="false"
                :class="{ 'border-danger': purchaseAdditionalCostForm.invalid('amount_payable') }"
                :placeholder="t('views.purchase_additional_cost.fields.amount_payable')"
                @change="purchaseAdditionalCostForm.validate('amount_payable')"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostForm.errors.amount_payable" />
            </div>

            <div class="col-span-12">
              <div class="rounded-md border border-slate-200/60 bg-slate-50 p-4 text-sm dark:border-darkmode-400 dark:bg-darkmode-600/40">
                <div class="flex items-center justify-between gap-4">
                  <span class="text-slate-500">{{ t('views.purchase_additional_cost.fields.amount_total') }}</span>
                  <span class="font-medium text-slate-700 dark:text-slate-200">
                    {{ formatCurrency(amountTotalPreview) }}
                  </span>
                </div>
                <FormErrorMessages :messages="(purchaseAdditionalCostForm.errors as any).amount_total" />
              </div>
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostForm.invalid('remarks') }">
                {{ t('views.purchase_additional_cost.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="purchaseAdditionalCostForm.remarks"
                rows="3"
                :class="{ 'border-danger': purchaseAdditionalCostForm.invalid('remarks') }"
                :placeholder="t('views.purchase_additional_cost.fields.remarks')"
                @change="purchaseAdditionalCostForm.validate('remarks')"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-button>
        <div class="flex gap-4">
          <Button
            type="submit"
            href="#"
            variant="primary"
            class="w-28 shadow-md"
            :disabled="purchaseAdditionalCostForm.validating || purchaseAdditionalCostForm.hasErrors"
          >
            <Lucide v-if="purchaseAdditionalCostForm.validating" icon="Loader" class="animate-spin" />
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
  </form>
</template>
