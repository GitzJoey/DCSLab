<script setup lang="ts">
//#region Imports
import { onMounted, ref } from "vue";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
import DataList from "../../base-components/DataList";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import Table from "../../base-components/Table";
import { TitleLayout, TwoColumnsLayout } from "../../base-components/Form/FormLayout";
import { ViewMode } from "../../types/enums/ViewMode";
import EmployeeService from "../../services/EmployeeService";
import { Employee } from "../../types/models/Employee";
import { Collection } from "../../types/resources/Collection";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { Dialog } from "../../base-components/Headless";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { DropDownOption } from "../../types/services/DropDownOption";
import { FormRequest } from "../../types/requests/FormRequest";
import DashboardService from "../../services/DashboardService";
import CompanyService from "../../services/CompanyService";
//#endregion

//#region Declarations
const { t } = useI18n();
const employeeServices = new EmployeeService();
const companyServices = new CompanyService();
const dashboardServices = new DashboardService();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const datalistErrors = ref<Record<string, string[]> | null>(null);
const cards: Array<TwoColumnsLayoutCards> = [];
const deleteId = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
const employeeForm = ref<FormRequest<Employee>>({
  data: {
    id: '',
    ulid: '',
    company: [],
    user: [],
    employee_accesses: [],
    selected_companies: '',
    selected_accesses: '',
    code: '',
    join_date: '',
    status: 'ACTIVE',
  }
});
const employeeLists = ref<Collection<Employee[]> | null>({
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
const companyDDL = ref<Array<DropDownOption> | null>(null);
const statusDDL = ref<Array<DropDownOption> | null>(null);
//#endregion

//#region onMounted
onMounted(async () => {
  await getEmployees('', true, true, 1, 10);
  await getDDL();
});

//#endregion

//#region Computed
//#endregion

//#region Methods
const toggleDetail = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const getEmployees = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {
  let result: ServiceResponse<Collection<Employee[]> | Resource<Employee[]> | null> = await employeeServices.readAny(
    search,
    refresh,
    paginate,
    page,
    per_page
  );

  if (result.success && result.data) {
    employeeLists.value = result.data as Collection<Employee[]>;
  } else {
    datalistErrors.value = result.errors as Record<string, string[]>;
  }
}

const getDDL = async () => {
  companyDDL.value = await companyServices.getCompanyDDL();
  statusDDL.value = await dashboardServices.getStatusDDL();
}

const onDataListChanged = (data: DataListEmittedData) => {
  getEmployees(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const editSelected = (itemIdx: number) => {
  console.log(itemIdx);
}

const deleteSelected = (itemUlid: string) => {
  deleteId.value = itemUlid;
  deleteModalShow.value = true;
}

const onSubmit = async () => {
  loading.value = true;

  loading.value = false;
};
//#endregion

//#region Watcher
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <TitleLayout>
        <template #title>{{ t("views.employee.page_title") }}</template>
        <template #optional>
          <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
            <Button v-if="mode == ViewMode.LIST" as="a" href="#" variant="primary" class="shadow-md">
              <Lucide icon="Plus" class="w-4 h-4" />&nbsp;{{
                t("components.buttons.create_new")
              }}
            </Button>
          </div>
        </template>
      </TitleLayout>

      <div v-if="mode == ViewMode.LIST">
        <AlertPlaceholder :errors="datalistErrors" />
        <DataList :title="t('views.employee.table.title')" :enable-search="true" :can-print="true" :can-export="true"
          :pagination="employeeLists ? employeeLists.meta : null" @dataListChanged="onDataListChanged">
          <template #content>
            <Table class="mt-5" :hover="true">
              <Table.Thead variant="light">
                <Table.Tr>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.employee.table.cols.code") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.employee.table.cols.join_date") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.employee.table.cols.status") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap"></Table.Th>
                </Table.Tr>
              </Table.Thead>
              <Table.Tbody v-if="employeeLists !== null">
                <template v-if="employeeLists.data.length == 0">
                  <Table.Tr class="intro-x">
                    <Table.Td colspan="5">
                      <div class="flex justify-center italic">{{ t('components.data-list.data_not_found') }}</div>
                    </Table.Td>
                  </Table.Tr>
                </template>
                <template v-for="( item, itemIdx ) in employeeLists.data" :key="item.ulid">
                  <Table.Tr class="intro-x">
                    <Table.Td>{{ item.code }}</Table.Td>
                    <Table.Td>{{ item.join_date }}</Table.Td>
                    <Table.Td>
                      <Lucide v-if="item.status === 'ACTIVE'" icon="CheckCircle" />
                      <Lucide v-if="item.status === 'INACTIVE'" icon="X" />
                    </Table.Td>
                    <Table.Td>
                      <div class="flex justify-end gap-1">
                        <Button variant="outline-secondary" @click="toggleDetail(itemIdx)">
                          <Lucide icon="Info" class="w-4 h-4" />
                        </Button>
                        <Button variant="outline-secondary" @click="editSelected(itemIdx)">
                          <Lucide icon="CheckSquare" class="w-4 h-4" />
                        </Button>
                        <Button variant="outline-secondary" disabled @click="deleteSelected(item.ulid)">
                          <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                        </Button>
                      </div>
                    </Table.Td>
                  </Table.Tr>
                  <Table.Tr :class="{ 'intro-x': true, 'hidden transition-all': expandDetail !== itemIdx }">
                    <Table.Td colspan="5">
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.code') }}</div>
                        <div class="flex-1">{{ item.code }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.join_date') }}</div>
                        <div class="flex-1">{{ item.join_date }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.status') }}</div>
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
                  <Button type="button" variant="danger" class="w-24">
                    {{ t('components.buttons.delete') }}
                  </Button>
                </div>
              </Dialog.Panel>
            </Dialog>
          </template>
        </DataList>
      </div>
      <div v-else>
        <VeeForm id="employeeForm" v-slot="{ errors }" @submit="onSubmit">
          <AlertPlaceholder :errors="errors" />
          <TwoColumnsLayout :cards="cards">

          </TwoColumnsLayout>
        </VeeForm>
      </div>
    </LoadingOverlay>
  </div>
</template>
