<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { Dialog } from '@/components/Base/Headless';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import PrepaidExpenseService from '@/services/PrepaidExpenseService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { PrepaidExpense } from '@/types/models/PrepaidExpense';
import type { PrepaidExpenseImage } from '@/types/models/PrepaidExpenseImage';
import type { PrepaidExpensePayment } from '@/types/models/PrepaidExpensePayment';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Collection } from '@/types/resources/Collection';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { PrepaidExpenseReadAnyPaginateRequest } from '@/types/services/prepaid-expense/PrepaidExpenseRequest';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const prepaidExpenseService = new PrepaidExpenseService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder', 'show-notification']);
const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<string | null>(null);
const isImagePreviewOpen = ref(false);
const previewImages = ref<PrepaidExpenseImage[]>([]);
const previewImageIndex = ref(0);
const previewPrepaidExpenseCode = ref('');
const prepaidExpenseLists = ref<Collection<Array<PrepaidExpense>> | null>({
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
  await getPrepaidExpenses('', true, 1, 10);
});

const getPrepaidExpenses = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);
  const searchReq: PrepaidExpenseReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    expense_category_id: null,
    is_amount_payable_paid_off: null,
    include_id: undefined,
    refresh,
    page,
    per_page,
  };
  const result: ServiceResponse<Collection<Array<PrepaidExpense>> | null> =
    await prepaidExpenseService.readAnyPaginate(searchReq);

  if (result.success && result.data) {
    prepaidExpenseLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  await getPrepaidExpenses(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const editSelected = (idx: number) => {
  if (!prepaidExpenseLists.value) return;
  router.push({
    name: 'side-menu-prepaid-expense-edit',
    params: { ulid: prepaidExpenseLists.value.data[idx].ulid },
  });
};

const viewSelected = (idx: number) => {
  if (!prepaidExpenseLists.value) return;

  const prepaidExpense = prepaidExpenseLists.value.data[idx];
  expandDetail.value = expandDetail.value === prepaidExpense.ulid ? null : prepaidExpense.ulid;
};

const deleteSelected = (idx: number) => {
  if (!prepaidExpenseLists.value) return;
  deleteUlid.value = prepaidExpenseLists.value.data[idx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);
  const result = await prepaidExpenseService.delete(deleteUlid.value);
  emits('loading-state', false);

  if (result.success) {
    emits('update-profile');
    await getPrepaidExpenses('', true, 1, 10);
    showNotification(
      t('views.prepaid_expense.alert.delete_prepaid_expense.title'),
      t('views.prepaid_expense.alert.delete_prepaid_expense.content'),
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

const getCategoryLabel = (prepaidExpense: PrepaidExpense) => {
  if (!prepaidExpense.category) return '-';

  return prepaidExpense.category.display_code
    ? `${prepaidExpense.category.display_code} - ${prepaidExpense.category.name}`
    : prepaidExpense.category.name;
};

const getCashAccountLabel = (prepaidExpense: PrepaidExpense) => {
  if (!prepaidExpense.paid_immediately_cash_account) return '-';

  return prepaidExpense.paid_immediately_cash_account.code
    ? `${prepaidExpense.paid_immediately_cash_account.code} - ${prepaidExpense.paid_immediately_cash_account.name}`
    : prepaidExpense.paid_immediately_cash_account.name;
};

const getPaidOffLabel = (prepaidExpense: PrepaidExpense) =>
  prepaidExpense.is_amount_payable_paid_off
    ? t('views.prepaid_expense.status.paid_off')
    : t('views.prepaid_expense.status.not_paid_off');

const formatEstimatedUsefulLife = (estimatedUsefulLife?: number | null): string => {
  const totalMonths = Number(estimatedUsefulLife ?? 0);
  const totalYears = totalMonths / 12;
  const formattedYears = Number.isInteger(totalYears)
    ? totalYears.toString()
    : totalYears.toFixed(1).replace(/\.0$/, '');
  const monthUnit = totalMonths === 1
    ? t('views.prepaid_expense.units.month')
    : t('views.prepaid_expense.units.months');
  const yearUnit = totalYears === 1
    ? t('views.prepaid_expense.units.year')
    : t('views.prepaid_expense.units.years');

  return `${totalMonths} ${monthUnit} (${formattedYears} ${yearUnit})`;
};

const getPrepaidExpenseImages = (prepaidExpense: PrepaidExpense): PrepaidExpenseImage[] => {
  const images = prepaidExpense.prepaid_expense_images?.filter((image) => !!image.url) ?? [];

  if (images.length > 0) {
    return images;
  }

  return prepaidExpense.main_prepaid_expense_image?.url ? [prepaidExpense.main_prepaid_expense_image] : [];
};

const getPrepaidExpensePayments = (prepaidExpense: PrepaidExpense) => prepaidExpense.payments ?? [];

const getPaymentCashAccountLabel = (payment: PrepaidExpensePayment) => {
  if (!payment.cash_account) return '-';

  return payment.cash_account.code ? `${payment.cash_account.code} - ${payment.cash_account.name}` : payment.cash_account.name;
};

const openImagePreview = (prepaidExpense: PrepaidExpense) => {
  const images = getPrepaidExpenseImages(prepaidExpense);

  if (!images.length) return;

  const mainImageIndex = images.findIndex((image) => image.is_main);

  previewImages.value = images;
  previewImageIndex.value = mainImageIndex >= 0 ? mainImageIndex : 0;
  previewPrepaidExpenseCode.value = prepaidExpense.code;
  isImagePreviewOpen.value = true;
};

const closeImagePreview = () => {
  isImagePreviewOpen.value = false;
  previewImages.value = [];
  previewImageIndex.value = 0;
  previewPrepaidExpenseCode.value = '';
};

const showPreviousImage = () => {
  if (previewImages.value.length <= 1) return;

  previewImageIndex.value =
    previewImageIndex.value === 0 ? previewImages.value.length - 1 : previewImageIndex.value - 1;
};

const showNextImage = () => {
  if (previewImages.value.length <= 1) return;

  previewImageIndex.value =
    previewImageIndex.value === previewImages.value.length - 1 ? 0 : previewImageIndex.value + 1;
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12">
      <DataListFlex
        :data="prepaidExpenseLists"
        :title="t('views.prepaid_expense.table.title')"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="prepaidExpenseLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="prepaidExpenseLists ? prepaidExpenseLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 md:col-span-6 lg:col-span-6 self-start">
            <div class="space-y-2">
              <div class="flex items-start justify-between gap-3">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.prepaid_expense.page_title') }}
                </div>
                <div
                  v-if="(item as PrepaidExpense).main_prepaid_expense_image?.url"
                  class="flex cursor-zoom-in items-center gap-2 rounded-md border border-slate-200/60 bg-slate-50 px-2 py-1 dark:border-darkmode-400 dark:bg-darkmode-700/50"
                  @click="openImagePreview(item as PrepaidExpense)"
                >
                  <img
                    :src="(item as PrepaidExpense).main_prepaid_expense_image?.url"
                    :alt="`${t('views.prepaid_expense.page_title')} ${t('views.prepaid_expense.fields.images')}`"
                    class="h-10 w-10 rounded object-cover"
                  />
                  <div class="text-[11px] font-medium text-slate-600 dark:text-slate-300">
                    {{ t('views.prepaid_expense.fields.images') }}
                  </div>
                </div>
              </div>
              <div class="grid grid-cols-12 items-start gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.prepaid_expense.fields.code') }}</div>
                <div class="col-span-8 break-words font-medium text-slate-700 dark:text-slate-200">
                  {{ (item as PrepaidExpense).code }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.prepaid_expense.fields.date') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ formatDate((item as PrepaidExpense).date, 'YYYY-MM-DD HH:mm:ss') }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.prepaid_expense.fields.category') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ getCategoryLabel(item as PrepaidExpense) }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.prepaid_expense.fields.estimated_useful_life') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ formatEstimatedUsefulLife((item as PrepaidExpense).estimated_useful_life) }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.prepaid_expense.fields.paid_immediately_cash_account') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ getCashAccountLabel(item as PrepaidExpense) }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.prepaid_expense.fields.amount_paid_immediately') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  <div class="w-full max-w-[8rem] font-semibold">
                    {{ formatCurrency(Number((item as PrepaidExpense).amount_paid_immediately ?? 0)) }}
                  </div>
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.prepaid_expense.fields.remarks') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PrepaidExpense).remarks?.trim() || '-' }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-6 lg:col-span-4 self-start lg:pr-4">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.prepaid_expense.field_groups.payable_summary') }}
              </div>
              <div class="grid grid-cols-12 items-start gap-x-3 gap-y-2 text-xs">
                <div class="col-span-5 text-slate-500">{{ t('views.prepaid_expense.fields.amount_payable') }}</div>
                <div class="col-span-7 text-right font-semibold text-slate-700 dark:text-slate-200">
                  {{ formatCurrency(Number((item as PrepaidExpense).amount_payable ?? 0)) }}
                </div>
                <div class="col-span-5 text-slate-500">{{ t('views.prepaid_expense.fields.due_days') }}</div>
                <div class="col-span-7 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as PrepaidExpense).due_days ?? 0 }}
                </div>
                <div class="col-span-5 text-slate-500">{{ t('views.prepaid_expense.fields.amount_payable_paid') }}</div>
                <div class="col-span-7 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency(Number((item as PrepaidExpense).amount_payable_paid ?? 0)) }}
                </div>
                <div class="col-span-5 text-slate-500">{{ t('views.prepaid_expense.fields.amount_payable_due') }}</div>
                <div class="col-span-7 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency(Number((item as PrepaidExpense).amount_payable_due ?? 0)) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.prepaid_expense.fields.amount_total') }}</div>
                <div class="col-span-5 text-right font-semibold text-primary">
                  {{ formatCurrency(Number((item as PrepaidExpense).amount_total ?? 0)) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.prepaid_expense.fields.is_amount_payable_paid_off') }}</div>
                <div class="col-span-5 text-right">
                  <span
                    class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium"
                    :class="
                      (item as PrepaidExpense).is_amount_payable_paid_off
                        ? 'bg-success/20 text-success'
                        : 'bg-warning/20 text-warning'
                    "
                  >
                    {{ getPaidOffLabel(item as PrepaidExpense) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-6 lg:col-span-2 self-center">
            <div class="flex justify-end gap-2 md:justify-start lg:min-h-full lg:items-center lg:justify-center">
                <Button
                  size="sm"
                  variant="outline-secondary"
                  class="flex h-9 w-9 items-center justify-center"
                  @click="viewSelected(index)"
                >
                  <Lucide icon="Info" class="w-4 h-4" />
                </Button>
                <Button
                  size="sm"
                  variant="outline-secondary"
                  class="flex h-9 w-9 items-center justify-center"
                  @click="editSelected(index)"
                >
                  <Lucide icon="Pen" class="w-4 h-4" />
                </Button>
                <Button
                  size="sm"
                  variant="outline-secondary"
                  class="flex h-9 w-9 items-center justify-center"
                  @click="deleteSelected(index)"
                >
                  <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                </Button>
            </div>
          </div>

          <div
            v-if="expandDetail === (item as PrepaidExpense).ulid"
            class="col-span-12 mt-2 border-t border-slate-200 pt-3 dark:border-darkmode-400"
          >
            <div class="grid grid-cols-12 gap-4">
              <div class="col-span-12 space-y-3">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  Payment Detail
                </div>

                <div v-if="!getPrepaidExpensePayments(item as PrepaidExpense).length" class="text-xs text-slate-500">
                  {{ t('components.data-list.data_not_found') }}
                </div>

                <div v-else class="space-y-3 rounded-md border border-slate-200/60 p-3 dark:border-darkmode-400">
                  <div
                    v-for="payment in getPrepaidExpensePayments(item as PrepaidExpense)"
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
                        {{ t('views.expense_payment.fields.cash_account') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ getPaymentCashAccountLabel(payment) }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.expense_payment.fields.amount') }}
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
        <Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
        <div class="mt-5 text-3xl">{{ t('components.delete-modal.title') }}</div>
        <div class="mt-2 text-slate-500">
          {{ t('components.delete-modal.desc_1') }}
          <br />
          {{ t('components.delete-modal.desc_2') }}
        </div>
      </div>
      <div class="px-5 pb-8 text-center">
        <Button variant="outline-secondary" type="button" class="w-24 mr-1" @click="deleteModalShow = false">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button variant="danger" type="button" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>

  <Dialog :open="isImagePreviewOpen" size="xl" @close="closeImagePreview">
    <Dialog.Panel class="flex flex-col">
      <Dialog.Title>
        <div class="flex items-center justify-between gap-4">
          <div class="font-medium">
            {{ previewPrepaidExpenseCode }} - {{ t('views.prepaid_expense.fields.images') }}
          </div>
          <div class="flex items-center gap-2">
            <Button
              type="button"
              variant="outline-secondary"
              size="sm"
              :disabled="previewImages.length <= 1"
              @click="showPreviousImage"
            >
              <Lucide icon="ChevronLeft" class="h-4 w-4" />
            </Button>
            <Button
              type="button"
              variant="outline-secondary"
              size="sm"
              :disabled="previewImages.length <= 1"
              @click="showNextImage"
            >
              <Lucide icon="ChevronRight" class="h-4 w-4" />
            </Button>
            <Button type="button" variant="outline-secondary" size="sm" @click="closeImagePreview">
              <Lucide icon="X" class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </Dialog.Title>
      <Dialog.Description class="space-y-4 bg-slate-900 p-4">
        <div class="flex min-h-[60vh] items-center justify-center">
          <img
            v-if="previewImages[previewImageIndex]?.url"
            :src="previewImages[previewImageIndex]?.url"
            class="max-h-[70vh] max-w-full object-contain"
          />
        </div>
        <div
          v-if="previewImages.length > 1"
          class="flex gap-2 overflow-x-auto border-t border-slate-700 pt-3"
        >
          <button
            v-for="(image, imageIndex) in previewImages"
            :key="image.id"
            type="button"
            class="overflow-hidden rounded border-2"
            :class="imageIndex === previewImageIndex ? 'border-primary' : 'border-transparent opacity-70'"
            @click="previewImageIndex = imageIndex"
          >
            <img :src="image.url" class="h-14 w-14 object-cover" />
          </button>
        </div>
      </Dialog.Description>
    </Dialog.Panel>
  </Dialog>
</template>
