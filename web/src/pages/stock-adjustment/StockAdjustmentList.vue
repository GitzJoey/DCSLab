<script setup lang="ts">
  import { computed, onMounted, ref } from 'vue';
  import { useI18n } from 'vue-i18n';
  import DataList from '@/components/DataList';
  import Table from '@/components/Base/Table';
  import Button from '@/components/Base/Button';
  import Lucide from '@/components/Base/Lucide';
  import { Dialog } from '@/components/Base/Headless';
  import StockAdjustmentService from '@/services/StockAdjustmentService';
  import { StockAdjustment } from '@/types/models/StockAdjustment';
  import { NotificationData } from '@/types/models/NotificationData';
  import { Collection } from '@/types/resources/Collection';
  import { DataListEmittedData } from '@/components/DataList/DataList.vue';
  import { ServiceResponse } from '@/types/services/ServiceResponse';
  import { StockAdjustmentReadAnyPaginateRequest } from '@/types/services/stock-adjustment/StockAdjustmentRequest';
  import { useRouter } from 'vue-router';
  import { ViewMode } from '@/types/enums/ViewMode';
  import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
  import { ErrorCode } from '@/types/enums/ErrorCode';
  import { formatDate, formatCurrency } from '@/utils/helper';
  import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';

  const { t } = useI18n();
  const router = useRouter();
  const stockAdjustmentService = new StockAdjustmentService();
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
  const expandDetail = ref<number | null>(null);

  const stockAdjustmentLists = ref<Collection<Array<StockAdjustment>> | null>({
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

  const isUserLocationSelected = computed(
    () => selectedUserLocationStore.isUserLocationSelected
  );
  const selectedUserLocation = computed(
    () => selectedUserLocationStore.selectedUserLocation
  );

  onMounted(async () => {
    emits('mode-state', ViewMode.LIST);

    if (!isUserLocationSelected.value) {
      router.push({
        name: 'side-menu-error-code',
        params: { code: ErrorCode.USERLOCATION_REQUIRED },
      });
      return;
    }

    await getStockAdjustments('', true, 1, 10);
  });

  const getStockAdjustments = async (
    search: string,
    refresh: boolean,
    page: number,
    per_page: number
  ) => {
    emits('loading-state', true);

    const request: StockAdjustmentReadAnyPaginateRequest = {
      with_trashed: false,
      company_id: selectedUserLocation.value.company.id,
      branch_id: selectedUserLocation.value.branch.id,
      search,
      refresh,
      page,
      per_page,
    };

    const result: ServiceResponse<Collection<Array<StockAdjustment>> | null> =
      await stockAdjustmentService.readAnyPaginate(request);

    if (result.success && result.data) {
      stockAdjustmentLists.value = result.data;
      showAlertPlaceholder('hidden', '', null);
    } else {
      showAlertPlaceholder(
        'danger',
        '',
        result.errors as Record<string, Array<string>>
      );
    }

    emits('loading-state', false);
  };

  const handleDataListChange = async (data: DataListEmittedData) => {
    await getStockAdjustments(
      data.search.text,
      false,
      data.pagination.page,
      data.pagination.per_page
    );
  };

  const viewSelected = (idx: number) => {
    if (expandDetail.value === idx) {
      expandDetail.value = null;
    } else {
      expandDetail.value = idx;
    }
  };

  const editSelected = (idx: number) => {
    if (!stockAdjustmentLists.value) return;

    const ulid = stockAdjustmentLists.value.data[idx].ulid;

    emits('mode-state', ViewMode.FORM_EDIT);
    router.push({
      name: 'side-menu-stock-adjustment-edit',
      params: { ulid: ulid },
    });
  };

  const deleteSelected = (idx: number) => {
    if (!stockAdjustmentLists.value) return;

    const ulid = stockAdjustmentLists.value.data[idx].ulid;
    deleteUlid.value = ulid;
    deleteModalShow.value = true;
  };

  const confirmDelete = async () => {
    deleteModalShow.value = false;
    emits('loading-state', true);

    const result = await stockAdjustmentService.delete(deleteUlid.value);

    emits('loading-state', false);

    if (result.success) {
      emits('update-profile');
      await getStockAdjustments('', true, 1, 10);
      showNotification(
        t('views.stock_adjustment.alert.delete.title'),
        t('views.stock_adjustment.alert.delete.message')
      );
    } else {
      showAlertPlaceholder(
        'danger',
        '',
        result.errors as Record<string, Array<string>>
      );
    }
  };

  const showNotification = (pTitle: string, pContent: string) => {
    const n: NotificationData = {
      title: pTitle,
      content: pContent,
    };

    emits('show-notification', n);
  };

  const showAlertPlaceholder = (
    pAlertType:
      | 'hidden'
      | 'danger'
      | 'success'
      | 'warning'
      | 'pending'
      | 'dark',
    pTitle: string,
    pAlertList: Record<string, Array<string>> | null
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
  <!-- page layout -->
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y lg:col-span-12">
      <!-- data list wrapper -->
      <DataList
        :title="t('views.stock_adjustment.table.title')"
        :data="stockAdjustmentLists"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :pagination="stockAdjustmentLists ? stockAdjustmentLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #content>
          <!-- main table -->
          <Table class="mt-5" :hover="true">
            <Table.Thead variant="light">
              <Table.Tr>
                <!-- code -->
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment.table.cols.code') }}
                </Table.Th>
                <!-- date -->
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment.table.cols.date') }}
                </Table.Th>
                <!-- category -->
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment.table.cols.category') }}
                </Table.Th>
                <!-- in warehouse -->
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment.table.cols.in_warehouse') }}
                </Table.Th>
                <!-- out warehouse -->
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment.table.cols.out_warehouse') }}
                </Table.Th>
                <!-- is posted -->
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment.table.cols.is_posted') }}
                </Table.Th>
                <!-- remarks -->
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment.table.cols.remarks') }}
                </Table.Th>
                <!-- actions -->
                <Table.Th class="whitespace-nowrap"></Table.Th>
              </Table.Tr>
            </Table.Thead>
            <Table.Tbody v-if="stockAdjustmentLists !== null">
              <!-- data not found -->
              <template v-if="stockAdjustmentLists.data.length === 0">
                <Table.Tr class="intro-x">
                  <Table.Td colspan="8">
                    <div class="flex justify-center italic">
                      {{ t('components.data-list.data_not_found') }}
                    </div>
                  </Table.Td>
                </Table.Tr>
              </template>
              <!-- data found -->
              <template
                v-for="(item, itemIdx) in stockAdjustmentLists.data"
                :key="item.ulid"
              >
                <!-- main row -->
                <Table.Tr class="intro-x">
                  <!-- code -->
                  <Table.Td>
                    <div class="font-medium whitespace-nowrap">
                      {{ item.code }}
                    </div>
                  </Table.Td>
                  <!-- date -->
                  <Table.Td>
                    <div class="whitespace-nowrap">
                      {{ formatDate(item.date, 'DD-MMM-YYYY HH:mm:ss') }}
                    </div>
                  </Table.Td>
                  <!-- category -->
                  <Table.Td>
                    <div class="whitespace-nowrap">
                      {{ item.category.name }}
                    </div>
                  </Table.Td>
                  <!-- in warehouse -->
                  <Table.Td>
                    <div class="whitespace-nowrap">
                      {{ item.in_warehouse ? item.in_warehouse.name : '-' }}
                    </div>
                  </Table.Td>
                  <!-- out warehouse -->
                  <Table.Td>
                    <div class="whitespace-nowrap">
                      {{ item.out_warehouse ? item.out_warehouse.name : '-' }}
                    </div>
                  </Table.Td>
                  <!-- is posted -->
                  <Table.Td>
                    <div class="flex items-center">
                      <Lucide
                        v-if="item.is_posted"
                        icon="CheckCircle"
                        class="w-4 h-4 text-success"
                      />
                      <Lucide v-else icon="X" class="w-4 h-4 text-danger" />
                    </div>
                  </Table.Td>
                  <!-- remarks -->
                  <Table.Td>
                    <div class="truncate max-w-xs">
                      {{ item.remarks }}
                    </div>
                  </Table.Td>
                  <!-- actions -->
                  <Table.Td>
                    <div class="flex justify-end gap-1">
                      <Button
                        variant="outline-secondary"
                        @click="viewSelected(itemIdx)"
                      >
                        <Lucide icon="Info" class="w-4 h-4" />
                      </Button>
                      <Button
                        variant="outline-secondary"
                        @click="editSelected(itemIdx)"
                      >
                        <Lucide icon="Pen" class="w-4 h-4" />
                      </Button>
                      <Button
                        variant="outline-secondary"
                        @click="deleteSelected(itemIdx)"
                      >
                        <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                      </Button>
                    </div>
                  </Table.Td>
                </Table.Tr>
                <!-- detail row (expandable) -->
                <Table.Tr
                  :class="{
                    'intro-x': true,
                    'hidden transition-all': expandDetail !== itemIdx,
                  }"
                >
                  <Table.Td colspan="8" class="p-5">
                    <div class="grid grid-cols-12 gap-6">
                      <div class="col-span-12">
                        <!-- detail header -->
                        <div class="font-medium text-base mb-3 border-b pb-2">
                          {{ t('views.stock_adjustment.page_title') }}
                        </div>
                        <!-- detail fields -->
                        <div class="grid grid-cols-1 gap-y-2">
                          <!-- code -->
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.stock_adjustment.fields.code') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{ item.code }}
                            </div>
                          </div>
                          <!-- date -->
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.stock_adjustment.fields.date') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{
                                formatDate(item.date, 'DD-MMM-YYYY HH:mm:ss')
                              }}
                            </div>
                          </div>
                          <!-- category -->
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{
                                t('views.stock_adjustment.fields.category_id')
                              }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{ item.category.name }}
                            </div>
                          </div>
                          <!-- in warehouse -->
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{
                                t(
                                  'views.stock_adjustment.fields.in_warehouse_id'
                                )
                              }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{
                                item.in_warehouse ? item.in_warehouse.name : '-'
                              }}
                            </div>
                          </div>
                          <!-- out warehouse -->
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{
                                t(
                                  'views.stock_adjustment.fields.out_warehouse_id'
                                )
                              }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{
                                item.out_warehouse
                                  ? item.out_warehouse.name
                                  : '-'
                              }}
                            </div>
                          </div>
                          <!-- is posted -->
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.stock_adjustment.fields.is_posted') }}
                            </div>
                            <div class="flex-1 font-medium">
                              <div class="flex items-center">
                                <Lucide
                                  v-if="item.is_posted"
                                  icon="CheckCircle"
                                  class="w-4 h-4 text-success"
                                />
                                <Lucide
                                  v-else
                                  icon="X"
                                  class="w-4 h-4 text-danger"
                                />
                              </div>
                            </div>
                          </div>
                          <!-- remarks -->
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.stock_adjustment.fields.remarks') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{ item.remarks }}
                            </div>
                          </div>

                          <!-- in products -->
                          <div class="mt-4">
                            <div class="font-medium text-sm mb-2">
                              {{
                                t(
                                  'views.stock_adjustment.field_groups.in_products'
                                )
                              }}
                            </div>
                            <div
                              v-if="item.in_products.length === 0"
                              class="text-slate-500 text-sm"
                            >
                              {{ t('components.data-list.data_not_found') }}
                            </div>
                            <div v-else class="space-y-2 text-xs sm:text-sm">
                              <div
                                v-for="(p, index) in item.in_products"
                                :key="p.ulid"
                                class="flex gap-3"
                              >
                                <div class="w-6 text-right text-slate-500">
                                  {{ index + 1 }}.
                                </div>
                                <div class="flex-1">
                                  <div class="font-medium truncate">
                                    [{{ p.product_unit.code }}]
                                    {{ p.product_unit.product.name }}
                                  </div>
                                  <div class="mt-0.5 text-slate-500">
                                    {{
                                      t(
                                        'views.stock_adjustment_in_product.fields.qty'
                                      )
                                    }}:
                                    <span class="font-medium">
                                      {{ formatCurrency(p.qty) }}
                                      {{ p.product_unit.unit.name }}
                                    </span>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>

                          <!-- out products -->
                          <div class="mt-4">
                            <div class="font-medium text-sm mb-2">
                              {{
                                t(
                                  'views.stock_adjustment.field_groups.out_products'
                                )
                              }}
                            </div>
                            <div
                              v-if="item.out_products.length === 0"
                              class="text-slate-500 text-sm"
                            >
                              {{ t('components.data-list.data_not_found') }}
                            </div>
                            <div v-else class="space-y-2 text-xs sm:text-sm">
                              <div
                                v-for="(p, index) in item.out_products"
                                :key="p.ulid"
                                class="flex gap-3"
                              >
                                <div class="w-6 text-right text-slate-500">
                                  {{ index + 1 }}.
                                </div>
                                <div class="flex-1">
                                  <div class="font-medium truncate">
                                    [{{ p.product_unit.code }}]
                                    {{ p.product_unit.product.name }}
                                  </div>
                                  <div class="mt-0.5 text-slate-500">
                                    {{
                                      t(
                                        'views.stock_adjustment_in_product.fields.qty'
                                      )
                                    }}:
                                    <span class="font-medium">
                                      {{ formatCurrency(p.qty) }}
                                      {{ p.product_unit.unit.name }}
                                    </span>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </Table.Td>
                </Table.Tr>
              </template>
            </Table.Tbody>
          </Table>
        </template>
      </DataList>
    </div>
  </div>
  <!-- delete confirmation modal -->
  <Dialog
    :open="deleteModalShow"
    @close="
      () => {
        deleteModalShow = false;
      }
    "
  >
    <Dialog.Panel>
      <!-- modal content -->
      <div class="p-5 text-center">
        <Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
        <div class="mt-5 text-3xl">
          {{ t('components.delete-modal.title') }}
        </div>
        <div class="mt-2 text-slate-500">
          {{ t('components.delete-modal.desc_1') }}
          <br />
          {{ t('components.delete-modal.desc_2') }}
        </div>
      </div>
      <!-- modal actions -->
      <div class="px-5 pb-8 text-center">
        <Button
          type="button"
          variant="outline-secondary"
          @click="
            () => {
              deleteModalShow = false;
            }
          "
          class="w-24 mr-1"
        >
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button
          type="button"
          variant="danger"
          class="w-24"
          @click="confirmDelete"
        >
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
