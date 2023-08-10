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
import ProductService from "../../services/ProductService";
import ProductGroupService from "../../services/ProductGroupService";
import BrandService from "../../services/BrandService";
import UnitService from "../../services/UnitService";
import { ProductFormFieldValues } from "../../types/requests/ProductFormFieldValues";
import { Product } from "../../types/models/Product";
import { Collection } from "../../types/resources/Collection";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { Dialog } from "../../base-components/Headless";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { DropDownOption } from "../../types/services/DropDownOption";
import { ProductFormRequest } from "../../types/requests/ProductFormRequest";
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
const productGroupServices = new ProductGroupService();
const brandServices = new BrandService();
const unitServices = new UnitService();
const productServices = new ProductService();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const datalistErrors = ref<LaravelError | VeeValidateError | null>(null);
const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'Company Information', state: CardState.Expanded, },
  { title: 'Product Information', state: CardState.Expanded, },
  { title: '', state: CardState.Hidden, id: 'button' }
]);
const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
const productForm = ref<ProductFormRequest>({
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
      status: '',
      branches: [],
    },
    code: '',
    name: '',
    product_group: {
        company: {
        id: '',
        ulid: '',
        code: '',
        name: '',
        address: '',
        default: false,
        status: '',
        branches: [],
      },
      id: '',
      ulid: '',
      code: '',
      name: '',
      category: 'PRODUCTS'
    },
    brand: {
      company: {
        id: '',
        ulid: '',
        code: '',
        name: '',
        address: '',
        default: false,
        status: '',
        branches: [],
      },
      id: '',
      ulid: '',
      code: '',
      name: ''
    },
    product_units: [
      {
        id: '',
        ulid: '',
        company: {
          id: '',
          ulid: '',
          code: '',
          name: '',
          address: '',
          default: false,
          status: '',          
          branches: [],
        },
        product: null,
        code: '[AUTO]',
        unit: {
            id: '',
            ulid: '',
            company: null,
            code: '',
            name: '',
            description: '',
            category: ''
        },
        is_base: true,
        conversion_value: 1,
        is_primary_unit: true,
        remarks: '',
      }
    ],
    product_type: '',
    taxable_supply: '',
    price_include_vat: false,
    standard_rated_supply: 0,
    point: 0,
    use_serial_number: false,
    has_expiry_date: false,
    remarks: '',
    status: 'ACTIVE',
  }
});

const productLists = ref<Collection<Product[]> | null>({
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
const productGroupDDL = ref<Array<DropDownOption> | null>(null);
const brandDDL = ref<Array<DropDownOption> | null>(null);
const productTypeDDL = ref<Array<DropDownOption> | null>(null);
const unitDDL = ref<Array<DropDownOption> | null>(null);
const statusDDL = ref<Array<DropDownOption> | null>(null);
//#endregion

//#region onMounted
onMounted(async () => {
  await getProducts('', true, true, 1, 10);

  getDDL();
});
//#endregion

//#region Methods
const getProducts = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {  
  loading.value = true;

  let company_id = userLocation.value.company.id;  
  
  const searchReq: SearchRequest = {
    search: search,
    refresh: refresh,
    paginate: paginate,
    page: page,
    per_page: per_page
  };

  let result: ServiceResponse<Collection<Array<Product>> | Resource<Array<Product>> | null> = await productServices.readAnyProducts(company_id, searchReq);

  if (result.success && result.data) {
    productLists.value = result.data as Collection<Product[]>;
  } else {
    datalistErrors.value = result.errors as LaravelError;
  }

  loading.value = false;
}

const getDDL = async() => {
  let company_id = userLocation.value.company.id;

  productGroupServices.getProductGroupDDL(company_id).then((result: Array<DropDownOption> | null) => {
    productGroupDDL.value = result;
  });
  brandServices.getBrandDDL(company_id).then((result: Array<DropDownOption> | null) => {
    brandDDL.value = result;
  });
  unitServices.getUnitDDL(company_id).then((result: Array<DropDownOption> | null) => {
    unitDDL.value = result;
  });
  dashboardServices.getProductTypeDDL().then((result: Array<DropDownOption> | null) => {
    productTypeDDL.value = result;
  });
  dashboardServices.getStatusDDL().then((result: Array<DropDownOption> | null) => {
    statusDDL.value = result;
  });
}

const selectedCompanyId = () => {
  return userLocation.value.company.id;
}

const emptyProduct = () => {
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
        status: '',
        branches: []
      },
      code: '',
      product_group: {
        company: {
          id: '',
          ulid: '',
          code: '',
          name: '',
          address: '',
          default: false,
          status: '',
          branches: []
        },
        id: '',
        ulid: '',
        code: '',
        name: '',
        category: 'PRODUCTS'
      },
      brand: {
        company: {
          id: '',
          ulid: '',
          code: '',
          name: '',
          address: '',
          default: false,
          status: ''
        },
        id: '',
        ulid: '',
        code: '',
        name: ''
      },
      name: '',
      product_units: [
        {
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
          product: null,
          code: '[AUTO]',
          unit: {
            id: '',
            ulid: '',
            company: null,
            code: '',
            name: '',
            description: '',
            category: ''
        },
          is_base: true,
          conversion_value: 1,
          is_primary_unit: true,
          remarks: '',
        }
      ],
      product_type: '',
      taxable_supply: '',
      price_include_vat: false,
      standard_rated_supply: 0,
      point: 0,
      use_serial_number: false,
      has_expiry_date: false,
      remarks: '',
      status: 'ACTIVE',
    }
  }
}

