<script setup lang="ts">
//#region Imports
import { onMounted, ref, watch, computed } from "vue";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
import DataList from "../../base-components/DataList";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import Table from "../../base-components/Table";
import { TitleLayout, TwoColumnsLayout } from "../../base-components/Form/FormLayout";
import { FormInput, FormLabel, FormTextarea, FormSelect, FormSwitch } from "../../base-components/Form";
import { ViewMode } from "../../types/enums/ViewMode";
import CompanyService from "../../services/CompanyService";
import { Company } from "../../types/models/Company";
import { Collection } from "../../types/resources/Collection";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { Dialog } from "../../base-components/Headless";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { DropDownOption } from "../../types/services/DropDownOption";
import { FormRequest } from "../../types/requests/FormRequest";
import DashboardService from "../../services/DashboardService";
import CacheService from "../../services/CacheService";
import { debounce, values } from "lodash";
import { CardState } from "../../types/enums/CardState";
import { SearchRequest } from "../../types/requests/SearchRequest";
import { LaravelError } from "../../types/errors/LaravelError";
import { VeeValidateError } from "../../types/errors/VeeValidateError";
import { FormActions } from "vee-validate";
//#endregion

//#region Declarations
const { t } = useI18n();
const cacheServices = new CacheService();
const dashboardServices = new DashboardService();
const companyServices = new CompanyService();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const datalistErrors = ref<LaravelError | VeeValidateError | null>(null);
const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'Company Information', state: CardState.Expanded, },
  { title: '', state: CardState.Hidden, id: 'button' }
]);
const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
const companyForm = ref<FormRequest<Company>>({
  data: {
    id: '',
    ulid: '',
    code: '',
    name: '',
    address: '',
    default: false,
    status: 'ACTIVE',
    branches: [],
  }
});

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

const statusDDL = ref<Array<DropDownOption> | null>(null);
//#endregion

//#region onMounted
onMounted(async () => {
  await getCompanies('', true, true, 1, 10);

  getDDL();
});
//#endregion

//#region Methods
const getCompanies = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {
  loading.value = true;

  const searchReq: SearchRequest = {
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
    datalistErrors.value = result.errors as LaravelError;
  }

  loading.value = false;
}

const getDDL = async (): Promise<void> => {
  dashboardServices.getStatusDDL().then((result: Array<DropDownOption> | null) => {
    statusDDL.value = result;
  });
}

const emptyCompany = () => {
  return {
    data: {
      id: '',
      ulid: '',
      code: '',
      name: '',
      address: '',
      default: true,
      status: 'ACTIVE',
      branches: [],
    }
  }
}

const onDataListChanged = (data: DataListEmittedData) => {
  getCompanies(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const createNew = () => {
  mode.value = ViewMode.FORM_CREATE;

  let cachedData: unknown | null = cacheServices.getLastEntity('Company');

  companyForm.value = cachedData == null ? emptyCompany() : cachedData as FormRequest<Company>;
}

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (itemIdx: number) => {
  mode.value = ViewMode.FORM_EDIT;
  companyForm.value.data = companyLists.value?.data[itemIdx] as Company;
}

const deleteSelected = (itemUlid: string) => {
  deleteUlid.value = itemUlid;
  deleteModalShow.value = true;
}

const confirmDelete = async () => {
  deleteModalShow.value = false;
  loading.value = true;

  let result: ServiceResponse<boolean | null> = await companyServices.delete(deleteUlid.value);

  if (result.success) {
    backToList();
  } else {
    console.log(result);
  }

  loading.value = true;
}

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed
  }
}

const onSubmit = async (values: FormRequest<Company>, actions: FormActions<FormRequest<Company>>) => {
  loading.value = true;

  let result: ServiceResponse<Company | null> = {
    success: false,
  }

  if (mode.value == ViewMode.FORM_CREATE) {
    result = await companyServices.create({data: values});
  } else if (mode.value == ViewMode.FORM_EDIT) {
    result = await companyServices.update(companyForm.value.data.ulid, {data: values});
  } else {
    result.success = false;
  }

  if (!result.success) {
    actions.setErrors({ data: 'error' });
  } else {
    backToList();
  }

  loading.value = false;
};

const backToList = async () => {
  loading.value = true;

  cacheServices.removeLastEntity('Company');

  mode.value = ViewMode.LIST;
  await getCompanies('', true, true, 1, 10);

  loading.value = false;
}
//#endregion

