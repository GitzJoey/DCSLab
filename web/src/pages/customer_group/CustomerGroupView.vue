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
import CustomerGroupService from "../../services/CustomerGroupService";
import { CustomerGroup } from "../../types/models/CustomerGroup";
import { Collection } from "../../types/resources/Collection";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { Dialog } from "../../base-components/Headless";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { DropDownOption } from "../../types/services/DropDownOption";
import { CustomerGroupFormRequest } from "../../types/requests/CustomerGroupFormRequest";
import DashboardService from "../../services/DashboardService";
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
interface CustomerGroupFormFieldValues {
  code: string,
  name: string,
  address: string,
  city: string,
  contact: string,
  is_main: boolean,
  remarks: string,
  status: string
}
//#endregion

//#region Declarations
const { t } = useI18n();
const cacheServices = new CacheService();
const dashboardServices = new DashboardService();
const selectedUserStore = useSelectedUserLocationStore();
const userLocation = computed(() => selectedUserStore.selectedUserLocation);
const customerGroupServices = new CustomerGroupService();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const datalistErrors = ref<LaravelError | VeeValidateError | null>(null);
const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'CustomerGroup Information', state: CardState.Expanded, },
  { title: '', state: CardState.Hidden, id: 'button' }
]);
const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
const customerGroupForm = ref<CustomerGroupFormRequest>({
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
    max_open_invoice: 0,
    max_outstanding_invoice: 0,
    max_invoice_age: 0,
    payment_term_type: '',
    payment_term: '',
    selling_point: 0,
    selling_point_multiple: 0,
    sell_at_cost: false,
    price_markup_percent: 0,
    price_markup_nominal: 0,
    price_markdown_percent: 0,
    price_markdown_nominal: 0,
    round_on: '',
    round_digit: 0,
    remarks: '',
  }
});

const customerGroupLists = ref<Collection<CustomerGroup[]> | null>({
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
  await getCustomerGroups('', true, true, 1, 10);

  getDDL();
});
//#endregion

//#region Methods
const getCustomerGroups = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {  
  loading.value = true;

  let company_id = userLocation.value.company.id;  

  const searchReq: SearchRequest = {
    search: search,
    refresh: refresh,
    paginate: paginate,
    page: page,
    per_page: per_page
  };

  let result: ServiceResponse<Collection<Array<CustomerGroup>> | Resource<Array<CustomerGroup>> | null> = await customerGroupServices.readAny(company_id, searchReq);

  if (result.success && result.data) {
    customerGroupLists.value = result.data as Collection<CustomerGroup[]>;
  } else {
    datalistErrors.value = result.errors as LaravelError;
  }

  loading.value = false;
}

const getDDL = (): void => {
  dashboardServices.getStatusDDL().then((result: Array<DropDownOption> | null) => {
    statusDDL.value = result;
  });
}

const emptyCustomerGroup = () => {
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
      max_open_invoice: 0,
      max_outstanding_invoice: 0,
      max_invoice_age: 0,
      payment_term_type: '',
      payment_term: '',
      selling_point: 0,
      selling_point_multiple: 0,
      sell_at_cost: false,
      price_markup_percent: 0,
      price_markup_nominal: 0,
      price_markdown_percent: 0,
      price_markdown_nominal: 0,
      round_on: '',
      round_digit: 0,
      remarks: '',
    }
  }
}

