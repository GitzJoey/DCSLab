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
import { FormInput, FormLabel, FormTextarea, FormSelect } from "../../base-components/Form";
import { ViewMode } from "../../types/enums/ViewMode";
import PurchaseOrderService from "../../services/PurchaseOrderService";
import { PurchaseOrder } from "../../types/models/PurchaseOrder";
import { Collection } from "../../types/resources/Collection";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { Dialog } from "../../base-components/Headless";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { DropDownOption } from "../../types/services/DropDownOption";
import { PurchaseOrderFormRequest } from "../../types/requests/PurchaseOrderFormRequest";
import { PurchaseOrderFormFieldValues } from "../../types/requests/PurchaseOrderFormFieldValues";
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
//#endregion

//#region Declarations
const { t } = useI18n();
const cacheServices = new CacheService();
const dashboardServices = new DashboardService();
const selectedUserStore = useSelectedUserLocationStore();
const userLocation = computed(() => selectedUserStore.selectedUserLocation);
const purchaseOrderServices = new PurchaseOrderService();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const datalistErrors = ref<LaravelError | VeeValidateError | null>(null);
const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'Company Information', state: CardState.Expanded, },
  { title: 'PurchaseOrder Information', state: CardState.Expanded, },
  { title: '', state: CardState.Hidden, id: 'button' }
]);
const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
const purchaseOrderForm = ref<PurchaseOrderFormRequest>({
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
    branch: {
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
    },
    invoice_code: '',
    invoice_date: '',
    shipping_date: '',
    shipping_address: '',
    supplier: {
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
      contact: '',
      address: '',
      city: '',
      payment_term_type: '',
      payment_term: 0,
      taxable_enterprise: 0,
      tax_id: '',
      remarks: '',
      status: '',
      supplier_pic: User,
      supplier_products: SupplierProduct,
      selected_products: '',
      main_products: '',
    },
    global_discounts: {
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
      branch: {
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
      },
      purchase_order: null,
      discount_type: '',
      amount: 0,
    },
    product_units: '',
    remarks: '',
    status: 'ACTIVE',
  }
});

const purchaseOrderLists = ref<Collection<PurchaseOrder[]> | null>({
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
  await getPurchaseOrders('', true, true, 1, 10);

  getDDL();
});
//#endregion

//#region Methods
const getPurchaseOrders = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {  
  loading.value = true;

  let company_id = userLocation.value.company.id;
  let branch_id = userLocation.value.branch.id;

  const searchReq: SearchRequest = {
    search: search,
    refresh: refresh,
    paginate: paginate,
    page: page,
    per_page: per_page
  };

  let result: ServiceResponse<Collection<Array<PurchaseOrder>> | Resource<Array<PurchaseOrder>> | null> = await purchaseOrderServices.readAny(company_id, branch_id, searchReq);

  if (result.success && result.data) {
    purchaseOrderLists.value = result.data as Collection<PurchaseOrder[]>;
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

const selectedCompanyId = () => {
  return userLocation.value.company.id;
}

const emptyPurchaseOrder = () => {
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
      branch: {
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
      },
      invoice_code: '',
      invoice_date: '',
      shipping_date: '',
      shipping_address: '',
      supplier: '',
      global_discounts: '',
      product_units: '',
      remarks: '',
      status: 'ACTIVE',
    }
  }
}

