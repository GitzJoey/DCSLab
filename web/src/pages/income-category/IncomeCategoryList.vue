<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref } from 'vue';
import DataList from '@/components/DataList';
import TreeList from '@/components/TreeList/TreeList.vue';
import { useI18n } from 'vue-i18n';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import Table from '@/components/Base/Table';
import { Dialog } from '@/components/Base/Headless';
import { useRouter } from 'vue-router';
import IncomeCategoryService from '@/services/IncomeCategoryService';
import { IncomeCategory } from '@/types/models/IncomeCategory';
import { Resource } from '@/types/resources/Resource';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import { ViewMode } from '@/types/enums/ViewMode';
import { NotificationData } from '@/types/models/NotificationData';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
// #endregion

interface TreeListRef {
  expandAll: () => void;
  collapseAll: () => void;
}

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const incomeCategoryService = new IncomeCategoryService();
const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits([
  'mode-state',
  'loading-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);
// #endregion

// #region Refs
const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<string | number | null>(null);
const treeListRef = ref<TreeListRef | null>(null);
const incomeCategoryLists = ref<Resource<Array<IncomeCategory>> | null>({
  data: [],
});
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await getIncomeCategories('', true);
});
// #endregion

// #region Methods
const getIncomeCategories = async (search: string, refresh: boolean) => {
  emits('loading-state', true);

  const result: ServiceResponse<Resource<Array<IncomeCategory>> | null> = await incomeCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    parent_id: undefined,
    has_parent: false,
    has_children: undefined,
    include_id: undefined,
    refresh,
    limit: 1000,
  });

  if (result.success && result.data) {
    incomeCategoryLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const onDataListChanged = async () => {
  await getIncomeCategories('', false);
};

const viewSelected = (itemId: string | number) => {
  if (expandDetail.value === itemId) {
    expandDetail.value = null;
  } else {
    expandDetail.value = itemId;
  }
};

const editSelected = (item: IncomeCategory) => {
  router.push({
    name: 'side-menu-income-category-edit',
    params: { ulid: item.ulid },
  });
};

const deleteSelected = (item: IncomeCategory) => {
  deleteUlid.value = item.ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result: ServiceResponse<boolean | null> = await incomeCategoryService.delete(deleteUlid.value);

  if (result.success) {
    emits('update-profile');
    await getIncomeCategories('', true);
    showNotification(
      t('views.income_category.alert.delete_income_category.title'),
      t('views.income_category.alert.delete_income_category.content'),
    );
  } else {
    const alertList = result.errors ?? convertErrorTypeToAlertListType({ message: result.message });
    showAlertPlaceholder('danger', result.message ?? '', alertList);
  }

  emits('loading-state', false);
};

const getRowIndentStyle = (depth: number) => ({
  paddingLeft: `${depth * 1.5}rem`,
});

const expandAllTreeRows = () => {
  treeListRef.value?.expandAll();
};

const collapseAllTreeRows = () => {
  treeListRef.value?.collapseAll();
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
// #endregion
</script>

<template>
  <DataList
    :title="t('views.income_category.table.title')"
    :enable-search="false"
    :can-print="true"
    :can-export="true"
    :pagination="null"
    @dataListChanged="onDataListChanged"
  >
    <template #toolbar-actions>
      <Button variant="outline-secondary" @click="collapseAllTreeRows">
        <Lucide icon="ChevronsUpDown" class="mr-1 h-4 w-4" />
        Collapse All
      </Button>
      <Button variant="outline-secondary" @click="expandAllTreeRows">
        <Lucide icon="ListTree" class="mr-1 h-4 w-4" />
        Expand All
      </Button>
    </template>

    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.income_category.fields.code') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.income_category.fields.name') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap"></Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody v-if="incomeCategoryLists !== null">
          <template v-if="incomeCategoryLists.data.length == 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="3">
                <div class="flex justify-center italic">
                  {{ t('components.data-list.data_not_found') }}
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
          <TreeList v-else ref="treeListRef" :items="incomeCategoryLists.data">
            <template #row="{ item, depth, hasChildren, isExpanded, toggle }">
            <Table.Tr class="intro-x">
              <Table.Td>
                <div class="font-medium">{{ item.display_code }}</div>
              </Table.Td>
              <Table.Td>
                <div class="flex items-center gap-2" :style="getRowIndentStyle(depth)">
                  <button
                    v-if="hasChildren"
                    type="button"
                    class="flex h-5 w-5 items-center justify-center rounded border border-slate-200 text-slate-500 transition hover:bg-slate-100"
                    :aria-label="isExpanded ? 'Collapse row' : 'Expand row'"
                    @click="toggle()"
                  >
                    <Lucide :icon="isExpanded ? 'ChevronDown' : 'ChevronRight'" class="h-3 w-3" />
                  </button>
                  <Lucide v-else-if="depth > 0" icon="CornerDownRight" class="w-4 h-4 text-slate-400" />
                  <div v-else class="w-5"></div>
                  <span>{{ item.name }}</span>
                </div>
              </Table.Td>
              <Table.Td>
                <div class="flex justify-end gap-1">
                  <Button variant="outline-secondary" @click="viewSelected(item.id)">
                    <Lucide icon="Info" class="w-4 h-4" />
                  </Button>
                  <Button variant="outline-secondary" @click="editSelected(item as IncomeCategory)">
                    <Lucide icon="Pen" class="w-4 h-4" />
                  </Button>
                  <Button variant="outline-secondary" @click="deleteSelected(item as IncomeCategory)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </Table.Td>
            </Table.Tr>
            <Table.Tr
              :class="{
                'intro-x': true,
                'hidden transition-all': expandDetail !== item.id,
              }"
            >
              <Table.Td colspan="3">
                <div class="flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">
                    {{ t('views.income_category.fields.code') }}
                  </div>
                  <div class="flex-1">{{ item.display_code }}</div>
                </div>
                <div class="mt-1 flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">
                    {{ t('views.income_category.fields.name') }}
                  </div>
                  <div class="flex-1">{{ item.name }}</div>
                </div>
                <div class="mt-1 flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">
                    {{ t('views.income_category.fields.parent') }}
                  </div>
                  <div class="flex-1">{{ item.parent?.name ?? '-' }}</div>
                </div>
                <div class="mt-1 flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">
                    {{ t('views.income_category.fields.sequence') }}
                  </div>
                  <div class="flex-1">{{ item.sequence }}</div>
                </div>
                <div class="mt-1 flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">
                    {{ t('views.income_category.fields.child_count') }}
                  </div>
                  <div class="flex-1">{{ item.children?.length ?? 0 }}</div>
                </div>
              </Table.Td>
            </Table.Tr>
            </template>
          </TreeList>
        </Table.Tbody>
      </Table>

      <Dialog
        :open="deleteModalShow"
        @close="
          () => {
            deleteModalShow = false;
          }
        "
      >
        <Dialog.Panel>
          <div class="p-5 text-center">
            <Lucide icon="XCircle" class="mx-auto mt-3 h-16 w-16 text-danger" />
            <div class="mt-5 text-3xl">
              {{ t('components.delete-modal.title') }}
            </div>
            <div class="mt-2 text-slate-500">
              {{ t('components.delete-modal.desc_1') }}
              <br />
              {{ t('components.delete-modal.desc_2') }}
            </div>
          </div>
          <div class="px-5 pb-8 text-center">
            <Button
              type="button"
              variant="outline-secondary"
              class="mr-1 w-24"
              @click="
                () => {
                  deleteModalShow = false;
                }
              "
            >
              {{ t('components.buttons.cancel') }}
            </Button>
            <Button type="button" variant="danger" class="w-24" @click="confirmDelete">
              {{ t('components.buttons.delete') }}
            </Button>
          </div>
        </Dialog.Panel>
      </Dialog>
    </template>
  </DataList>
</template>
