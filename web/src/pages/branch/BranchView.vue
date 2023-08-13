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
import { FormInput, FormInputCode, FormLabel, FormTextarea, FormSelect, FormSwitch } from "../../base-components/Form";
import { ViewMode } from "../../types/enums/ViewMode";
import BranchService from "../../services/BranchService";
import { Branch } from "../../types/models/Branch";
import { BranchFormFieldValues } from "../../types/requests/BranchFormFieldValues";
import { Collection } from "../../types/resources/Collection";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { Dialog } from "../../base-components/Headless";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { DropDownOption } from "../../types/models/DropDownOption";
import { BranchFormRequest } from "../../types/requests/BranchFormRequest";
import DashboardService from "../../services/DashboardService";
import CacheService from "../../services/CacheService";
import { debounce } from "lodash";
import { CardState } from "../../types/enums/CardState";
import { SearchRequest } from "../../types/requests/SearchRequest";
import { FormActions } from "vee-validate";
import { useSelectedUserLocationStore } from "../../stores/user-location";
//#endregion

//#region Interfaces
//#endregion

//#region Declarations
const { t } = useI18n();
const cacheServices = new CacheService();
const dashboardServices = new DashboardService();
const selectedUserStore = useSelectedUserLocationStore();
const userLocation = computed(() => selectedUserStore.selectedUserLocation);
const branchServices = new BranchService();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const datalistErrors = ref<Record<string, string> | null>(null);
const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'Branch Information', state: CardState.Expanded, },
  { title: '', state: CardState.Hidden, id: 'button' }
]);
const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
const branchForm = ref<BranchFormRequest>({
  data: {
    id: '',
    ulid: '',
    company: {
      id: '',
      ulid: '',
      code: '',
      name: '',
      address: '',
      default: false,
      status: ''
    },
    code: '',
    name: '',
    address: '',
    city: '',
    contact: '',
    is_main: false,
    remarks: '',
    status: 'ACTIVE',
  }
});

const branchLists = ref<Collection<Branch[]> | null>({
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
  await getBranches('', true, true, 1, 10);

  getDDL();
});
//#endregion

//#region Methods
const getBranches = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {
  loading.value = true;

  let company_id = userLocation.value.company.id;

  const searchReq: SearchRequest = {
    search: search,
    refresh: refresh,
    paginate: paginate,
    page: page,
    per_page: per_page
  };

  let result: ServiceResponse<Collection<Array<Branch>> | Resource<Array<Branch>> | null> = await branchServices.readAny(company_id, searchReq);

  if (result.success && result.data) {
    branchLists.value = result.data as Collection<Branch[]>;
  } else {
    datalistErrors.value = result.errors as Record<string, string>;
  }

  loading.value = false;
}

const getDDL = (): void => {
  dashboardServices.getStatusDDL().then((result: Array<DropDownOption> | null) => {
    statusDDL.value = result;
  });
}

const emptyBranch = () => {
  return {
    data: {
      id: '',
      ulid: '',
      company: {
        id: '',
        ulid: '',
        code: '',
        name: '',
        address: '',
        default: false,
        status: ''
      },
      code: '_AUTO_',
      name: '',
      address: '',
      city: '',
      contact: '',
      is_main: false,
      remarks: '',
      status: 'ACTIVE',
    }
  }
}

