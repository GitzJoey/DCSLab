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
import { FormInput, FormLabel, FormSelect, FormTextarea } from "../../base-components/Form";
import { ViewMode } from "../../types/enums/ViewMode";
import UnitService from "../../services/UnitService";
import { Unit } from "../../types/models/Unit";
import { UnitFormFieldValues } from "../../types/requests/UnitFormFieldValues";
import { Collection } from "../../types/resources/Collection";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { Dialog } from "../../base-components/Headless";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { DropDownOption } from "../../types/services/DropDownOption";
import { UnitFormRequest } from "../../types/requests/UnitFormRequest";
import CacheService from "../../services/CacheService";
import { debounce } from "lodash";
import { CardState } from "../../types/enums/CardState";
import { SearchRequest } from "../../types/requests/SearchRequest";
import { LaravelError } from "../../types/errors/LaravelError";
import { VeeValidateError } from "../../types/errors/VeeValidateError";
import { FormActions } from "vee-validate";
import { useSelectedUserLocationStore } from "../../stores/user-location";
//#endregion

//#region Interfaces
//#endregion

//#region Declarations
const { t } = useI18n();
const cacheServices = new CacheService();
const selectedUserStore = useSelectedUserLocationStore();
const userLocation = computed(() => selectedUserStore.selectedUserLocation);
const unitServices = new UnitService();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const datalistErrors = ref<LaravelError | VeeValidateError | null>(null);
const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'Company Information', state: CardState.Expanded, },
  { title: 'Unit Information', state: CardState.Expanded, },
  { title: '', state: CardState.Hidden, id: 'button' }
]);
const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
const unitForm = ref<UnitFormRequest>({
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
    description: '',
    category: '',
  }
});

const unitLists = ref<Collection<Unit[]> | null>({
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
const unitCategoryDDL = ref<Array<DropDownOption> | null>(null);
//#endregion

//#region onMounted
onMounted(async () => {
  await getUnits('', true, true, 1, 10);

  getDDL();
});
//#endregion

//#region Methods
const getUnits = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {  
  loading.value = true;

  
  let company_id = userLocation.value.company.id;  

  const searchReq: SearchRequest = {
    search: search,
    refresh: refresh,
    paginate: paginate,
    page: page,
    per_page: per_page
  };

  let result: ServiceResponse<Collection<Array<Unit>> | Resource<Array<Unit>> | null> = await unitServices.readAny(company_id, searchReq);

  if (result.success && result.data) {
    unitLists.value = result.data as Collection<Unit[]>;
  } else {
    datalistErrors.value = result.errors as LaravelError;
  }

  loading.value = false;
}

const getDDL = (): void => {
  unitServices.getUnitCategoryDDL().then((result: Array<DropDownOption> | null) => {
    unitCategoryDDL.value = result;
  });
}

const selectedCompanyId = () => {
  return userLocation.value.company.id;
}

const emptyUnit = () => {
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
      code: '',
      name: '',
      description: '',
      category: '',
    }
  }
}

