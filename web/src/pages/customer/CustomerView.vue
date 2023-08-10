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
import { FormSwitch, FormInput, FormLabel, FormTextarea, FormSelect } from "../../base-components/Form";
import { ViewMode } from "../../types/enums/ViewMode";
import CustomerService from "../../services/CustomerService";
import { Customer } from "../../types/models/Customer";
import { Collection } from "../../types/resources/Collection";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { Dialog } from "../../base-components/Headless";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { DropDownOption } from "../../types/services/DropDownOption";
import { CustomerFormRequest } from "../../types/requests/CustomerFormRequest";
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

//#region Declarations
const { t } = useI18n();
const selectedUserStore = useSelectedUserLocationStore();
const userLocation = computed(() => selectedUserStore.selectedUserLocation);
const customerServices = new CustomerService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const datalistErrors = ref<LaravelError | VeeValidateError | null>(null);
const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'Customer Information', state: CardState.Expanded, },
  { title: '', state: CardState.Hidden, id: 'button' }
]);
const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
const customerForm = ref<FormRequest<Customer>>({
  data: {
    id: '',
    ulid: '',
    company: [],
    code: '',
    name: '',
    is_member: false,
    customer_group: [],
    zone: '',
    customer_addresses: [],
    max_open_invoice: 0,
    max_outstanding_invoice: 0,
    max_invoice_age: 0,
    payment_term_type: '',
    payment_term: 0,
    tax_id: '',
    taxable_enterprise: false,
    remarks: '',
    status: 'ACTIVE',
    customer_pic: [],
  }
});
const customerLists = ref<Collection<Customer[]> | null>({
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
const customerGroupDDL = ref<Array<DropDownOption> | null>(null);
const statusDDL = ref<Array<DropDownOption> | null>(null);
//#endregion

//#region onMounted
onMounted(async () => {
  await getCustomerList('', true, true, 1, 10);
  getDDL();
});
//#endregion

//#region Methods
const getCustomerList = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {  
  loading.value = true;

  let company_id = userLocation.value.company.id;  

  const searchReq: SearchRequest = {
    search: search,
    refresh: refresh,
    paginate: paginate,
    page: page,
    per_page: per_page
  };

  let result: ServiceResponse<Collection<Array<Customer>> | Resource<Array<Customer>> | null> = await customerServices.readAny(company_id, searchReq);

  if (result.success && result.data) {
    customerLists.value = result.data as Collection<Customer[]>;
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

const emptyCustomer = () => {
  return {
    data: {
      id: '',
      ulid: '',
      company: [],
      code: '',
      name: '',
      is_member: false,
      customer_group: [],
      zone: '',
      customer_addresses: [],
      max_open_invoice: 0,
      max_outstanding_invoice: 0,
      max_invoice_age: 0,
      payment_term_type: '',
      payment_term: 0,
      tax_id: '',
      taxable_enterprise: false,
      remarks: '',
      status: 'ACTIVE',
      customer_pic: [],
    }
  }
}

const onDataListChanged = (data: DataListEmittedData) => {
  getCustomerList(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const createNew = () => {
  mode.value = ViewMode.FORM_CREATE;

  let cachedData: unknown | null = cacheServices.getLastEntity('Customer');

  customerForm.value = cachedData == null ? emptyCustomer() : cachedData as FormRequest<Customer>;
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
  customerForm.value.data = customerLists.value?.data[itemIdx] as Customer;
}

const deleteSelected = (itemUlid: string) => {
  deleteUlid.value = itemUlid;
  deleteModalShow.value = true;
}

const confirmDelete = async () => {
  deleteModalShow.value = false;
  loading.value = true;

  let result: ServiceResponse<boolean | null> = await customerServices.delete(deleteUlid.value);

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

const onSubmit = async (values: FormRequest<Customer>, actions: FormActions<FormRequest<Customer>>) => {
  loading.value = true;

  let result: ServiceResponse<Customer | null> = {
    success: false,
  }

  if (mode.value == ViewMode.FORM_CREATE) {
    let company_id = userLocation.value.company.id;    
    values.company_id = company_id;
    result = await customerServices.create({data:values});
  } else if (mode.value == ViewMode.FORM_EDIT) {
    result = await customerServices.update({data:values});
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

  cacheServices.removeLastEntity('Customer');

  mode.value = ViewMode.LIST;
  await getCustomerList('', true, true, 1, 10);

  loading.value = false;
}


//#endregion

//#region Computed
const titleView = computed(() => {
  switch (mode.value) {
    case ViewMode.FORM_CREATE:
      return t('views.customer.actions.create');
    case ViewMode.FORM_EDIT:
      return t('views.customer.actions.edit');
    case ViewMode.LIST:
    default:
      return t('views.customer.page_title');
  }
});
//#endregion

//#region Watcher
watch(
  customerForm,
  debounce((newValue): void => {
    if (mode.value != ViewMode.FORM_CREATE) return;
    cacheServices.setLastEntity('Customer', newValue)
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
        <DataList :title="t('views.customer.table.title')" :enable-search="true" :can-print="true" :can-export="true"
          :pagination="customerLists ? customerLists.meta : null" @dataListChanged="onDataListChanged">
          <template #content>
            <Table class="mt-5" :hover="true">
              <Table.Thead variant="light">
                <Table.Tr>
                <Table.Th class="whitespace-nowrap">
                {{ t("views.customer.table.cols.code") }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t("views.customer.table.cols.name") }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t("views.customer.table.cols.is_member") }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t("views.customer.table.cols.status") }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap"></Table.Th>
                </Table.Tr>
              </Table.Thead>
              <Table.Tbody v-if="customerLists !== null">
                <template v-if="customerLists.data.length == 0">
                  <Table.Tr class="intro-x">
                    <Table.Td colspan="5">
                      <div class="flex justify-center italic">{{ t('components.data-list.data_not_found') }}</div>
                    </Table.Td>
                  </Table.Tr>
                </template>
                <template v-for="( item, itemIdx ) in customerLists.data" :key="item.ulid">
                  <Table.Tr class="intro-x">
                    <Table.Td>{{ item.code }}</Table.Td>
                    <Table.Td>{{ item.name }}</Table.Td>
                    <Table.Td>
                      <Lucide v-if="item.is_member === true" icon="CheckCircle" />
                      <Lucide v-if="item.is_member === false" icon="X" />
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
                        <Button variant="outline-secondary" disabled @click="deleteSelected(item.ulid)">
                          <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                        </Button>
                      </div>
                    </Table.Td>
                  </Table.Tr>
                  <Table.Tr :class="{ 'intro-x': true, 'hidden transition-all': expandDetail !== itemIdx }">
                    <Table.Td colspan="5">
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.code') }}</div>
                        <div class="flex-1">{{ item.code }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.name') }}</div>
                        <div class="flex-1">{{ item.name }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.is_member') }}</div>
                        <div class="flex-1">
                          <span v-if="item.is_member">{{ t('components.dropdown.values.switch.on') }}</span>
                          <span v-else>{{ t('components.dropdown.values.switch.off') }}</span>
                        </div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.customer_group') }}</div>
                        <div class="flex-1">{{ item.customer_group }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.max_open_invoice') }}</div>
                        <div class="flex-1">{{ item.max_open_invoice }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.max_outstanding_invoice') }}</div>
                        <div class="flex-1">{{ item.max_outstanding_invoice }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.max_invoice_age') }}</div>
                        <div class="flex-1">{{ item.max_invoice_age }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.payment_term') }}</div>
                        <div class="flex-1">{{ item.payment_term }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.payment_term_type') }}</div>
                        <div class="flex-1">{{ item.payment_term_type }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.taxable_enterprise') }}</div>
                        <div class="flex-1">
                          <span v-if="item.taxable_enterprise">{{ t('components.dropdown.values.switch.on') }}</span>
                          <span v-else>{{ t('components.dropdown.values.switch.off') }}</span>
                        </div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.tax_id') }}</div>
                        <div class="flex-1">{{ item.tax_id }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.remarks') }}</div>
                        <div class="flex-1">{{ item.remarks }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.status') }}</div>
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
        <VeeForm id="customerForm" v-slot="{ errors }" @submit="onSubmit">
          <AlertPlaceholder :errors="errors" />
          <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
              <div class="p-5">
                <div class="pb-4">
                  <FormLabel html-for="code" :class="{ 'text-danger': errors['code'] }">
                    {{ t('views.customer.fields.code') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="code" rules="required|alpha_num"
                    :label="t('views.customer.fields.code')">
                    <FormInput id="code" v-model="customerForm.data.code" v-bind="field" name="code" type="text"
                      :class="{ 'border-danger': errors['code'] }" :placeholder="t('views.customer.fields.code')" />
                  </VeeField>
                  <VeeErrorMessage name="code" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="name" :class="{ 'text-danger': errors['name'] }">
                    {{ t('views.customer.fields.name') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="name" rules="required|alpha_num"
                    :label="t('views.customer.fields.name')">
                    <FormInput id="name" v-model="customerForm.data.name" v-bind="field" name="name" type="text"
                      :class="{ 'border-danger': errors['name'] }" :placeholder="t('views.customer.fields.name')" />
                  </VeeField>
                  <VeeErrorMessage name="name" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="is_member" :class="{ 'text-danger': errors['is_member']} " class="pr-5">
                    {{ t('views.customer.fields.is_member') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="is_member" :label="t('views.customer.fields.is_member')">
                    <FormSwitch.Input id="is_member" v-model="customerForm.data.is_member" v-bind="field" name="is_member" type="checkbox"
                      :class="{ 'border-danger': errors['is_member'] }" :placeholder="t('views.customer.fields.is_member')"
                    />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="customer_group" :class="{ 'text-danger': errors['customer_group[]'] }">
                    {{ t('views.customer.fields.customer_group') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="customer_group[]" rules="required" :label="t('views.customer.fields.customer_group')">
                    <FormSelect id="customer_group" v-model="customerForm.data.customer_group" multiple size="6" v-bind="field"
                      :class="{ 'border-danger': errors['customer_group[]'] }">
                      <option v-for="r in customerGroupDDL" :key="r.id" :value="r">
                        {{ r.name }}
                      </option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="customer_group[]" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="zone" :class="{ 'text-danger': errors['zone'] }">
                    {{ t('views.customer.fields.zone') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="zone"
                    :label="t('views.customer.fields.zone')">
                    <FormTextarea id="zone" v-model="customerForm.data.zone" v-bind="field" name="zone" type="text"
                      :class="{ 'border-danger': errors['zone'] }" :placeholder="t('views.customer.fields.zone')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="customer_address" :class="{ 'text-danger': errors['customer_address[]'] }">
                    {{ t('views.customer.fields.customer_address') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="customer_address[]" rules="required" :label="t('views.customer.fields.customer_address')">
                    <FormSelect id="customer_address" v-model="customerForm.data.customer_address" multiple size="6" v-bind="field"
                      :class="{ 'border-danger': errors['customer_address[]'] }">
                      <option v-for="r in customerAddressDDL" :key="r.id" :value="r">
                        {{ r.name }}
                      </option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="customer_group[]" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="max_open_invoice" :class="{ 'text-danger': errors['max_open_invoice'] }">
                    {{ t('views.customer.fields.max_open_invoice') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="max_open_invoice"
                    :label="t('views.customer.fields.max_open_invoice')">
                    <FormInput id="max_open_invoice" v-model="customerForm.data.max_open_invoice" v-bind="field" name="max_open_invoice" type="text"
                      :class="{ 'border-danger': errors['max_open_invoice'] }" :placeholder="t('views.customer.fields.max_open_invoice')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="max_outstanding_invoice" :class="{ 'text-danger': errors['max_outstanding_invoice'] }">
                    {{ t('views.customer.fields.max_outstanding_invoice') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="max_outstanding_invoice"
                    :label="t('views.customer.fields.max_outstanding_invoice')">
                    <FormInput id="max_outstanding_invoice" v-model="customerForm.data.max_outstanding_invoice" v-bind="field" name="max_outstanding_invoice" type="text"
                      :class="{ 'border-danger': errors['max_outstanding_invoice'] }" :placeholder="t('views.customer.fields.max_outstanding_invoice')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="max_invoice_age" :class="{ 'text-danger': errors['max_invoice_age'] }">
                    {{ t('views.customer.fields.max_invoice_age') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="max_invoice_age"
                    :label="t('views.customer.fields.max_invoice_age')">
                    <FormInput id="max_invoice_age" v-model="customerForm.data.max_invoice_age" v-bind="field" name="max_invoice_age" type="text"
                      :class="{ 'border-danger': errors['max_invoice_age'] }" :placeholder="t('views.customer.fields.max_invoice_age')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="payment_term_type" :class="{ 'text-danger': errors['payment_term_type'] }">
                    {{ t('views.customer.fields.payment_term_type') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="payment_term_type"
                    :label="t('views.customer.fields.payment_term_type')">
                    <FormInput id="payment_term_type" v-model="customerForm.data.payment_term_type" v-bind="field" name="payment_term_type" type="text"
                      :class="{ 'border-danger': errors['payment_term_type'] }" :placeholder="t('views.customer.fields.payment_term_type')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="payment_term" :class="{ 'text-danger': errors['payment_term'] }">
                    {{ t('views.customer.fields.payment_term') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="payment_term"
                    :label="t('views.customer.fields.payment_term')">
                    <FormInput id="payment_term" v-model="customerForm.data.payment_term" v-bind="field" name="payment_term" type="text"
                      :class="{ 'border-danger': errors['payment_term'] }" :placeholder="t('views.customer.fields.payment_term')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="tax_id" :class="{ 'text-danger': errors['tax_id'] }">
                    {{ t('views.customer.fields.tax_id') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="tax_id"
                    :label="t('views.customer.fields.tax_id')">
                    <FormInput id="tax_id" v-model="customerForm.data.tax_id" v-bind="field" name="tax_id" type="text"
                      :class="{ 'border-danger': errors['tax_id'] }" :placeholder="t('views.customer.fields.tax_id')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="taxable_enterprise" :class="{ 'text-danger': errors['taxable_enterprise']} " class="pr-5">
                    {{ t('views.customer.fields.taxable_enterprise') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="taxable_enterprise" :label="t('views.customer.fields.taxable_enterprise')">
                    <FormSwitch.Input id="taxable_enterprise" v-model="customerForm.data.taxable_enterprise" v-bind="field" name="taxable_enterprise" type="checkbox"
                      :class="{ 'border-danger': errors['taxable_enterprise'] }" :placeholder="t('views.customer.fields.taxable_enterprise')"
                    />
                  </VeeField>
                  <VeeErrorMessage name="taxable_enterprise" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="remarks" :class="{ 'text-danger': errors['remarks'] }">
                    {{ t('views.customer.fields.remarks') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="remarks"
                    :label="t('views.customer.fields.remarks')">
                    <FormTextarea id="remarks" v-model="customerForm.data.remarks" v-bind="field" name="remarks" type="text"
                      :class="{ 'border-danger': errors['remarks'] }" :placeholder="t('views.customer.fields.remarks')" rows="3" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="status" :class="{ 'text-danger': errors['status'] }">
                    {{ t('views.customer.fields.status') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="status" rules="required" :label="t('views.customer.fields.status')">
                    <FormSelect id="status" v-model="customerForm.data.status" v-bind="field" name="status"
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