const onDataListChanged = (data: DataListEmittedData) => {
  getCustomerGroups(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const createNew = () => {
  mode.value = ViewMode.FORM_CREATE;

  let cachedData: unknown | null = cacheServices.getLastEntity('CustomerGroup');

  customerGroupForm.value = cachedData == null ? emptyCustomerGroup() : cachedData as CustomerGroupFormRequest;
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
  customerGroupForm.value.data = customerGroupLists.value?.data[itemIdx] as CustomerGroup;
}

const deleteSelected = (itemUlid: string) => {
  deleteUlid.value = itemUlid;
  deleteModalShow.value = true;
}

const confirmDelete = async () => {
  deleteModalShow.value = false;
  loading.value = true;

  let result: ServiceResponse<boolean | null> = await customerGroupServices.delete(deleteUlid.value);

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

const onSubmit = async (values: CustomerGroupFormFieldValues, actions: FormActions<CustomerGroupFormFieldValues>) => {
  loading.value = true;

  let result: ServiceResponse<CustomerGroup | null> = {
    success: false,
  }

  if (mode.value == ViewMode.FORM_CREATE) {
    let company_id = userLocation.value.company.id;
    
    result = await customerGroupServices.create(
      company_id, 
      { data:values }
    );
  } else if (mode.value == ViewMode.FORM_EDIT) {
    let customerGroup_ulid = customerGroupForm.value.data.ulid;
    let company_id = userLocation.value.company.id;

    result = await customerGroupServices.update(
      customerGroup_ulid, 
      company_id, 
      { data:values }
    );
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

  cacheServices.removeLastEntity('CustomerGroup');

  mode.value = ViewMode.LIST;
  await getCustomerGroups('', true, true, 1, 10);

  loading.value = false;
}
//#endregion

//#region Computed
const titleView = computed(() => {
  switch (mode.value) {
    case ViewMode.FORM_CREATE:
      return t('views.customer_group.actions.create');
    case ViewMode.FORM_EDIT:
      return t('views.customer_group.actions.edit');
    case ViewMode.LIST:
    default:
      return t('views.customer_group.page_title');
  }
});
//#endregion

//#region Watcher
watch(
  customerGroupForm,
  debounce((newValue): void => {
    if (mode.value != ViewMode.FORM_CREATE) return;
    cacheServices.setLastEntity('CustomerGroup', newValue)
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
        <AlertPlaceholder :errors="datalistErrors" :title="t('views.customer_group.table.title')" />
        <DataList :title="t('views.customer_group.table.title')" :enable-search="true" :can-print="true" :can-export="true"
          :pagination="customerGroupLists ? customerGroupLists.meta : null" @dataListChanged="onDataListChanged">
          <template #content>
            <Table class="mt-5" :hover="true">
              <Table.Thead variant="light">
                <Table.Tr>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.customer_group.table.cols.code") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.customer_group.table.cols.name") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.customer_group.table.cols.payment_term_type") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.customer_group.table.cols.remarks") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap"></Table.Th>
                </Table.Tr>
              </Table.Thead>
              <Table.Tbody v-if="customerGroupLists !== null">
                <template v-if="customerGroupLists.data.length == 0">
                  <Table.Tr class="intro-x">
                    <Table.Td colspan="5">
                      <div class="flex justify-center italic">{{ t('components.data-list.data_not_found') }}</div>
                    </Table.Td>
                  </Table.Tr>
                </template>
                <template v-for="( item, itemIdx ) in customerGroupLists.data" :key="item.ulid">
                  <Table.Tr class="intro-x">
                    <Table.Td>{{ item.code }}</Table.Td>
                    <Table.Td>{{ item.name }}</Table.Td>
                    <Table.Td>
                      <span v-if="item.payment_term_type === 'PAYMENT_IN_ADVANCE'">{{ t('components.dropdown.values.paymentTermTypeDDL.pia') }}</span>
                      <span v-if="item.payment_term_type === 'X_DAYS_AFTER_INVOICE'">{{ t('components.dropdown.values.paymentTermTypeDDL.net') }}</span>
                      <span v-if="item.payment_term_type === 'END_OF_MONTH'">{{ t('components.dropdown.values.paymentTermTypeDDL.eom') }}</span>
                      <span v-if="item.payment_term_type === 'CASH_ON_DELIVERY'">{{ t('components.dropdown.values.paymentTermTypeDDL.cod') }}</span>
                      <span v-if="item.payment_term_type === 'CASH_ON_NEXT_DELIVERY'">{{ t('components.dropdown.values.paymentTermTypeDDL.cnd') }}</span>
                      <span v-if="item.payment_term_type === 'CASH_BEFORE_SHIPMENT'">{{ t('components.dropdown.values.paymentTermTypeDDL.cbs') }}</span>
                    </Table.Td>
                    <Table.Td>{{ item.remarks }}</Table.Td>
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
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.code') }}</div>
                        <div class="flex-1">{{ item.code }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.name') }}</div>
                        <div class="flex-1">{{ item.name }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.max_open_invoice') }}</div>
                        <div class="flex-1">{{ item.max_open_invoice }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.max_outstanding_invoice') }}</div>
                        <div class="flex-1">{{ item.max_outstanding_invoice }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.max_invoice_age') }}</div>
                        <div class="flex-1">{{ item.max_invoice_age }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.payment_term_type') }}</div>
                        <div class="flex-1">
                          <span v-if="item.payment_term_type === 'PAYMENT_IN_ADVANCE'">
                            {{ t('components.dropdown.values.paymentTermTypeDDl.pia') }}
                          </span>
                          <span v-if="item.payment_term_type === 'X_DAYS_AFTER_INVOICE'">
                            {{ t('components.dropdown.values.paymentTermTypeDDl.net') }}
                          </span>
                          <span v-if="item.payment_term_type === 'END_OF_MONTH'">
                            {{ t('components.dropdown.values.paymentTermTypeDDl.eom') }}
                          </span>
                          <span v-if="item.payment_term_type === 'CASH_ON_DELIVERY'">
                            {{ t('components.dropdown.values.paymentTermTypeDDl.cod') }}
                          </span>
                          <span v-if="item.payment_term_type === 'CASH_ON_NEXT_DELIVERY'">
                            {{ t('components.dropdown.values.paymentTermTypeDDl.cnd') }}
                          </span>
                          <span v-if="item.payment_term_type === 'CASH_BEFORE_SHIPMENT'">
                            {{ t('components.dropdown.values.paymentTermTypeDDl.cbs') }}
                          </span>
                        </div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.payment_term') }}</div>
                        <div class="flex-1">{{ item.payment_term }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.selling_point') }}</div>
                        <div class="flex-1">{{ item.selling_point }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.selling_point_multiple') }}</div>
                        <div class="flex-1">{{ item.selling_point_multiple }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.sell_at_cost') }}</div>
                        <div class="flex-1">{{ item.sell_at_cost }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.price_markup_percent') }}</div>
                        <div class="flex-1">{{ item.price_markup_percent }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.price_markup_nominal') }}</div>
                        <div class="flex-1">{{ item.price_markup_nominal }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.price_markdown_percent') }}</div>
                        <div class="flex-1">{{ item.price_markdown_percent }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.price_markdown_nominal') }}</div>
                        <div class="flex-1">{{ item.price_markdown_nominal }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product_group.fields.category') }}</div>
                        <div class="flex-1">
                          <span v-if="item.category === 'UP'">
                            {{ t('components.dropdown.values.roundOnDDL.up') }}
                          </span>
                          <span v-if="item.category === 'DOWN'">
                            {{ t('components.dropdown.values.roundOnDDL.down') }}
                          </span>
                        </div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.round_digit') }}</div>
                        <div class="flex-1">{{ item.round_digit }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer_group.fields.remarks') }}</div>
                        <div class="flex-1">{{ item.remarks }}</div>
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
        <VeeForm id="customerGroupForm" v-slot="{ errors }" @submit="onSubmit">
          <AlertPlaceholder :errors="errors" />
          <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
              <div class="p-5">
                <div class="pb-4">
                  <FormLabel html-for="code" :class="{ 'text-danger': errors['code'] }">
                    {{ t('views.customer_group.fields.code') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="code" rules="required|alpha_num"
                    :label="t('views.customer_group.fields.code')">
                    <FormInput id="code" v-model="customerGroupForm.data.code" v-bind="field" name="code" type="text"
                      :class="{ 'border-danger': errors['code'] }" :placeholder="t('views.customer_group.fields.code')" />
                  </VeeField>
                  <VeeErrorMessage name="code" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="name" :class="{ 'text-danger': errors['name'] }">
                    {{ t('views.customer_group.fields.name') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="name" rules="required"
                    :label="t('views.customer_group.fields.name')">
                    <FormInput id="name" v-model="customerGroupForm.data.name" v-bind="field" name="name" type="text"
                      :class="{ 'border-danger': errors['name'] }" :placeholder="t('views.customer_group.fields.name')" />
                  </VeeField>
                  <VeeErrorMessage name="name" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="max_open_invoice" :class="{ 'text-danger': errors['max_open_invoice'] }">
                    {{ t('views.customer_group.fields.max_open_invoice') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="max_open_invoice"
                    :label="t('views.customer_group.fields.max_open_invoice')">
                    <FormInput id="max_open_invoice" v-model="customerGroupForm.data.max_open_invoice" v-bind="field" name="max_open_invoice" type="number"
                      :class="{ 'border-danger': errors['max_open_invoice'] }" :placeholder="t('views.customer_group.fields.max_open_invoice')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="max_outstanding_invoice" :class="{ 'text-danger': errors['max_outstanding_invoice'] }">
                    {{ t('views.customer_group.fields.max_outstanding_invoice') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="max_outstanding_invoice"
                    :label="t('views.customer_group.fields.max_outstanding_invoice')">
                    <FormInput id="max_outstanding_invoice" v-model="customerGroupForm.data.max_outstanding_invoice" v-bind="field" name="max_outstanding_invoice" type="number"
                      :class="{ 'border-danger': errors['max_outstanding_invoice'] }" :placeholder="t('views.customer_group.fields.max_outstanding_invoice')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="max_invoice_age" :class="{ 'text-danger': errors['max_invoice_age'] }">
                    {{ t('views.customer_group.fields.max_invoice_age') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="max_invoice_age"
                    :label="t('views.customer_group.fields.max_invoice_age')">
                    <FormInput id="max_invoice_age" v-model="customerGroupForm.data.max_invoice_age" v-bind="field" name="max_invoice_age" type="number"
                      :class="{ 'border-danger': errors['max_invoice_age'] }" :placeholder="t('views.customer_group.fields.max_invoice_age')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="payment_term_type" :class="{ 'text-danger': errors['payment_term_type'] }">
                    {{ t('views.product_group.fields.payment_term_type') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="payment_term_type" rules="required" :label="t('views.product_group.fields.payment_term_type')">
                    <FormSelect id="payment_term_type" v-model="productGroupForm.data.payment_term_type" v-bind="field" name="payment_term_type"
                      :class="{ 'border-danger': errors['payment_term_type'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="c in paymentTermTypeDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="payment_term_type" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="selling_point" :class="{ 'text-danger': errors['selling_point'] }">
                    {{ t('views.customer_group.fields.selling_point') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="selling_point"
                    :label="t('views.customer_group.fields.selling_point')">
                    <FormInput id="selling_point" v-model="customerGroupForm.data.selling_point" v-bind="field" name="selling_point" type="number"
                      :class="{ 'border-danger': errors['selling_point'] }" :placeholder="t('views.customer_group.fields.selling_point')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="selling_point_multiple" :class="{ 'text-danger': errors['selling_point_multiple'] }">
                    {{ t('views.customer_group.fields.selling_point_multiple') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="selling_point_multiple"
                    :label="t('views.customer_group.fields.selling_point_multiple')">
                    <FormInput id="selling_point_multiple" v-model="customerGroupForm.data.selling_point_multiple" v-bind="field" name="selling_point_multiple" type="number"
                      :class="{ 'border-danger': errors['selling_point_multiple'] }" :placeholder="t('views.customer_group.fields.selling_point_multiple')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="sell_at_cost" :class="{ 'text-danger': errors['sell_at_cost']} " class="pr-5">
                    {{ t('views.customer_group.fields.sell_at_cost') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="sell_at_cost" :label="t('views.customer_group.fields.sell_at_cost')">
                    <FormSwitch.Input id="sell_at_cost" v-model="customerGroupForm.data.sell_at_cost" v-bind="field" name="sell_at_cost" type="checkbox"
                      :class="{ 'border-danger': errors['sell_at_cost'] }" :placeholder="t('views.customer_group.fields.sell_at_cost')"
                    />
                  </VeeField>
                  <VeeErrorMessage name="sell_at_cost" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="price_markup_percent" :class="{ 'text-danger': errors['price_markup_percent'] }">
                    {{ t('views.customer_group.fields.price_markup_percent') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="price_markup_percent"
                    :label="t('views.customer_group.fields.price_markup_percent')">
                    <FormInput id="price_markup_percent" v-model="customerGroupForm.data.price_markup_percent" v-bind="field" name="price_markup_percent" type="number"
                      :class="{ 'border-danger': errors['price_markup_percent'] }" :placeholder="t('views.customer_group.fields.price_markup_percent')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="price_markup_nominal" :class="{ 'text-danger': errors['price_markup_nominal'] }">
                    {{ t('views.customer_group.fields.price_markup_nominal') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="price_markup_nominal"
                    :label="t('views.customer_group.fields.price_markup_nominal')">
                    <FormInput id="price_markup_nominal" v-model="customerGroupForm.data.price_markup_nominal" v-bind="field" name="price_markup_nominal" type="number"
                      :class="{ 'border-danger': errors['price_markup_nominal'] }" :placeholder="t('views.customer_group.fields.price_markup_nominal')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="price_markdown_percent" :class="{ 'text-danger': errors['price_markdown_percent'] }">
                    {{ t('views.customer_group.fields.price_markdown_percent') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="price_markdown_percent"
                    :label="t('views.customer_group.fields.price_markdown_percent')">
                    <FormInput id="price_markdown_percent" v-model="customerGroupForm.data.price_markdown_percent" v-bind="field" name="price_markdown_percent" type="number"
                      :class="{ 'border-danger': errors['price_markdown_percent'] }" :placeholder="t('views.customer_group.fields.price_markdown_percent')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="price_markdown_nominal" :class="{ 'text-danger': errors['price_markdown_nominal'] }">
                    {{ t('views.customer_group.fields.price_markdown_nominal') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="price_markdown_nominal"
                    :label="t('views.customer_group.fields.price_markdown_nominal')">
                    <FormInput id="price_markdown_nominal" v-model="customerGroupForm.data.price_markdown_nominal" v-bind="field" name="price_markdown_nominal" type="number"
                      :class="{ 'border-danger': errors['price_markdown_nominal'] }" :placeholder="t('views.customer_group.fields.price_markdown_nominal')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="round_on" :class="{ 'text-danger': errors['round_on'] }">
                    {{ t('views.product_group.fields.round_on') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="round_on" rules="required" :label="t('views.product_group.fields.round_on')">
                    <FormSelect id="round_on" v-model="productGroupForm.data.round_on" v-bind="field" name="round_on"
                      :class="{ 'border-danger': errors['round_on'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="c in paymentTermTypeDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="round_on" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="round_digit" :class="{ 'text-danger': errors['round_digit'] }">
                    {{ t('views.customer_group.fields.round_digit') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="round_digit"
                    :label="t('views.customer_group.fields.round_digit')">
                    <FormInput id="round_digit" v-model="customerGroupForm.data.round_digit" v-bind="field" name="round_digit" type="number"
                      :class="{ 'border-danger': errors['round_digit'] }" :placeholder="t('views.customer_group.fields.round_digit')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="remarks" :class="{ 'text-danger': errors['remarks'] }">
                    {{ t('views.customer_group.fields.remarks') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="remarks"
                    :label="t('views.customer_group.fields.remarks')">
                    <FormTextarea id="remarks" v-model="customerGroupForm.data.remarks" v-bind="field" name="remarks" type="text"
                      :class="{ 'border-danger': errors['remarks'] }" :placeholder="t('views.customer_group.fields.remarks')" rows="3" />
                  </VeeField>
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
