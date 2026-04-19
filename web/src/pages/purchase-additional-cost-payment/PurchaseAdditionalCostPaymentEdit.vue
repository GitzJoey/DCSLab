<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
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
import PurchaseAdditionalCostPaymentService from '@/services/PurchaseAdditionalCostPaymentService';
import CashAccountService from '@/services/CashAccountService';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { type DropDownOption } from '@/types/models/DropDownOption';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { type PurchaseAdditionalCostPayment } from '@/types/models/PurchaseAdditionalCostPayment';
import { type CashAccount } from '@/types/models/CashAccount';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const purchaseAdditionalCostPaymentService = new PurchaseAdditionalCostPaymentService();
const cashAccountService = new CashAccountService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.purchase_additional_cost_payment.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.purchase_additional_cost_payment.field_groups.purchase_additional_cost_payment_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const purchaseAdditionalCostPaymentForm = purchaseAdditionalCostPaymentService.usePurchaseAdditionalCostPaymentEditForm(
  route.params.ulid.toString(),
);

const purchaseAdditionalCostPaymentData = ref<PurchaseAdditionalCostPayment | null>(null);

const cashAccountDDL = ref<Array<DropDownOption> | null>(null);
const cashAccountSearch = ref<string>('');
const cashAccountOptions = computed(() =>
  (cashAccountDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const purchaseAdditionalCostLabel = computed(() => {
  const purchaseAdditionalCost = purchaseAdditionalCostPaymentData.value?.purchase_additional_cost;

  if (!purchaseAdditionalCost) return '-';

  return [
    purchaseAdditionalCost.code,
    purchaseAdditionalCost.purchase?.code ? `Purchase ${purchaseAdditionalCost.purchase.code}` : null,
    `Sisa ${formatCurrency(Number(purchaseAdditionalCost.amount_payable_due ?? 0))}`,
  ]
    .filter(Boolean)
    .join(' - ');
});

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_EDIT);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await loadData();
  await loadCashAccountDDL();
});

const loadData = async () => {
  emits('loading-state', true);
  const result = await purchaseAdditionalCostPaymentService.read(route.params.ulid.toString());
  emits('loading-state', false);

  if (result.success && result.data) {
    purchaseAdditionalCostPaymentData.value = result.data;

    purchaseAdditionalCostPaymentForm.setData({
      company_id: result.data.company?.id ?? '',
      branch_id: result.data.branch?.id ?? '',
      purchase_additional_cost_id: result.data.purchase_additional_cost?.id ?? '',
      code: result.data.code,
      date: result.data.date,
      cash_account_id: result.data.cash_account?.id ?? '',
      amount: result.data.amount,
      remarks: result.data.remarks ?? '',
    } as any);
  } else {
    router.push({ name: 'side-menu-purchase-additional-cost-payment-list' });
  }
};

const loadCashAccountDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await cashAccountService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    is_bank: null,
    search,
    include_id: purchaseAdditionalCostPaymentData.value?.cash_account?.id,
    with_remaining_balance: null,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    cashAccountDDL.value = result.data.data.map((item: CashAccount) => ({
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
  purchaseAdditionalCostPaymentForm.forgetError('code');

  if (purchaseAdditionalCostPaymentForm.code === '_AUTO_') {
    purchaseAdditionalCostPaymentForm.setData({ code: '' });
    return;
  }

  purchaseAdditionalCostPaymentForm.setData({ code: '_AUTO_' });
};

const clearCashAccount = () => {
  purchaseAdditionalCostPaymentForm.setData({ cash_account_id: '' });
  purchaseAdditionalCostPaymentForm.forgetError('cash_account_id');
};

const onSubmit = async () => {
  if (purchaseAdditionalCostPaymentForm.hasErrors) {
    const firstErrorKey = Object.keys(purchaseAdditionalCostPaymentForm.errors)[0];

    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }

    return;
  }

  emits('loading-state', true);

  await purchaseAdditionalCostPaymentForm
    .submit()
    .then(() => {
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-purchase-additional-cost-payment-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = async () => {
  purchaseAdditionalCostPaymentForm.reset();
  purchaseAdditionalCostPaymentForm.setErrors({});
  cashAccountSearch.value = '';
  await loadData();
  await loadCashAccountDDL();
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
</script>

<template>
  <form id="purchaseAdditionalCostPaymentForm" @submit.prevent="onSubmit">
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
              <FormInput type="hidden" v-model="purchaseAdditionalCostPaymentForm.company_id" />
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="purchaseAdditionalCostPaymentForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12">
              <FormLabel>
                {{ t('views.purchase_additional_cost_payment.fields.purchase_additional_cost') }}
              </FormLabel>
              <FormInput type="hidden" v-model="purchaseAdditionalCostPaymentForm.purchase_additional_cost_id" />
              <div class="rounded-md border border-slate-200/60 bg-slate-50 px-3 py-2 text-sm text-slate-700 dark:border-darkmode-400 dark:bg-darkmode-600/40 dark:text-slate-200">
                {{ purchaseAdditionalCostLabel }}
              </div>
            </div>

            <div class="col-span-12">
              <div class="rounded-md border border-slate-200/60 bg-slate-50 p-4 text-sm dark:border-darkmode-400 dark:bg-darkmode-600/40">
                <div class="grid grid-cols-12 gap-3">
                  <div class="col-span-12 md:col-span-4">
                    <div class="text-slate-500">{{ t('views.purchase_additional_cost.fields.amount_payable') }}</div>
                    <div class="mt-1 font-medium text-slate-700 dark:text-slate-200">
                      {{ formatCurrency(Number(purchaseAdditionalCostPaymentData?.purchase_additional_cost?.amount_payable ?? 0)) }}
                    </div>
                  </div>
                  <div class="col-span-12 md:col-span-4">
                    <div class="text-slate-500">{{ t('views.purchase_additional_cost.fields.amount_payable_paid') }}</div>
                    <div class="mt-1 font-medium text-slate-700 dark:text-slate-200">
                      {{ formatCurrency(Number(purchaseAdditionalCostPaymentData?.purchase_additional_cost?.amount_payable_paid ?? 0)) }}
                    </div>
                  </div>
                  <div class="col-span-12 md:col-span-4">
                    <div class="text-slate-500">{{ t('views.purchase_additional_cost.fields.amount_payable_due') }}</div>
                    <div class="mt-1 font-medium text-slate-700 dark:text-slate-200">
                      {{ formatCurrency(Number(purchaseAdditionalCostPaymentData?.purchase_additional_cost?.amount_payable_due ?? 0)) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostPaymentForm.invalid('code') }">
                {{ t('views.purchase_additional_cost_payment.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="purchaseAdditionalCostPaymentForm.code"
                :class="{ 'border-danger': purchaseAdditionalCostPaymentForm.invalid('code') }"
                :placeholder="t('views.purchase_additional_cost_payment.fields.code')"
                @set-auto="setCode"
                @change="purchaseAdditionalCostPaymentForm.validate('code')"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostPaymentForm.errors.code" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostPaymentForm.invalid('date') }">
                {{ t('views.purchase_additional_cost_payment.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="purchaseAdditionalCostPaymentForm.date"
                :class="{ 'border-danger': purchaseAdditionalCostPaymentForm.invalid('date') }"
                :placeholder="t('views.purchase_additional_cost_payment.fields.date')"
                @change="purchaseAdditionalCostPaymentForm.validate('date')"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostPaymentForm.errors.date" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostPaymentForm.invalid('cash_account_id') }">
                {{ t('views.purchase_additional_cost_payment.fields.cash_account') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseAdditionalCostPaymentForm.cash_account_id"
                v-model:search="cashAccountSearch"
                :options="cashAccountOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': purchaseAdditionalCostPaymentForm.invalid('cash_account_id') }"
                @change="purchaseAdditionalCostPaymentForm.validate('cash_account_id')"
                @search="loadCashAccountDDL"
                @clear="clearCashAccount"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostPaymentForm.errors.cash_account_id" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostPaymentForm.invalid('amount') }">
                {{ t('views.purchase_additional_cost_payment.fields.amount') }}
              </FormLabel>
              <FormInputCurrency
                v-model="purchaseAdditionalCostPaymentForm.amount"
                :allow-negative="false"
                :class="{ 'border-danger': purchaseAdditionalCostPaymentForm.invalid('amount') }"
                :placeholder="t('views.purchase_additional_cost_payment.fields.amount')"
                @change="purchaseAdditionalCostPaymentForm.validate('amount')"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostPaymentForm.errors.amount" />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': purchaseAdditionalCostPaymentForm.invalid('remarks') }">
                {{ t('views.purchase_additional_cost_payment.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="purchaseAdditionalCostPaymentForm.remarks"
                rows="3"
                :class="{ 'border-danger': purchaseAdditionalCostPaymentForm.invalid('remarks') }"
                :placeholder="t('views.purchase_additional_cost_payment.fields.remarks')"
                @change="purchaseAdditionalCostPaymentForm.validate('remarks')"
              />
              <FormErrorMessages :messages="purchaseAdditionalCostPaymentForm.errors.remarks" />
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
            :disabled="purchaseAdditionalCostPaymentForm.validating || purchaseAdditionalCostPaymentForm.hasErrors"
          >
            <Lucide v-if="purchaseAdditionalCostPaymentForm.validating" icon="Loader" class="animate-spin" />
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
