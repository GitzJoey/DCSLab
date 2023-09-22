<script setup lang="ts">
// #region Imports
import { onMounted, ref } from "vue";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
import DataList from "../../base-components/DataList";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import Table from "../../base-components/Table";
import BranchService from "../../services/BranchService";
import { Branch } from "../../types/models/Branch";
import { Collection } from "../../types/resources/Collection";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { SearchFormFieldValues } from "../../types/forms/SearchFormFieldValues";
import { useRouter } from "vue-router";
import { Dialog } from "../../base-components/Headless";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const branchServices = new BranchService();
// #endregion

// #region Props, Emits
const emit = defineEmits(['title-view', 'loading-state']);
// #endregion

// #region Refs
const datalistErrors = ref<Record<string, Array<string>> | null>(null);
const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
const branchLists = ref<Collection<Array<Branch>> | null>({
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
  }
});
// #endregion

// #region Computed
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
  emit('title-view', t('views.user.page_title'));
  await getBranches('', true, true, 1, 10);
});
// #endregion

// #region Methods
const getBranches = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {
  emit('loading-state', true);

  const searchReq: SearchFormFieldValues = {
    search: search,
    refresh: refresh,
    paginate: paginate,
    page: page,
    per_page: per_page
  };

  let result: ServiceResponse<Collection<Array<Branch>> | Resource<Array<Branch>> | null> = await branchServices.readAny('', searchReq);

  if (result.success && result.data) {
    branchLists.value = result.data as Collection<Array<Branch>>;
  } else {
    datalistErrors.value = result.errors as Record<string, Array<string>>;
  }

  emit('loading-state', false);
}

const onDataListChanged = async (data: DataListEmittedData) => {
  await getBranches(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (itemIdx: number) => {
  if (!branchLists.value) return;

  let ulid = branchLists.value.data[itemIdx].ulid;
  router.push({ name: 'side-menu-company-company-edit', params: { ulid: ulid } });
}

const deleteSelected = (itemIdx: number) => {
  if (!branchLists.value) return;

  let itemUlid = branchLists.value.data[itemIdx].ulid;

  deleteUlid.value = itemUlid;
  deleteModalShow.value = true;
}

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emit('loading-state', true);

  let result: ServiceResponse<boolean | null> = await branchServices.delete(deleteUlid.value);

  if (result.success) {
    await getBranches('', true, true, 1, 10);
  }

  emit('loading-state', false);
}
// #endregion

// #region Watchers
// #endregion
</script>

<template>
  <AlertPlaceholder :errors="datalistErrors" />
  <DataList :title="t('views.company.table.title')" :enable-search="true" :can-print="true" :can-export="true"
    :pagination="branchLists ? branchLists.meta : null" @dataListChanged="onDataListChanged">
    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">

        </Table.Thead>
        <Table.Tbody v-if="branchLists !== null">
          <template v-if="branchLists.data.length == 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="5">
                <div class="flex justify-center italic">{{
                  t('components.data-list.data_not_found') }}</div>
              </Table.Td>
            </Table.Tr>
          </template>
          <template v-for="( item, itemIdx ) in branchLists.data" :key="item.ulid">
            <Table.Tr class="intro-x">

            </Table.Tr>
            <Table.Tr :class="{ 'intro-x': true, 'hidden transition-all': expandDetail !== itemIdx }">
              <Table.Td colspan="5">

              </Table.Td>
            </Table.Tr>
          </template>
        </Table.Tbody>
      </Table>
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
            <Button type="button" variant="outline-secondary" class="w-24 mr-1"
              @click="() => { deleteModalShow = false; }">
              {{ t('components.buttons.cancel') }}
            </Button>
            <Button type="button" variant="danger" class="w-24" @click="(confirmDelete)">
              {{ t('components.buttons.delete') }}
            </Button>
          </div>
        </Dialog.Panel>
      </Dialog>
    </template>
  </DataList>
</template>