const onDataListChanged = (data: DataListEmittedData) => {
  getPurchaseOrders(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const createNew = () => {
  mode.value = ViewMode.FORM_CREATE;

  let cachedData: unknown | null = cacheServices.getLastEntity('PurchaseOrder');

  purchaseOrderForm.value = cachedData == null ? emptyPurchaseOrder() : cachedData as PurchaseOrderFormRequest;
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
  purchaseOrderForm.value.data = purchaseOrderLists.value?.data[itemIdx] as PurchaseOrder;
}

const deleteSelected = (itemUlid: string) => {
  deleteUlid.value = itemUlid;
  deleteModalShow.value = true;
}

const confirmDelete = async () => {
  deleteModalShow.value = false;
  loading.value = true;

  let result: ServiceResponse<boolean | null> = await purchaseOrderServices.delete(deleteUlid.value);

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

const onSubmit = async (values: PurchaseOrderFormFieldValues, actions: FormActions<PurchaseOrderFormFieldValues>) => {
  loading.value = true;

  let result: ServiceResponse<PurchaseOrder | null> = {
    success: false,
  }

  if (mode.value == ViewMode.FORM_CREATE) {
    result = await purchaseOrderServices.create(values);
  } else if (mode.value == ViewMode.FORM_EDIT) {
    let purchaseOrder_ulid = purchaseOrderForm.value.data.ulid;

    result = await purchaseOrderServices.update( purchaseOrder_ulid, values);
  } else {
    result.success = false;
  }

  if (!result.success) {
    actions.setErrors({ invoice_code: 'error' });
  } else {
    backToList();
  }

  loading.value = false;
};

const backToList = async () => {
  loading.value = true;

  cacheServices.removeLastEntity('PurchaseOrder');

  mode.value = ViewMode.LIST;
  await getPurchaseOrders('', true, true, 1, 10);

  loading.value = false;
}


//#endregion

//#region Computed
const titleView = computed(() => {
  switch (mode.value) {
    case ViewMode.FORM_CREATE:
      return t('views.purchase_order.actions.create');
    case ViewMode.FORM_EDIT:
      return t('views.purchase_order.actions.edit');
    case ViewMode.LIST:
    default:
      return t('views.purchase_order.page_title');
  }
});
//#endregion

//#region Watcher
watch(
  purchaseOrderForm,
  debounce((newValue): void => {
    if (mode.value != ViewMode.FORM_CREATE) return;
    cacheServices.setLastEntity('PurchaseOrder', newValue)
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
        <AlertPlaceholder :errors="datalistErrors" :title="t('views.purchase_order.table.title')" />
        <DataList :title="t('views.purchase_order.table.title')" :enable-search="true" :can-print="true" :can-export="true"
          :pagination="purchaseOrderLists ? purchaseOrderLists.meta : null" @dataListChanged="onDataListChanged">
          <template #content>
            <Table class="mt-5" :hover="true">
              <Table.Thead variant="light">
                <Table.Tr>
                <Table.Th class="whitespace-nowrap">
                {{ t("views.purchase_order.table.cols.invoice_code") }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t("views.purchase_order.table.cols.shipping_address") }}
                </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.purchase_order.table.cols.remarks") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.purchase_order.table.cols.status") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap"></Table.Th>
                </Table.Tr>
              </Table.Thead>
              <Table.Tbody v-if="purchaseOrderLists !== null">
                <template v-if="purchaseOrderLists.data.length == 0">
                  <Table.Tr class="intro-x">
                    <Table.Td colspan="5">
                      <div class="flex justify-center italic">{{ t('components.data-list.data_not_found') }}</div>
                    </Table.Td>
                  </Table.Tr>
                </template>
                <template v-for="( item, itemIdx ) in purchaseOrderLists.data" :key="item.ulid">
                  <Table.Tr class="intro-x">
                    <Table.Td>{{ item.invoice_code }}</Table.Td>
                    <Table.Td>{{ item.shipping_address }}</Table.Td>
                    <Table.Td>{{ item.remarks }}</Table.Td>
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
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.purchase_order.fields.invoice_code') }}</div>
                        <div class="flex-1">{{ item.invoice_code }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.purchase_order.fields.invoice_date') }}</div>
                        <div class="flex-1">{{ item.invoice_date }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.purchase_order.fields.shipping_date') }}</div>
                        <div class="flex-1">{{ item.shipping_date }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.purchase_order.fields.shipping_date') }}</div>
                        <div class="flex-1">{{ item.shipping_date }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.purchase_order.fields.shipping_address') }}</div>
                        <div class="flex-1">{{ item.shipping_address }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.purchase_order.fields.supplier') }}</div>
                        <div class="flex-1">{{ item.supplier.name }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.purchase_order.fields.global_discounts') }}</div>
                        <div class="flex-1">{{ item.global_discounts.name }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.purchase_order.fields.product_units') }}</div>
                        <div class="flex-1">{{ item.product_units.name }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.purchase_order.fields.remarks') }}</div>
                        <div class="flex-1">{{ item.remarks }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.purchase_order.fields.status') }}</div>
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
        <VeeForm id="purchaseOrderForm" v-slot="{ errors }" @submit="onSubmit">
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
                <VeeField v-slot="{ field }" :value=selectedCompanyId() name="company_id">                  
                  <FormInput id="company_id" name="company_id" type="hidden" v-bind="field" />
                </VeeField>
                <div class="pb-4">
                  <FormLabel html-for="code" :class="{ 'text-danger': errors['code'] }">
                    {{ t('views.purchase_order.fields.code') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="code" rules="required|alpha_num"
                    :label="t('views.purchase_order.fields.code')">
                    <FormInput id="code" v-model="purchaseOrderForm.data.code" v-bind="field" name="code" type="text"
                      :class="{ 'border-danger': errors['code'] }" :placeholder="t('views.purchase_order.fields.code')" />
                  </VeeField>
                  <VeeErrorMessage name="code" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="invoice_date">
                    {{ t('views.purchase_order.fields.settings.invoice_date') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="purchaseOrderForm.data.invoice_date" name="invoice_date">
                    <FormSelect v-show="mode == ViewMode.FORM_CREATE || mode == ViewMode.FORM_EDIT" id="invoice_date"
                      name="invoice_date" v-bind="field">
                      <option value="yyyy_MM_dd">{{ 'YYYY-MM-DD' }}</option>
                      <option value="dd_MMM_yyyy">{{ 'DD-MMM-YYYY' }}</option>
                    </FormSelect>
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="shipping_date">
                    {{ t('views.purchase_order.fields.shipping_date') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" v-model="purchaseOrderForm.data.shipping_date" name="shipping_date">
                    <FormSelect v-show="mode == ViewMode.FORM_CREATE || mode == ViewMode.FORM_EDIT" id="shipping_date"
                      name="shipping_date" v-bind="field">
                      <option value="yyyy_MM_dd">{{ 'YYYY-MM-DD' }}</option>
                      <option value="dd_MMM_yyyy">{{ 'DD-MMM-YYYY' }}</option>
                    </FormSelect>
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="shipping_address" :class="{ 'text-danger': errors['shipping_address'] }">
                    {{ t('views.purchase_order.fields.shipping_address') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="shipping_address"
                    :label="t('views.purchase_order.fields.shipping_address')">
                    <FormTextarea id="shipping_address" v-model="purchaseOrderForm.data.shipping_address" v-bind="field" name="shipping_address" type="text"
                      :class="{ 'border-danger': errors['shipping_address'] }" :placeholder="t('views.purchase_order.fields.shipping_address')" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="supplier" :class="{ 'text-danger': errors['supplier'] }">
                    {{ t('views.product.fields.supplier') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="supplier" rules="required" :label="t('views.product.fields.supplier')">
                    <FormSelect id="supplier" v-model="purchaseOrderForm.data.supplier" v-bind="field" name="supplier"
                      :class="{ 'border-danger': errors['supplier'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="s in supplierDDL" :key="s.code" :value="s.code">{{ t(s.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="brand" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="global_discount" :class="{ 'text-danger': errors['global_discount'] }">
                    {{ t('views.product.fields.global_discount') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="global_discount" rules="required" :label="t('views.product.fields.global_discount')">
                    <FormSelect id="global_discount" v-model="purchaseOrderForm.data.global_discounts" v-bind="field" name="global_discount"
                      :class="{ 'border-danger': errors['global_discount'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="g in globalDiscountDDL" :key="g.code" :value="g.code">{{ t(g.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="global_discount" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="product_unit" :class="{ 'text-danger': errors['product_unit'] }">
                    {{ t('views.product.fields.product_unit') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="product_unit" rules="required" :label="t('views.product.fields.product_unit')">
                    <FormSelect id="product_unit" v-model="purchaseOrderForm.data.product_units" v-bind="field" name="product_unit"
                      :class="{ 'border-danger': errors['product_unit'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="p in productUnitDDL" :key="p.code" :value="p.code">{{ t(p.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="product_unit" class="mt-2 text-danger" />
                </div>
                <div class="pb-4">
                  <FormLabel html-for="remarks" :class="{ 'text-danger': errors['remarks'] }">
                    {{ t('views.purchase_order.fields.remarks') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="remarks"
                    :label="t('views.purchase_order.fields.remarks')">
                    <FormTextarea id="remarks" v-model="purchaseOrderForm.data.remarks" v-bind="field" name="remarks" type="text"
                      :class="{ 'border-danger': errors['remarks'] }" :placeholder="t('views.purchase_order.fields.remarks')" rows="3" />
                  </VeeField>
                </div>
                <div class="pb-4">
                  <FormLabel html-for="status" :class="{ 'text-danger': errors['status'] }">
                    {{ t('views.purchase_order.fields.status') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="status" rules="required" :label="t('views.purchase_order.fields.status')">
                    <FormSelect id="status" v-model="purchaseOrderForm.data.status" v-bind="field" name="status"
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