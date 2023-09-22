<script setup lang="ts">
// #region Imports
import { onMounted, ref } from "vue";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
import DataList from "../../base-components/DataList";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import Table from "../../base-components/Table";
import CompanyService from "../../services/CompanyService";
import { Company } from "../../types/models/Company";
import { Collection } from "../../types/resources/Collection";
import { Role } from "../../types/models/Role";
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
const companyServices = new CompanyService();
// #endregion

// #region Props, Emits
const emit = defineEmits(['title-view', 'loading-state']);
// #endregion

// #region Refs
const datalistErrors = ref<Record<string, Array<string>> | null>(null);
const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
const companyLists = ref<Collection<Array<Company>> | null>({
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
  await getCompanies('', true, true, 1, 10);
});
// #endregion

// #region Methods
const getCompanies = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {
  emit('loading-state', true);

  const searchReq: SearchFormFieldValues = {
    search: search,
    refresh: refresh,
    paginate: paginate,
    page: page,
    per_page: per_page
  };

  let result: ServiceResponse<Collection<Array<Company>> | Resource<Array<Company>> | null> = await companyServices.readAny(searchReq);

  if (result.success && result.data) {
    companyLists.value = result.data as Collection<Array<Company>>;
  } else {
    datalistErrors.value = result.errors as Record<string, Array<string>>;
  }

  emit('loading-state', false);
}

const onDataListChanged = async (data: DataListEmittedData) => {
  await getCompanies(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (itemIdx: number) => {
  if (!companyLists.value) return;

  let ulid = companyLists.value.data[itemIdx].ulid;
  router.push({ name: 'side-menu-company-company-edit', params: { ulid: ulid } });
}

const deleteSelected = (itemIdx: number) => {
  if (!companyLists.value) return;

  let itemUlid = companyLists.value.data[itemIdx].ulid;

  deleteUlid.value = itemUlid;
  deleteModalShow.value = true;
}

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emit('loading-state', true);

  let result: ServiceResponse<boolean | null> = await companyServices.delete(deleteUlid.value);

  if (result.success) {
    await getCompanies('', true, true, 1, 10);
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
    :pagination="companyLists ? companyLists.meta : null" @dataListChanged="onDataListChanged">
    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.company.table.cols.code") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.company.table.cols.name") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.company.table.cols.default") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.company.table.cols.status") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap"></Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody v-if="companyLists !== null">
          <template v-if="companyLists.data.length == 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="5">
                <div class="flex justify-center italic">{{
                  t('components.data-list.data_not_found') }}</div>
              </Table.Td>
            </Table.Tr>
          </template>
          <template v-for="( item, itemIdx ) in companyLists.data" :key="item.ulid">
            <Table.Tr class="intro-x">
              <Table.Td>{{ item.code }}</Table.Td>
              <Table.Td>{{ item.name }}</Table.Td>
              <Table.Td>
                <Lucide v-if="item.default === true" icon="CheckCircle" />
                <Lucide v-if="item.default === false" icon="X" />
              </Table.Td>
              <Table.Td>
                <Lucide v-if="item.status === 'ACTIVE'" icon="CheckCircle" />
                <Lucide v-if="item.status === 'INACTIVE'" icon="X" />
              </Table.Td>
              <Table.Td>
                <div class="flex justify-end gap-1">
                  <Button variant="outline-secondary" @click="viewSelected(itemIdx)">
                    <Lucide icon="Info" class="w-4 h-4" />
                  </Button>
                  <Button variant="outline-secondary" @click="editSelected(itemIdx)">
                    <Lucide icon="CheckSquare" class="w-4 h-4" />
                  </Button>
                  <Button variant="outline-secondary" @click="deleteSelected(itemIdx)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </Table.Td>
            </Table.Tr>
            <Table.Tr :class="{ 'intro-x': true, 'hidden transition-all': expandDetail !== itemIdx }">
              <Table.Td colspan="5">
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">{{ t('views.company.fields.code') }}</div>
                  <div class="flex-1">{{ item.code }}</div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">{{ t('views.company.fields.name') }}</div>
                  <div class="flex-1">{{ item.name }}</div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">{{ t('views.company.fields.address') }}</div>
                  <div class="flex-1">{{ item.address }}</div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">{{ t('views.company.fields.default') }}</div>
                  <div class="flex-1">
                    <span v-if="item.default">{{ t('components.dropdown.values.switch.on') }}</span>
                    <span v-else>{{ t('components.dropdown.values.switch.off') }}</span>
                  </div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">{{ t('views.company.fields.status') }}</div>
                  <div class="flex-1">
                    <span v-if="item.status === 'ACTIVE'">
                      {{ t('components.dropdown.values.statusDDL.active') }}
                    </span>
                    <span v-if="item.status === 'INACTIVE'">
                      {{ t('components.dropdown.values.statusDDL.inactive') }}
                    </span>
                  </div>
                </div>
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