const onDataListChanged = (data: DataListEmittedData) => {
  getBranches(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const createNew = () => {
  mode.value = ViewMode.FORM_CREATE;

  let cachedData: unknown | null = cacheServices.getLastEntity('Branch');

  branchForm.value = cachedData == null ? emptyBranch() : cachedData as BranchFormRequest;
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
  branchForm.value.data = branchLists.value?.data[itemIdx] as Branch;
}

const deleteSelected = (itemUlid: string) => {
  deleteUlid.value = itemUlid;
  deleteModalShow.value = true;
}

const confirmDelete = async () => {
  deleteModalShow.value = false;
  loading.value = true;

  let result: ServiceResponse<boolean | null> = await branchServices.delete(deleteUlid.value);

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

const onSubmit = async (values: BranchFormFieldValues, actions: FormActions<BranchFormFieldValues>) => {
  loading.value = true;

  let result: ServiceResponse<Branch | null> = {
    success: false,
  }

  if (mode.value == ViewMode.FORM_CREATE) {
    result = await branchServices.create(values);
  } else if (mode.value == ViewMode.FORM_EDIT) {
    let branch_ulid = branchForm.value.data.ulid;

    result = await branchServices.update(branch_ulid, values);
  } else {
    result.success = false;
  }

  if (!result.success) {
    actions.setErrors({ code: 'error' });
  } else {
    backToList();
  }

  loading.value = false;
};

const backToList = async () => {
  loading.value = true;

  cacheServices.removeLastEntity('Branch');

  mode.value = ViewMode.LIST;
  await getBranches('', true, true, 1, 10);

  loading.value = false;
}
//#endregion

//#region Computed
const titleView = computed(() => {
  switch (mode.value) {
    case ViewMode.FORM_CREATE:
      return t('views.branch.actions.create');
    case ViewMode.FORM_EDIT:
      return t('views.branch.actions.edit');
    case ViewMode.LIST:
    default:
      return t('views.branch.page_title');
  }
});
//#endregion

//#region Watcher
watch(
  branchForm,
  debounce((newValue): void => {
    if (mode.value != ViewMode.FORM_CREATE) return;
    cacheServices.setLastEntity('Branch', newValue)
  }, 500),
  { deep: true }
);

watch(
  userLocation,
  async () => {
    await getBranches('', true, true, 1, 10);
  },
  { deep: true }
)
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
        <AlertPlaceholder :errors="datalistErrors" :title="t('views.branch.table.title')" />
        <DataList :title="t('views.branch.table.title')" :enable-search="true" :can-print="true" :can-export="true"
          :pagination="branchLists ? branchLists.meta : null" @dataListChanged="onDataListChanged">
          <template #content>
            <Table class="mt-5" :hover="true">
              <Table.Thead variant="light">
                <Table.Tr>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.branch.table.cols.code") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.branch.table.cols.name") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.branch.table.cols.is_main") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.branch.table.cols.status") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap"></Table.Th>
                </Table.Tr>
              </Table.Thead>
              <Table.Tbody v-if="branchLists !== null">
                <template v-if="branchLists.data.length == 0">
                  <Table.Tr class="intro-x">
                    <Table.Td colspan="5">
                      <div class="flex justify-center italic">{{ t('components.data-list.data_not_found') }}</div>
                    </Table.Td>
                  </Table.Tr>
                </template>
                <template v-for="( item, itemIdx ) in branchLists.data" :key="item.ulid">
                  <Table.Tr class="intro-x">
                    <Table.Td>{{ item.code }}</Table.Td>
                    <Table.Td>{{ item.name }}</Table.Td>
                    <Table.Td>
                      <Lucide v-if="item.is_main === true" icon="CheckCircle" />
                      <Lucide v-if="item.is_main === false" icon="X" />
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
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.code') }}</div>
                        <div class="flex-1">{{ item.code }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.name') }}</div>
                        <div class="flex-1">{{ item.name }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.address') }}</div>
                        <div class="flex-1">{{ item.address }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.city') }}</div>
                        <div class="flex-1">{{ item.city }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.contact') }}</div>
                        <div class="flex-1">{{ item.contact }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.is_main') }}</div>
                        <div class="flex-1">
                          <span v-if="item.is_main">{{ t('components.dropdown.values.switch.on') }}</span>
                          <span v-else>{{ t('components.dropdown.values.switch.off') }}</span>
                        </div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.remarks') }}</div>
                        <div class="flex-1">{{ item.remarks }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.status') }}</div>
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
      </div>
      <div v-else>
        <VeeForm id="branchForm" v-slot="{ errors, handleReset }" @submit="onSubmit">
          <AlertPlaceholder :errors="errors" />
          <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
              <div class="p-5">
                <VeeField v-slot="{ field }" v-model="userLocation.company.id" name="company_id" rules="required"
                  :label="t('views.branch.fields.company_id')">
                  <FormInput id="company_id" name="company_id" type="hidden" v-bind="field" />
                </VeeField>
                <div class="pb-4">
                  <FormLabel html-for="code" :class="{ 'text-danger': errors['code'] }">
                    {{ t('views.branch.fields.code') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="branchForm.data.code" name="code" rules="required|alpha_dash"
                    :label="t('views.branch.fields.code')">
                    <FormInputCode id="code" v-bind="field" name="code" type="text"
                      :class="{ 'border-danger': errors['code'] }" :placeholder="t('views.branch.fields.code')" />
                  </VeeField>
                  <VeeErrorMessage name="code" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="name" :class="{ 'text-danger': errors['name'] }">
                    {{ t('views.branch.fields.name') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="branchForm.data.name" name="name" rules="required"
                    :label="t('views.branch.fields.name')">
                    <FormInput id="name" v-bind="field" name="name" type="text"
                      :class="{ 'border-danger': errors['name'] }" :placeholder="t('views.branch.fields.name')" />
                  </VeeField>
                  <VeeErrorMessage name="name" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="address" :class="{ 'text-danger': errors['address'] }">
                    {{ t('views.branch.fields.address') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="branchForm.data.address" name="address"
                    :label="t('views.branch.fields.address')">
                    <FormTextarea id="address" v-bind="field" name="address" type="text"
                      :class="{ 'border-danger': errors['address'] }" :placeholder="t('views.branch.fields.address')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="city" :class="{ 'text-danger': errors['city'] }">
                    {{ t('views.branch.fields.city') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="branchForm.data.city" name="city"
                    :label="t('views.branch.fields.city')">
                    <FormInput id="city" v-bind="field" name="city" type="text"
                      :class="{ 'border-danger': errors['city'] }" :placeholder="t('views.branch.fields.city')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="contact" :class="{ 'text-danger': errors['contact'] }">
                    {{ t('views.branch.fields.contact') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="branchForm.data.contact" name="contact"
                    :label="t('views.branch.fields.contact')">
                    <FormInput id="contact" v-bind="field" name="contact" type="text"
                      :class="{ 'border-danger': errors['contact'] }" :placeholder="t('views.branch.fields.contact')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="is_main" :class="{ 'text-danger': errors['is_main'] }" class="pr-5">
                    {{ t('views.branch.fields.is_main') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="branchForm.data.is_main" name="is_main"
                    :label="t('views.branch.fields.is_main')">
                    <FormSwitch.Input id="is_main" v-bind="field" name="is_main" type="checkbox"
                      :class="{ 'border-danger': errors['is_main'] }" :placeholder="t('views.branch.fields.is_main')" />
                  </VeeField>
                  <VeeErrorMessage name="is_main" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="remarks" :class="{ 'text-danger': errors['remarks'] }">
                    {{ t('views.branch.fields.remarks') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="branchForm.data.remarks" name="remarks"
                    :label="t('views.branch.fields.remarks')">
                    <FormTextarea id="remarks" v-bind="field" name="remarks" type="text"
                      :class="{ 'border-danger': errors['remarks'] }" :placeholder="t('views.branch.fields.remarks')"
                      rows="3" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="status" :class="{ 'text-danger': errors['status'] }">
                    {{ t('views.branch.fields.status') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="branchForm.data.status" name="status" rules="required"
                    :label="t('views.branch.fields.status')">
                    <FormSelect id="status" v-bind="field" name="status" :class="{ 'border-danger': errors['status'] }">
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
                <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md" @click="handleReset">
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
