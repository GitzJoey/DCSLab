<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { Dialog } from '@/components/Base/Headless';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import LiabilityService from '@/services/LiabilityService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { Liability } from '@/types/models/Liability';
import type { LiabilityPayment } from '@/types/models/LiabilityPayment';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Collection } from '@/types/resources/Collection';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { LiabilityReadAnyPaginateRequest } from '@/types/services/liability/LiabilityRequest';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const liabilityService = new LiabilityService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits([
  'mode-state',
  'loading-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);
const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<string | null>(null);
const liabilityLists = ref<Collection<Array<Liability>> | null>({
  data: [],
  meta: { current_page: 0, from: null, last_page: 0, path: '', per_page: 0, to: null, total: 0 },
  links: { first: '', last: '', prev: null, next: null },
});

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
    return;
  }

  await getLiabilities('', true, 1, 10);
});

const getLiabilities = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);

  const searchReq: LiabilityReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    category_id: null,
    creditor_id: null,
    supplier_id: null,
    is_paid_off: null,
    include_id: undefined,
    refresh,
    page,
    per_page,
  };

  const result: ServiceResponse<Collection<Array<Liability>> | null> =
    await liabilityService.readAnyPaginate(searchReq);

  if (result.success && result.data) {
    liabilityLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  await getLiabilities(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const editSelected = (idx: number) => {
  if (!liabilityLists.value) return;

  router.push({
    name: 'side-menu-liability-edit',
    params: { ulid: liabilityLists.value.data[idx].ulid },
  });
};

const viewSelected = (idx: number) => {
  if (!liabilityLists.value) return;

  const liability = liabilityLists.value.data[idx];
  expandDetail.value = expandDetail.value === liability.ulid ? null : liability.ulid;
};

const deleteSelected = (idx: number) => {
  if (!liabilityLists.value) return;

  deleteUlid.value = liabilityLists.value.data[idx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result = await liabilityService.delete(deleteUlid.value);

  emits('loading-state', false);

  if (result.success) {
    emits('update-profile');
    await getLiabilities('', true, 1, 10);
    showNotification(
      t('views.liability.alert.delete_liability.title'),
      t('views.liability.alert.delete_liability.content'),
    );
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
};

const showNotification = (pTitle: string, pContent: string) => {
  const n: NotificationData = { title: pTitle, content: pContent };
  emits('show-notification', n);
};

const showAlertPlaceholder = (
  pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark',
  pTitle: string,
  pAlertList: Record<string, Array<string>> | null,
) => {
  const ap: AlertPlaceholderProps = { alertType: pAlertType, title: pTitle, alertList: pAlertList };
  emits('show-alertplaceholder', ap);
};

const getCategoryLabel = (liability: Liability) => liability.category?.name ?? '-';

const getPartyLabel = (liability: Liability) => {
  if (liability.creditor) {
    return liability.creditor.code
      ? `${liability.creditor.code} - ${liability.creditor.name}`
      : liability.creditor.name;
  }

  if (liability.supplier) {
    return liability.supplier.code
      ? `${liability.supplier.code} - ${liability.supplier.name}`
      : liability.supplier.name;
  }

  return '-';
};

const getPartyTypeLabel = (liability: Liability) =>
  liability.creditor
    ? t('views.liability.party_types.creditor')
    : liability.supplier
      ? t('views.liability.party_types.supplier')
      : '-';

const getCashAccountLabel = (liability: Liability) => {
  if (!liability.cash_account) return '-';

  return liability.cash_account.code
    ? `${liability.cash_account.code} - ${liability.cash_account.name}`
    : liability.cash_account.name;
};

const getPaidOffLabel = (liability: Liability) =>
  liability.is_paid_off ? t('views.liability.status.paid_off') : t('views.liability.status.not_paid_off');

const getLiabilityPayments = (liability: Liability) => liability.payments ?? [];

const getPaymentCashAccountLabel = (payment: LiabilityPayment) => {
  if (!payment.cash_account) return '-';

  return payment.cash_account.code
    ? `${payment.cash_account.code} - ${payment.cash_account.name}`
    : payment.cash_account.name;
};
</script>

<template>
  <div class="mt-5 grid grid-cols-12 gap-6">
    <div class="col-span-12">
      <DataListFlex
        :data="liabilityLists"
        :title="t('views.liability.table.title')"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="liabilityLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="liabilityLists ? liabilityLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 self-start md:col-span-6 lg:col-span-5">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.liability.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-start gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.liability.fields.code') }}</div>
                <div class="col-span-8 break-words font-medium text-slate-700 dark:text-slate-200">
                  {{ (item as Liability).code }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.liability.fields.date') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ formatDate((item as Liability).date, 'YYYY-MM-DD HH:mm:ss') }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.liability.fields.category') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ getCategoryLabel(item as Liability) }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.liability.fields.party_type') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ getPartyTypeLabel(item as Liability) }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.liability.fields.party') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ getPartyLabel(item as Liability) }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.liability.fields.cash_account') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ getCashAccountLabel(item as Liability) }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.liability.fields.remarks') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as Liability).remarks?.trim() || '-' }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start md:col-span-6 lg:col-span-5 lg:pr-4">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.liability.field_groups.payable_summary') }}
              </div>
              <div class="grid grid-cols-12 items-start gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.liability.fields.amount_received') }}</div>
                <div class="col-span-5 text-right font-semibold text-slate-700 dark:text-slate-200">
                  {{ formatCurrency(Number((item as Liability).amount_received ?? 0)) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.liability.fields.amount_payable') }}</div>
                <div class="col-span-5 text-right font-semibold text-slate-700 dark:text-slate-200">
                  {{ formatCurrency(Number((item as Liability).amount_payable ?? 0)) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.liability.fields.amount_total') }}</div>
                <div class="col-span-5 text-right font-semibold text-primary">
                  {{ formatCurrency(Number((item as Liability).amount_total ?? 0)) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.liability.fields.amount_paid_by_cash_account') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency(Number((item as Liability).amount_paid_by_cash_account ?? 0)) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.liability.fields.amount_paid_by_stock_adjustment') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency(Number((item as Liability).amount_paid_by_stock_adjustment ?? 0)) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.liability.fields.amount_due') }}</div>
                <div class="col-span-5 text-right font-semibold text-slate-700 dark:text-slate-200">
                  {{ formatCurrency(Number((item as Liability).amount_due ?? 0)) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.liability.fields.due_days') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as Liability).due_days ?? 0 }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.liability.fields.is_paid_off') }}</div>
                <div class="col-span-5 text-right">
                  <span
                    class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium"
                    :class="
                      (item as Liability).is_paid_off
                        ? 'bg-success/20 text-success'
                        : 'bg-warning/20 text-warning'
                    "
                  >
                    {{ getPaidOffLabel(item as Liability) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-center md:col-span-6 lg:col-span-2">
            <div class="flex justify-end gap-2 md:justify-start lg:min-h-full lg:items-center lg:justify-center">
              <Button
                size="sm"
                variant="outline-secondary"
                class="flex h-9 w-9 items-center justify-center"
                @click="viewSelected(index)"
              >
                <Lucide icon="Info" class="h-4 w-4" />
              </Button>
              <Button
                size="sm"
                variant="outline-secondary"
                class="flex h-9 w-9 items-center justify-center"
                @click="editSelected(index)"
              >
                <Lucide icon="Pen" class="h-4 w-4" />
              </Button>
              <Button
                size="sm"
                variant="outline-secondary"
                class="flex h-9 w-9 items-center justify-center"
                @click="deleteSelected(index)"
              >
                <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
              </Button>
            </div>
          </div>

          <div
            v-if="expandDetail === (item as Liability).ulid"
            class="col-span-12 mt-2 border-t border-slate-200 pt-3 dark:border-darkmode-400"
          >
            <div class="grid grid-cols-12 gap-4">
              <div class="col-span-12 space-y-3">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.liability.field_groups.payments') }}
                </div>

                <div v-if="!getLiabilityPayments(item as Liability).length" class="text-xs text-slate-500">
                  {{ t('components.data-list.data_not_found') }}
                </div>

                <div v-else class="space-y-3 rounded-md border border-slate-200/60 p-3 dark:border-darkmode-400">
                  <div
                    v-for="payment in getLiabilityPayments(item as Liability)"
                    :key="payment.ulid"
                    class="border-b border-slate-200/60 pb-3 text-sm last:border-b-0 last:pb-0 dark:border-darkmode-400"
                  >
                    <div class="font-medium break-words text-slate-700 dark:text-slate-200">
                      {{ payment.code }}
                    </div>
                    <div class="mt-1 break-words text-xs text-slate-500">
                      {{ formatDate(payment.date, 'YYYY-MM-DD HH:mm:ss') }}
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-600 dark:text-slate-300">
                      <span>
                        {{ t('views.liability.fields.payment_cash_account') }}
                        <span class="text-slate-700 dark:text-slate-200">
                          {{ getPaymentCashAccountLabel(payment) }}
                        </span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.liability.fields.payment_amount') }}
                        <span class="font-medium text-slate-700 dark:text-slate-200">
                          {{ formatCurrency(Number(payment.amount ?? 0)) }}
                        </span>
                      </span>
                    </div>
                    <div v-if="payment.remarks?.trim()" class="mt-2 break-words text-xs text-slate-500">
                      {{ payment.remarks }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </DataListFlex>
    </div>
  </div>

  <Dialog :open="deleteModalShow" @close="() => { deleteModalShow = false; }">
    <Dialog.Panel>
      <div class="p-5 text-center">
        <Lucide icon="XCircle" class="mx-auto mt-3 h-16 w-16 text-danger" />
        <div class="mt-5 text-3xl">{{ t('components.delete-modal.title') }}</div>
        <div class="mt-2 text-slate-500">
          {{ t('components.delete-modal.desc_1') }}
          <br />
          {{ t('components.delete-modal.desc_2') }}
        </div>
      </div>
      <div class="px-5 pb-8 text-center">
        <Button variant="outline-secondary" type="button" class="mr-1 w-24" @click="deleteModalShow = false">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button variant="danger" type="button" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
