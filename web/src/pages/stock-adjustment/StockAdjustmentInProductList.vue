<script setup lang="ts">
  import { computed, onMounted, ref } from 'vue';
  import { useI18n } from 'vue-i18n';
  import DataList from '@/components/DataList';
  import Table from '@/components/Base/Table';
  import Button from '@/components/Base/Button';
  import Lucide from '@/components/Base/Lucide';
  import { Dialog } from '@/components/Base/Headless';
  import StockAdjustmentInProductService from '@/services/StockAdjustmentInProductService';
  import { StockAdjustmentInProduct } from '@/types/models/StockAdjustmentInProduct';
  import { NotificationData } from '@/types/models/NotificationData';
  import { Collection } from '@/types/resources/Collection';
  import { DataListEmittedData } from '@/components/DataList/DataList.vue';
  import { ServiceResponse } from '@/types/services/ServiceResponse';
  import { StockAdjustmentInProductReadAnyPaginateRequest } from '@/types/services/stock-adjustment-in-product/StockAdjustmentInProductRequest';
  import { useRouter } from 'vue-router';
  import { ViewMode } from '@/types/enums/ViewMode';
  import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
  import { ErrorCode } from '@/types/enums/ErrorCode';
  import { formatCurrency } from '@/utils/helper';
  import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';

  const { t } = useI18n();
  const router = useRouter();
  const stockAdjustmentInProductService = new StockAdjustmentInProductService();
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

  const stockAdjustmentInProductLists = ref<Collection<Array<StockAdjustmentInProduct>> | null>({
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

    await getStockAdjustmentInProducts('', true, 1, 10);
  });

  const getStockAdjustmentInProducts = async (search: string, refresh: boolean, page: number, per_page: number) => {
    emits('loading-state', true);

    const request: StockAdjustmentInProductReadAnyPaginateRequest = {
      with_trashed: false,
      company_id: selectedUserLocation.value.company.id,
      branch_id: selectedUserLocation.value.branch.id,
      search,
      refresh,
      page,
      per_page,
    };

    const result: ServiceResponse<Collection<Array<StockAdjustmentInProduct>> | null> =
      await stockAdjustmentInProductService.readAnyPaginate(request);

    if (result.success && result.data) {
      stockAdjustmentInProductLists.value = result.data;
      showAlertPlaceholder('hidden', '', null);
    } else {
      showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
    }

    emits('loading-state', false);
  };

  const handleDataListChange = async (data: DataListEmittedData) => {
    await getStockAdjustmentInProducts(
      data.search.text, 
      true, 
      data.pagination.page, 
      data.pagination.per_page
    );
  };

  const deleteSelected = (idx: number) => {
    if (!stockAdjustmentInProductLists.value) return;

    const ulid = stockAdjustmentInProductLists.value.data[idx].ulid;
    deleteUlid.value = ulid;
    deleteModalShow.value = true;
  };

  const confirmDelete = async () => {
    deleteModalShow.value = false;
    emits('loading-state', true);

    const result = await stockAdjustmentInProductService.delete(deleteUlid.value);

    emits('loading-state', false);

    if (result.success) {
      emits('update-profile');
      await getStockAdjustmentInProducts('', true, 1, 10);
      showNotification(
        t('components.delete-modal.title'),
        t('components.delete-modal.message'),
      );
    } else {
      showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
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
      <DataList
        :title="t('views.stock_adjustment_in_product.table.title')"
        :data="stockAdjustmentInProductLists"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :pagination="stockAdjustmentInProductLists ? stockAdjustmentInProductLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #content>
          <Table class="mt-5" :hover="true">
            <Table.Thead variant="light">
              <Table.Tr>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment_in_product.table.cols.product') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment_in_product.table.cols.qty') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment_in_product.table.cols.product_unit') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment_in_product.table.cols.product_unit_conversion_value') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment_in_product.table.cols.product_unit_cogs') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment_in_product.table.cols.product_unit_total_cogs') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment_in_product.table.cols.remarks') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap"></Table.Th>
              </Table.Tr>
            </Table.Thead>
            <Table.Tbody v-if="stockAdjustmentInProductLists !== null">
              <template v-if="stockAdjustmentInProductLists.data.length === 0">
                <Table.Tr class="intro-x">
                  <Table.Td colspan="8">
                    <div class="flex justify-center italic">
                      {{ t('components.data-list.data_not_found') }}
                    </div>
                  </Table.Td>
                </Table.Tr>
              </template>
              <template v-for="(item, itemIdx) in stockAdjustmentInProductLists.data" :key="item.ulid">
                <Table.Tr class="intro-x">
                  <Table.Td>
                    <div class="font-medium whitespace-nowrap">
                      {{ item.product_unit?.product?.name ?? '-' }}
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="whitespace-nowrap">
                      {{ item.qty }}
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="whitespace-nowrap">
                      {{ item.product_unit?.unit?.name ?? '-' }}
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="whitespace-nowrap">
                      {{ item.product_unit_conversion_value }}
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="whitespace-nowrap">
                      {{ formatCurrency(item.product_unit_cogs) }}
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="whitespace-nowrap">
                      {{ formatCurrency(item.product_unit_total_cogs) }}
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="whitespace-nowrap">
                      {{ item.remarks ?? '-' }}
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="flex justify-end gap-1">
                      <Button variant="outline-secondary" @click="deleteSelected(itemIdx)">
                        <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                      </Button>
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

  <Dialog :open="deleteModalShow" @close="deleteModalShow = false">
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
        <Button type="button" variant="outline-secondary" @click="deleteModalShow = false" class="w-24 mr-1">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button type="button" variant="danger" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
