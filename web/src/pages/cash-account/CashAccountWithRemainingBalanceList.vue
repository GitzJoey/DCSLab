<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { DataListFlex } from '@/components/DataList';
import { FormInputDateTime, FormLabel, FormSelect } from '@/components/Base/Form';
import { Collection } from '@/types/resources/Collection';
import { CashAccount } from '@/types/models/CashAccount';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import CashAccountService from '@/services/CashAccountService';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import { CashAccountReadAnyPaginateRequest } from '@/types/services/cash_account/CashAccountRequest';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const cashAccountService = new CashAccountService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'show-alertplaceholder']);

interface CashAccountWithRemainingBalanceFilters {
  search: string;
  endDate: string | null;
  is_bank: boolean | null;
}

const filters = ref<CashAccountWithRemainingBalanceFilters>({
  search: '',
  endDate: null,
  is_bank: null,
});

const cashAccountLists = ref<Collection<Array<CashAccount>> | null>({
  data: [],
  meta: {
    current_page: 0,
    from: null,
    last_page: 0,
    path: '',
    per_page: 0,
    to: null,
    total: 0,
  },
  links: {
    first: '',
    last: '',
    prev: null,
    next: null,
  },
});

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  filters.value.search = '';
  const now = new Date();
  const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
  filters.value.endDate = formatDate(endOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');

  await getCashAccountsWithRemainingBalance('', true, 1, 10);
});

const getCashAccountsWithRemainingBalance = async (
  search: string,
  refresh: boolean,
  page: number,
  per_page: number,
) => {
  emits('loading-state', true);

  const searchReq: CashAccountReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    is_bank: filters.value.is_bank,
    with_remaining_balance: {
      end_date: filters.value.endDate,
    },
    refresh,
    page,
    per_page,
  };

  const result: ServiceResponse<Collection<Array<CashAccount>> | null> =
    await cashAccountService.readAnyPaginate(searchReq);

  if (result.success && result.data) {
    cashAccountLists.value = result.data as Collection<Array<CashAccount>>;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  filters.value.search = data.search.text;

  await getCashAccountsWithRemainingBalance(
    filters.value.search,
    false,
    data.pagination.page,
    data.pagination.per_page,
  );
};

const handleEndDateFilterChange = async () => {
  const perPage = cashAccountLists.value?.meta.per_page || 10;

  await getCashAccountsWithRemainingBalance(filters.value.search, true, 1, perPage);
};

const handleIsBankFilterChange = async () => {
  const perPage = cashAccountLists.value?.meta.per_page || 10;

  await getCashAccountsWithRemainingBalance(filters.value.search, true, 1, perPage);
};

const getRemainingBalance = (cashAccount: CashAccount): number => {
  return cashAccount.remaining_balance ?? 0;
};

const getCashAccountTypeLabel = (cashAccount: CashAccount): string => {
  return cashAccount.is_bank ? t('views.cash_account.types.bank') : t('views.cash_account.types.cash');
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
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y lg:col-span-12">
      <div class="grid grid-cols-12 gap-4 gap-y-3 mb-3">
        <div class="col-span-12 lg:col-span-4 md:col-span-6">
          <FormLabel>
            {{ t('views.cash_account.fields.with_remaining_balance_end_date') }}
          </FormLabel>
          <FormInputDateTime
            v-model="filters.endDate"
            :placeholder="t('views.cash_account.fields.with_remaining_balance_end_date')"
            @change="handleEndDateFilterChange"
          />
        </div>
        <div class="col-span-12 lg:col-span-4 md:col-span-6">
          <FormLabel>
            {{ t('views.cash_account.fields.is_bank') }}
          </FormLabel>
          <FormSelect v-model="filters.is_bank" @change="handleIsBankFilterChange">
            <option :value="null"></option>
            <option :value="true">{{ t('views.cash_account.types.bank') }}</option>
            <option :value="false">{{ t('views.cash_account.types.cash') }}</option>
          </FormSelect>
        </div>
      </div>

      <DataListFlex
        :data="cashAccountLists"
        :enable-search="true"
        :can-print="false"
        :can-export="false"
        :rows="cashAccountLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="cashAccountLists ? cashAccountLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item }">
          <div class="col-span-12 lg:col-span-6 sm:col-span-6 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.cash_account.page_title') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.cash_account.fields.code') }}:
              {{ (item as CashAccount).code ?? '-' }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.cash_account.fields.name') }}:
              {{ (item as CashAccount).name ?? '-' }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.cash_account.fields.is_bank') }}:
              {{ getCashAccountTypeLabel(item as CashAccount) }}
            </div>
          </div>
          <div class="col-span-12 lg:col-span-6 sm:col-span-6 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.cash_account.balance_suffix') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.cash_account.fields.remaining_balance') }}:
              {{ formatCurrency(getRemainingBalance(item as CashAccount)) }}
            </div>
          </div>
        </template>
      </DataListFlex>
    </div>
  </div>
</template>