//#region Computed
const titleView = computed(() => {
  switch (mode.value) {
    case ViewMode.FORM_CREATE:
      return t('views.company.actions.create');
    case ViewMode.FORM_EDIT:
      return t('views.company.actions.edit');
    case ViewMode.LIST:
    default:
      return t('views.company.page_title');
  }
});
//#endregion

//#region Watcher
watch(
  companyForm,
  debounce((newValue): void => {
    if (mode.value != ViewMode.FORM_CREATE) return;
    cacheServices.setLastEntity('Company', newValue)
  }, 500),
  { deep: true }
);
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <TitleLayout>
        <template #title>
          {{ titleView }}
        </template>
        <template #optional>
          <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
            <Button v-if="mode == ViewMode.LIST" as="a" href="#" variant="primary" class="shadow-md" @click="createNew">
              <Lucide icon="Plus" class="w-4 h-4" />&nbsp;{{
                t("components.buttons.create_new")
              }}
            </Button>
          </div>
        </template>
      </TitleLayout>

      <div v-if="mode == ViewMode.LIST">
        <AlertPlaceholder :errors="datalistErrors" :title="t('views.company.table.title')" />
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
                      <div class="flex justify-center italic">{{ t('components.data-list.data_not_found') }}</div>
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
                        <Button variant="outline-secondary" @click="deleteSelected(item.ulid)">
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
                  <Button type="button" variant="outline-secondary" class="w-24 mr-1" @click="() => { deleteModalShow = false; }">
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
      </div>
      <div v-else>
        <VeeForm id="companyForm" v-slot="{ errors }" @submit="onSubmit">
          <AlertPlaceholder :errors="errors"/>
          <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
              <div class="p-5">
                <div class="pb-4">
                  <FormLabel html-for="code" :class="{ 'text-danger': errors['code'] }">
                    {{ t('views.company.fields.code') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="code" rules="required" :label="t('views.company.fields.code')">
                    <FormInput id="code" v-model="companyForm.data.code" v-bind="field" name="code" type="text"
                      :class="{ 'border-danger': errors['code'] }" :placeholder="t('views.company.fields.code')" />
                  </VeeField>
                  <VeeErrorMessage name="code" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="name" :class="{ 'text-danger': errors['name'] }">
                    {{ t('views.company.fields.name') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="name" rules="required|alpha_num"
                    :label="t('views.company.fields.name')">
                    <FormInput id="name" v-model="companyForm.data.name" v-bind="field" name="name" type="text"
                      :class="{ 'border-danger': errors['name'] }" :placeholder="t('views.company.fields.name')" />
                  </VeeField>
                  <VeeErrorMessage name="name" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="address" :class="{ 'text-danger': errors['address'] }">
                    {{ t('views.company.fields.address') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="address" :label="t('views.company.fields.address')">
                    <FormTextarea id="address" v-model="companyForm.data.address" v-bind="field" name="address"
                      type="text" :placeholder="t('views.company.fields.address')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="default" :class="{ 'text-danger': errors['default']}" class="pr-5">
                    {{ t('views.company.fields.default') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="default" :label="t('views.company.fields.default')">
                    <FormSwitch.Input id="default" v-model="companyForm.data.default" v-bind="field" name="default" type="checkbox"
                      :class="{ 'border-danger': errors['default'] }" :placeholder="t('views.company.fields.default')"
                    />
                  </VeeField>                 
                </div>
                <div class="pb-4">
                  <FormLabel html-for="status" :class="{ 'text-danger': errors['status'] }">
                    {{ t('views.company.fields.status') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="status" rules="required" :label="t('views.company.fields.status')">
                    <FormSelect id="status" v-model="companyForm.data.status" v-bind="field" name="status"
                      :class="{ 'border-danger': errors['status'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="status" class="mt-2 text-danger" />
                </div>
              </div>
            </template>
            <template #card-items-button>
              <div class="flex gap-4">
                <Button type="submit" href="#" variant="primary" class="w-28 shadow-md">
                  {{ t("components.buttons.submit") }}
                </Button>
                <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md">
                  {{ t("components.buttons.reset") }}
                </Button>
              </div>
            </template>
          </TwoColumnsLayout>
        </VeeForm>
        <Button as="button" variant="secondary" class="mt-2 w-24" @click="backToList">
          {{ t('components.buttons.back') }}
        </Button>
      </div>
    </LoadingOverlay>
  </div>
</template>