const onDataListChanged = (data: DataListEmittedData) => {
  getProducts(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const createNew = () => {
  mode.value = ViewMode.FORM_CREATE;

  let cachedData: unknown | null = cacheServices.getLastEntity('Branch');

  productForm.value = cachedData == null ? emptyProduct() : cachedData as BranchFormRequest;
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
  productForm.value.data = productLists.value?.data[itemIdx] as Product;
}

const deleteSelected = (itemUlid: string) => {
  deleteUlid.value = itemUlid;
  deleteModalShow.value = true;
}

const confirmDelete = async () => {
  deleteModalShow.value = false;
  loading.value = true;

  let result: ServiceResponse<boolean | null> = await productServices.delete(deleteUlid.value);

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

const onSubmit = async (values: ProductFormFieldValues, actions: FormActions<ProductFormFieldValues>) => {
  loading.value = true;

  let result: ServiceResponse<Product | null> = {
    success: false,
  }

  if (mode.value == ViewMode.FORM_CREATE) {
    result = await productServices.create(values);
  } else if (mode.value == ViewMode.FORM_EDIT) {
    let product_ulid = productForm.value.data.ulid;

    result = await productServices.update( product_ulid, values);
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

  cacheServices.removeLastEntity('Product');

  mode.value = ViewMode.LIST;
  await getProducts('', true, true, 1, 10);

  loading.value = false;
}
//#endregion

//#region Computed
const titleView = computed(() => {
  switch (mode.value) {
    case ViewMode.FORM_CREATE:
      return t('views.product.actions.create');
    case ViewMode.FORM_EDIT:
      return t('views.product.actions.edit');
    case ViewMode.LIST:
    default:
      return t('views.product.page_title');
  }
});
//#endregion

//#region Watcher
watch(
  productForm,
  debounce((newValue): void => {
    if (mode.value != ViewMode.FORM_CREATE) return;
    cacheServices.setLastEntity('Product', newValue)
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
        <AlertPlaceholder :errors="datalistErrors" :title="t('views.product.table.title')" />
        <DataList :title="t('views.product.table.title')" :enable-search="true" :can-print="true" :can-export="true"
          :pagination="productLists ? productLists.meta : null" @dataListChanged="onDataListChanged">
          <template #content>
            <Table class="mt-5" :hover="true">
              <Table.Thead variant="light">
                <Table.Tr>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.product.table.cols.code") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.product.table.cols.name") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.product.table.cols.product_group") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.product.table.cols.brand") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap">
                    {{ t("views.product.table.cols.status") }}
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap"></Table.Th>
                </Table.Tr>
              </Table.Thead>
              <Table.Tbody v-if="productLists !== null">
                <template v-if="productLists.data.length == 0">
                  <Table.Tr class="intro-x">
                    <Table.Td colspan="5">
                      <div class="flex justify-center italic">{{ t('components.data-list.data_not_found') }}</div>
                    </Table.Td>
                  </Table.Tr>
                </template>
                <template v-for="( item, itemIdx ) in productLists.data" :key="item.ulid">
                  <Table.Tr class="intro-x">
                    <Table.Td>{{ item.code }}</Table.Td>
                    <Table.Td>{{ item.name }}</Table.Td>
                    <Table.Td>{{ item.product_group.name }}</Table.Td>
                    <Table.Td>{{ item.brand.name }}</Table.Td>
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
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.code') }}</div>
                        <div class="flex-1">{{ item.code }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.name') }}</div>
                        <div class="flex-1">{{ item.name }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.product_group') }}</div>
                        <div class="flex-1">{{ item.product_group.name }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.brand') }}</div>
                        <div class="flex-1">{{ item.brand.name }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.product_type') }}</div>
                        <div class="flex-1">
                          <span v-if="item.product_type === 'RAW_MATERIAL'">
                            {{ t('components.dropdown.values.productTypeDDL.raw') }}
                          </span>
                          <span v-if="item.product_type === 'WORK_IN_PROGRESS'">
                            {{ t('components.dropdown.values.productTypeDDL.wip') }}
                          </span>
                          <span v-if="item.product_type === 'FINISHED_GOODS'">
                            {{ t('components.dropdown.values.productTypeDDL.fg') }}
                          </span>
                          <span v-if="item.product_type === 'SERVICE'">
                            {{ t('components.dropdown.values.productTypeDDL.svc') }}
                          </span>
                        </div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.taxable_supply') }}</div>
                        <div class="flex-1">{{ item.taxable_supply }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.taxable_supply') }}</div>
                        <div class="flex-1">{{ item.taxable_supply }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.standard_rated_supply') }}</div>
                        <div class="flex-1">{{ item.standard_rated_supply }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.price_include_vat') }}</div>
                        <div class="flex-1">
                          <span v-if="item.price_include_vat">{{ t('components.dropdown.values.switch.on') }}</span>
                          <span v-else>{{ t('components.dropdown.values.switch.off') }}</span>
                        </div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.point') }}</div>
                        <div class="flex-1">{{ item.point }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.use_serial_number') }}</div>
                        <div class="flex-1">
                          <span v-if="item.use_serial_number">{{ t('components.dropdown.values.switch.on') }}</span>
                          <span v-else>{{ t('components.dropdown.values.switch.off') }}</span>
                        </div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.has_expiry_date') }}</div>
                        <div class="flex-1">
                          <span v-if="item.has_expiry_date">{{ t('components.dropdown.values.switch.on') }}</span>
                          <span v-else>{{ t('components.dropdown.values.switch.off') }}</span>
                        </div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.remarks') }}</div>
                        <div class="flex-1">{{ item.remarks }}</div>
                      </div>
                      <div class="flex flex-row">
                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.status') }}</div>
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
        <VeeForm id="productForm" v-slot="{ errors }" @submit="onSubmit">
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
                <!-- #region Code -->
                <div class="pb-4">
                  <FormLabel html-for="code" :class="{ 'text-danger': errors['code'] }">
                    {{ t('views.product.fields.code') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="code" rules="required|alpha_num"
                    :label="t('views.product.fields.code')">
                    <FormInput id="code" v-model="productForm.data.code" v-bind="field" name="code" type="text"
                      :class="{ 'border-danger': errors['code'] }" :placeholder="t('views.product.fields.code')" />
                  </VeeField>
                  <VeeErrorMessage name="code" class="mt-2 text-danger" />
                </div>
                <!-- #endregion -->

                <!-- #region Product Group -->
                <div class="pb-4">
                  <FormLabel html-for="product_group" :class="{ 'text-danger': errors['product_group'] }">
                    {{ t('views.product.fields.product_group') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="product_group" rules="required" :label="t('views.product.fields.product_group')">
                    <FormSelect id="product_group" v-model="productForm.data.product_group" v-bind="field" name="product_group"
                      :class="{ 'border-danger': errors['product_group'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="g in productGroupDDL" :key="g.code" :value="g.code">{{ t(g.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="product_group" class="mt-2 text-danger" />
                </div>
                <!-- #endregion -->

                <!-- #region Brand -->
                <div class="pb-4">
                  <FormLabel html-for="brand" :class="{ 'text-danger': errors['brand'] }">
                    {{ t('views.product.fields.brand') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="brand" rules="required" :label="t('views.product.fields.brand')">
                    <FormSelect id="brand" v-model="productForm.data.brand" v-bind="field" name="brand"
                      :class="{ 'border-danger': errors['brand'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="b in brandDDL" :key="b.code" :value="b.code">{{ t(b.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="brand" class="mt-2 text-danger" />
                </div>
                <!-- #endregion -->                

                <!-- #region Name -->
                <div class="pb-4">
                  <FormLabel html-for="name" :class="{ 'text-danger': errors['name'] }">
                    {{ t('views.product.fields.name') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="name" rules="required"
                    :label="t('views.product.fields.name')">
                    <FormInput id="name" v-model="productForm.data.name" v-bind="field" name="name" type="text"
                      :class="{ 'border-danger': errors['name'] }" :placeholder="t('views.product.fields.name')" />
                  </VeeField>
                  <VeeErrorMessage name="name" class="mt-2 text-danger" />
                </div>
                <!-- #endregion -->

                <!-- #region Product Units -->
                <div class="mb-3">
                  <label for="inputUnit" class="form-label">{{ t('views.product.fields.units.title') }}</label>
                  <div class="grid grid-cols-9 mb-3 bg-gray-700 dark:bg-dark-1 gap-2">
                      <div class="text-white p-3 font-bold col-span-2">{{ t('views.product.fields.units.table.cols.code') }}</div>
                      <div class="text-white p-3 font-bold col-span-2">{{ t('views.product.fields.units.table.cols.unit') }}</div>
                      <div class="text-white p-3 font-bold col-span-2 text-left">{{ t('views.product.fields.units.table.cols.conversion_value') }}</div>
                      <div class="flex justify-center text-white p-3 font-bold">{{ t('views.product.fields.units.table.cols.is_base') }}</div>
                      <div class="flex justify-center text-white p-3 font-bold">{{ t('views.product.fields.units.table.cols.is_primary') }}</div>
                      <div class="text-white p-3 font-bold"></div>
                  </div>
                </div>
                <!-- {{ productForm.data.product_units }} -->
                <div class="grid grid-cols-9 gap-2 mb-2" v-for="(pu, puIdx) in productForm.data.product_units">

                  <!-- #region Product Unit Code -->
                  <div class="col-span-2">
                    <FormLabel html-for="code" :class="{ 'text-danger': errors['code'] }">
                      {{ t('views.product.fields.units.code') }}
                    </FormLabel>
                    <VeeField v-slot="{ field }" name="code" rules="required|alpha_num"
                      :label="t('views.product.fields.units.code')">
                      <FormInput id="code" v-model="pu.code" v-bind="field" name="code" type="text"
                        :class="{ 'border-danger': errors['code'] }" :placeholder="t('views.product.fields.units.code')" />
                    </VeeField>
                    <VeeErrorMessage name="code" class="mt-2 text-danger" />
                  </div>
                  <!-- #endregion -->

                  <!-- #region Product Unit Unit -->
                  <div class="col-span-2">
                    <FormLabel html-for="unit" :class="{ 'text-danger': errors['unit'] }">
                      {{ t('views.product.fields.units.unit') }}
                    </FormLabel>
                    <VeeField v-slot="{ field }" name="unit" rules="required" :label="t('views.product.fields.units.unit')">
                      <FormSelect id="unit" v-model="pu.unit" v-bind="field" name="unit"
                        :class="{ 'border-danger': errors['unit'] }">
                        <option value="">{{ t('components.dropdown.placeholder') }}</option>
                        <option v-for="u in unitDDL" :key="u.code" :value="u.code">{{ t(u.name) }}</option>
                      </FormSelect>
                    </VeeField>
                    <VeeErrorMessage name="unit" class="mt-2 text-danger" />
                  </div>
                  <!-- #endregion -->

                  <!-- #region Product Unit Conversiont Value -->
                  <div class="col-span-2">
                    <FormLabel html-for="conv_value" :class="{ 'text-danger': errors['conv_value'] }">
                      {{ t('views.product.fields.units.conv_value') }}
                    </FormLabel>
                    <VeeField v-slot="{ field }" name="conv_value"
                      :label="t('views.product.fields.units.conv_value')">
                      <FormInput id="conv_value" v-model="pu.conversion_value" v-bind="field" name="conv_value" type="text"
                        :class="{ 'border-danger': errors['conv_value'] }" :placeholder="t('views.product.fields.units.conv_value')" />
                    </VeeField>
                  </div>
                  <!-- #endregion -->

                  <!-- #region Has Expiry Date -->
                  <div class="pb-4 text-center">
                    <FormLabel html-for="is_base" :class="{ 'text-danger': errors['is_base']} " class="pr-5">
                      {{ t('views.product.fields.units.is_base') }}
                    </FormLabel>
                    <VeeField v-slot="{ field }" name="is_base" :label="t('views.product.fields.units.is_base')">
                      <FormSwitch.Input id="is_base" v-model="pu.is_base" v-bind="field" name="is_base" type="checkbox"
                        :class="{ 'border-danger': errors['is_base'] }" :placeholder="t('views.product.fields.units.is_base')"
                      />
                    </VeeField>
                    <VeeErrorMessage name="is_base" class="mt-2 text-danger" />
                  </div>
                  <!-- #endregion -->

                  <!-- #region Has Expiry Date -->
                  <div class="pb-4">
                    <FormLabel html-for="is_primary" :class="{ 'text-danger': errors['is_primary']} " class="pr-5">
                      {{ t('views.product.fields.units.is_primary') }}
                    </FormLabel>
                    <VeeField v-slot="{ field }" name="is_primary" :label="t('views.product.fields.units.is_primary')">
                      <FormSwitch.Input id="is_primary" v-model="pu.is_primary" v-bind="field" name="is_primary" type="checkbox"
                        :class="{ 'border-danger': errors['is_primary'] }" :placeholder="t('views.product.fields.units.is_primary')"
                      />
                    </VeeField>
                    <VeeErrorMessage name="is_primary" class="mt-2 text-danger" />
                  </div>
                  <!-- #endregion -->
                </div>
                <!-- #endregion -->
                
                <!-- #region Product Type -->
                <div class="pb-4">
                  <FormLabel html-for="product_type" :class="{ 'text-danger': errors['product_type'] }">
                    {{ t('views.product.fields.product_type') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="product_type" rules="required" :label="t('views.product.fields.product_type')">
                    <FormSelect id="product_type" v-model="productForm.data.product_type" v-bind="field" name="product_type"
                      :class="{ 'border-danger': errors['product_type'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="p in productTypeDDL" :key="p.code" :value="p.code">{{ t(p.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="product_type" class="mt-2 text-danger" />
                </div>
                <!-- #endregion -->

                <!-- #region Taxable Supply -->
                <div class="pb-4">
                  <FormLabel html-for="taxable_supply" :class="{ 'text-danger': errors['taxable_supply'] }">
                    {{ t('views.product.fields.taxable_supply') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="taxable_supply"
                    :label="t('views.product.fields.taxable_supply')">
                    <FormInput id="taxable_supply" v-model="productForm.data.taxable_supply" v-bind="field" name="taxable_supply" type="text"
                      :class="{ 'border-danger': errors['taxable_supply'] }" :placeholder="t('views.product.fields.taxable_supply')" />
                  </VeeField>
                </div>
                <!-- #region -->

                <!-- #region Standard Rated Supply -->
                <div class="pb-4">
                  <FormLabel html-for="standard_rated_supply" :class="{ 'text-danger': errors['standard_rated_supply'] }">
                    {{ t('views.product.fields.standard_rated_supply') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="standard_rated_supply"
                    :label="t('views.product.fields.standard_rated_supply')">
                    <FormInput id="standard_rated_supply" v-model="productForm.data.standard_rated_supply" v-bind="field" name="standard_rated_supply" type="text"
                      :class="{ 'border-danger': errors['standard_rated_supply'] }" :placeholder="t('views.product.fields.standard_rated_supply')" />
                  </VeeField>
                </div>
                <!-- #endregion -->

                <!-- #region Price Include VAT -->
                <div class="pb-4">
                  <FormLabel html-for="price_include_vat" :class="{ 'text-danger': errors['price_include_vat']} " class="pr-5">
                    {{ t('views.product.fields.price_include_vat') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="price_include_vat" :label="t('views.product.fields.price_include_vat')">
                    <FormSwitch.Input id="price_include_vat" v-model="productForm.data.price_include_vat" v-bind="field" name="price_include_vat" type="checkbox"
                      :class="{ 'border-danger': errors['price_include_vat'] }" :placeholder="t('views.product.fields.price_include_vat')"
                    />
                  </VeeField>
                  <VeeErrorMessage name="price_include_vat" class="mt-2 text-danger" />
                </div>
                <!-- #endregion -->

                <!-- #region Point -->
                <div class="pb-4">
                  <FormLabel html-for="point" :class="{ 'text-danger': errors['point'] }">
                    {{ t('views.product.fields.point') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="point"
                    :label="t('views.product.fields.point')">
                    <FormInput id="point" v-model="productForm.data.point" v-bind="field" name="point" type="text"
                      :class="{ 'border-danger': errors['point'] }" :placeholder="t('views.product.fields.point')" />
                  </VeeField>
                </div>
                <!-- #endregion -->

                <!-- #region User Serial Number -->
                <div class="pb-4">
                  <FormLabel html-for="use_serial_number" :class="{ 'text-danger': errors['use_serial_number']} " class="pr-5">
                    {{ t('views.product.fields.use_serial_number') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="use_serial_number" :label="t('views.product.fields.use_serial_number')">
                    <FormSwitch.Input id="use_serial_number" v-model="productForm.data.use_serial_number" v-bind="field" name="use_serial_number" type="checkbox"
                      :class="{ 'border-danger': errors['use_serial_number'] }" :placeholder="t('views.product.fields.use_serial_number')"
                    />
                  </VeeField>
                  <VeeErrorMessage name="use_serial_number" class="mt-2 text-danger" />
                </div>
                <!-- #endregion -->

                <!-- #region Has Expiry Date -->
                <div class="pb-4">
                  <FormLabel html-for="has_expiry_date" :class="{ 'text-danger': errors['has_expiry_date']} " class="pr-5">
                    {{ t('views.product.fields.has_expiry_date') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="has_expiry_date" :label="t('views.product.fields.has_expiry_date')">
                    <FormSwitch.Input id="has_expiry_date" v-model="productForm.data.has_expiry_date" v-bind="field" name="has_expiry_date" type="checkbox"
                      :class="{ 'border-danger': errors['has_expiry_date'] }" :placeholder="t('views.product.fields.has_expiry_date')"
                    />
                  </VeeField>
                  <VeeErrorMessage name="has_expiry_date" class="mt-2 text-danger" />
                </div>
                <!-- #endregion -->

                <!-- #region Remarks -->
                <div class="pb-4">
                  <FormLabel html-for="remarks" :class="{ 'text-danger': errors['remarks'] }">
                    {{ t('views.product.fields.remarks') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="remarks"
                    :label="t('views.product.fields.remarks')">
                    <FormTextarea id="remarks" v-model="productForm.data.remarks" v-bind="field" name="remarks" type="text"
                      :class="{ 'border-danger': errors['remarks'] }" :placeholder="t('views.product.fields.remarks')" rows="3" />
                  </VeeField>
                </div>
                <!-- #endregion -->

                <!-- #region Status -->
                <div class="pb-4">
                  <FormLabel html-for="status" :class="{ 'text-danger': errors['status'] }">
                    {{ t('views.product.fields.status') }}
                  </FormLabel>
                  <VeeField v-slot="{ field }" name="status" rules="required" :label="t('views.product.fields.status')">
                    <FormSelect id="status" v-model="productForm.data.status" v-bind="field" name="status"
                      :class="{ 'border-danger': errors['status'] }">
                      <option value="">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                    </FormSelect>
                  </VeeField>
                  <VeeErrorMessage name="status" class="mt-2 text-danger" />
                </div>
                <!-- #endregion -->
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