const onDataListChanged = (data: DataListEmittedData) => {
  getUnits(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const createNew = () => {
  mode.value = ViewMode.FORM_CREATE;

  let cachedData: unknown | null = cacheServices.getLastEntity('Unit');

  unitForm.value = cachedData == null ? emptyUnit() : cachedData as UnitFormRequest;
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
  unitForm.value.data = unitLists.value?.data[itemIdx] as Unit;
}

const deleteSelected = (itemUlid: string) => {
  deleteUlid.value = itemUlid;
  deleteModalShow.value = true;
}

const confirmDelete = async () => {
  deleteModalShow.value = false;
  loading.value = true;

  let result: ServiceResponse<boolean | null> = await unitServices.delete(deleteUlid.value);

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

const onSubmit = async (values: UnitFormFieldValues, actions: FormActions<UnitFormFieldValues>) => {
  loading.value = true;

  let result: ServiceResponse<Unit | null> = {
    success: false,
  }

  if (mode.value == ViewMode.FORM_CREATE) {
    result = await unitServices.create(values);
  } else if (mode.value == ViewMode.FORM_EDIT) {
    let unit_ulid = unitForm.value.data.ulid;

    result = await unitServices.update( unit_ulid, values);
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

  cacheServices.removeLastEntity('Unit');

  mode.value = ViewMode.LIST;
  await getUnits('', true, true, 1, 10);

  loading.value = false;
}
//#endregion

//#region Computed
const titleView = computed(() => {
  switch (mode.value) {
    case ViewMode.FORM_CREATE:
      return t('views.unit.actions.create');
    case ViewMode.FORM_EDIT:
      return t('views.unit.actions.edit');
    case ViewMode.LIST:
    default:
      return t('views.unit.page_title');
  }
});
//#endregion

//#region Watcher
watch(
  unitForm,
  debounce((newValue): void => {
    if (mode.value != ViewMode.FORM_CREATE) return;
    cacheServices.setLastEntity('Unit', newValue)
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
        <AlertPlaceholder :errors="datalistErrors" :title="t('views.unit.table.title')" />
        <DataList :title="t('views.unit.table.title')" :enable-search="true" :can-print="true" :can-export="true"
          :pagination="unitLists ? unitLists.meta : null" @dataListChanged="onDataListChanged">
          <template #content>
            <Table class="mt-5" :hover="true">
              <Table.Thead variant="light">
                <Table.Tr>
                <Table.Th class="whitespace-nowrap">
                {{ t("views.unit.table.cols.code") }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t("views.unit.table.cols.name") }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t("views.unit.table.cols.description") }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t("views.unit.table.cols.category") }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap"></Table.Th>
                </Table.Tr>
              </Table.Thead>
              <Table.Tbody v-if="unitLists !== null">
                <template v-if="unitLists.data.length == 0">
                  <Table.Tr class="intro-x">
                    <Table.Td colspan="5">
                      <div class="flex justify-center italic">{{ t('components.data-list.data_not_found') }}</div>
                    </Table.Td>
                  </Table.Tr>
                </template>
                <template v-for="( item, itemIdx ) in unitLists.data" :key="item.ulid">
                  <Table.Tr class="intro-x">
                    <Table.Td>{{ item.code }}</Table.Td>
                    <Table.Td>{{ item.name }}</Table.Td>
                    <Table.Td>{{ item.description }}</Table.Td>
                    <Table.Td>
                      <span v-if="item.category === 'PRODUCTS'">{{ t('components.dropdown.values.unitCategoryDDL.product') }}</span>
                      <span v-if="item.category === 'SERVICES'">{{ t('components.dropdown.values.unitCategoryDDL.service') }}</span>
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
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.unit.fields.code') }}</div>
                        <div class="flex-1">{{ item.code }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.unit.fields.name') }}</div>
                        <div class="flex-1">{{ item.name }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.unit.fields.description') }}</div>
                        <div class="flex-1">{{ item.description }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.unit.fields.category') }}</div>
                        <div class="flex-1">
                          <span v-if="item.category === 'PRODUCTS'">
                            {{ t('components.dropdown.values.unitCategoryDDL.product') }}
                          </span>
                          <span v-if="item.category === 'SERVICES'">
                            {{ t('components.dropdown.values.unitCategoryDDL.service') }}
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
        <VeeForm id="unitForm" v-slot="{ errors }" @submit="onSubmit">
          <AlertPlaceholder :errors="errors" />
          <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
              <div class="p-5">
                <div class="pb-4">
                  <label for="code" class="block bold font-semibold">{{ t('views.company.fields.name') }}</label>
                  <div class="flex-1">{{ userLocation.company.name }}</div>
                </div>
              </div>
            </template>
            <template #card-items-1>
              <div class="p-5">
                <div class="pb-4">
                  <VeeField v-slot="{ field }" :value=selectedCompanyId() name="company_id">
                    <FormInput id="company_id" name="company_id" type="hidden" v-bind="field" />
                  </VeeField>
                  <div class="pb-4">
                    <FormLabel html-for="code" :class="{ 'text-danger': errors['code'] }">
                      {{ t('views.unit.fields.code') }}
                    </FormLabel>
                    <VeeField v-slot="{ field }" v-model="unitForm.data.code" name="code" rules="required|alpha_dash"
                      :label="t('views.unit.fields.code')">
                      <FormInput id="code" v-bind="field" name="code" type="text"
                        :class="{ 'border-danger': errors['code'] }" :placeholder="t('views.unit.fields.code')" />
                    </VeeField>
                    <VeeErrorMessage name="code" class="mt-2 text-danger" />
                  </div>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="name" :class="{ 'text-danger': errors['name'] }">
                    {{ t('views.unit.fields.name') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="unitForm.data.name" name="name" rules="required"
                    :label="t('views.unit.fields.name')">
                    <FormInput id="name" v-bind="field" name="name" type="text"
                      :class="{ 'border-danger': errors['name'] }" :placeholder="t('views.unit.fields.name')" />
                  </VeeField>
                  <VeeErrorMessage name="name" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="description" :class="{ 'text-danger': errors['description'] }">
                    {{ t('views.unit.fields.description') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="description"
                    :label="t('views.unit.fields.description')">
                    <FormTextarea id="description" v-model="unitForm.data.description" v-bind="field" name="description" type="text"
                      :class="{ 'border-danger': errors['description'] }" :placeholder="t('views.unit.fields.description')" rows="3" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="category" :class="{ 'text-danger': errors['category'] }">
                    {{ t('views.unit.fields.category') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="category" rules="required" :label="t('views.unit.fields.category')">
                    <FormSelect id="category" v-model="unitForm.data.category" v-bind="field" name="category"
                      :class="{ 'border-danger': errors['category'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="c in unitCategoryDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="category" class="mt-2 text-danger" />
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
