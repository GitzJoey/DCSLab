<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.product.table.title')" :data="productList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
           <template v-slot:table="tableProps">
                <table class="table table-report -mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.product.table.cols.code') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.product.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.product.table.cols.brand') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.product.table.cols.status') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data">
                            <tr class="intro-x">
                                <td>{{ item.code }}</td>
                                <td><a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse">{{ item.name }}</a></td>
                                <td>{{ item.brand !== null ? item.brand.name : '' }}</td>
                                <td>
                                    <CheckCircleIcon v-if="item.status === 1" />
                                    <XIcon v-if="item.status === 0" />
                                </td>
                                <td class="table-report__action w-56">
                                    <div class="flex justify-center items-center">
                                        <a class="flex items-center mr-3" href="" @click.prevent="showSelected(itemIdx)">
                                            <InfoIcon class="w-4 h-4 mr-1" />
                                            {{ t('components.data-list.view') }}
                                        </a>
                                        <a class="flex items-center mr-3" href="" @click.prevent="editSelected(itemIdx)">
                                            <CheckSquareIcon class="w-4 h-4 mr-1" />
                                            {{ t('components.data-list.edit') }}
                                        </a>
                                        <a class="flex items-center text-danger" href="javascript:;" @click="deleteSelected(itemIdx)">
                                            <Trash2Icon class="w-4 h-4 mr-1" /> {{ t('components.data-list.delete') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr :class="{'intro-x':true, 'hidden transition-all': expandDetail !== itemIdx}">
                                <td colspan="5">
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.code') }}</div>
                                        <div class="flex-1">{{ item.code }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.product_group_id') }}</div>
                                        <div class="flex-1">{{ item.product_group ? item.product_group.name : '' }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.brand_id') }}</div>
                                        <div class="flex-1">{{ item.brand.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.name') }}</div>
                                        <div class="flex-1">{{ item.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.product_type') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.product_type === 1">{{ t('components.dropdown.values.productTypeDDL.raw') }}</span>
                                            <span v-if="item.product_type === 2">{{ t('components.dropdown.values.productTypeDDL.wip') }}</span>
                                            <span v-if="item.product_type === 3">{{ t('components.dropdown.values.productTypeDDL.fg') }}</span>
                                            <span v-if="item.product_type === 4">{{ t('components.dropdown.values.productTypeDDL.svc') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.taxable_supply') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.taxable_supply">{{ t('components.dropdown.values.yesNoDDL.yes') }}</span>
                                            <span v-else>{{ t('components.dropdown.values.yesNoDDL.no') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.standard_rated_supply') }}</div>
                                        <div class="flex-1">{{ item.standard_rated_supply }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.units.title') }}</div>
                                        <table>
                                            <tbody>
                                                <tr v-for="(subItem, subItemIdx) in item.product_units">
                                                    <td>{{ subItem.code }}</td>
                                                    <td>{{ subItem.unit.name }}</td>
                                                    <td>{{ subItem.conversion_value }}</td>
                                                    <td>
                                                        <span v-if="subItem.is_base === 1">{{ t('components.dropdown.values.yesNoDDL.yes') }}</span>
                                                        <span v-if="subItem.is_base === 0">{{ t('components.dropdown.values.yesNoDDL.no') }}</span>
                                                    </td>
                                                    <td>
                                                        <span v-if="subItem.is_primary_unit === 1">{{ t('components.dropdown.values.yesNoDDL.yes') }}</span>
                                                        <span v-if="subItem.is_primary_unit === 0">{{ t('components.dropdown.values.yesNoDDL.no') }}</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.price_include_vat') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.price_include_vat">{{ t('components.dropdown.values.yesNoDDL.yes') }}</span>
                                            <span v-else>{{ t('components.dropdown.values.yesNoDDL.no') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.point') }}</div>
                                        <div class="flex-1">{{ item.point }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.use_serial_number') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.use_serial_number">{{ t('components.dropdown.values.yesNoDDL.yes') }}</span>
                                            <span v-else>{{ t('components.dropdown.values.yesNoDDL.no') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.has_expiry_date') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.has_expiry_date">{{ t('components.dropdown.values.yesNoDDL.yes') }}</span>
                                            <span v-else>{{ t('components.dropdown.values.yesNoDDL.no') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.status') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.status === 1">{{ t('components.dropdown.values.statusDDL.active') }}</span>
                                            <span v-if="item.status === 0">{{ t('components.dropdown.values.statusDDL.inactive') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.remarks') }}</div>
                                        <div class="flex-1">{{ item.Remarks }}</div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <Modal :show="deleteModalShow" @hidden="deleteModalShow = false">
                    <ModalBody class="p-0">
                        <div class="p-5 text-center">
                            <XCircleIcon class="w-16 h-16 text-danger mx-auto mt-3" />
                            <div class="text-3xl mt-5">{{ t('components.data-list.delete_confirmation.title') }}</div>
                            <div class="text-slate-600 mt-2">
                                {{ t('components.data-list.delete_confirmation.desc_1') }}<br />{{ t('components.data-list.delete_confirmation.desc_2') }}
                            </div>
                        </div>
                        <div class="px-5 pb-8 text-center">
                            <button type="button" class="btn btn-outline-secondary w-24 mr-1" @click="deleteModalShow = false">
                                {{ t('components.buttons.cancel') }}
                            </button>
                            <button type="button" class="btn btn-danger w-24" @click="confirmDelete">{{ t('components.buttons.delete') }}</button>
                        </div>
                    </ModalBody>
                </Modal>
            </template>
        </DataList>
    </div>

    <div class="intro-y box" v-if="mode !== 'list'">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.product.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.product.actions.edit') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="productForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <div class="p-5">
                    <div class="mb-3">
                        <label for="inputCode" class="form-label">{{ t('views.product.fields.code') }}</label>
                        <div class="flex items-center">
                            <VeeField id="inputCode" name="code" type="text" :class="{'form-control':true, 'border-danger': errors['code']}" :placeholder="t('views.product.fields.code')" :label="t('views.product.fields.code')" rules="required" @blur="reValidate(errors)" v-model="product.code" :readonly="product.code === '[AUTO]'" />
                            <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                        </div>
                        <ErrorMessage name="code" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="product_group_id">{{ t('views.product.fields.product_group_id') }}</label>
                        <select id="product_group_id" name="product_group_id" class="form-control form-select" v-model="product.product_group.hId">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option :value="g.hId" v-for="g in productGroupDDL" v-bind:key="g.hId">{{ g.code }} - {{ g.name }}</option>
                        </select>
                        <ErrorMessage name="product_group_id" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="brand_id">{{ t('views.product.fields.brand_id') }}</label>
                        <VeeField as="select" id="brand_id" name="brand_id" :class="{'form-control form-select':true, 'border-danger': errors['brand_id']}" v-model="product.brand.hId" :label="t('views.product.fields.brand_id')" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option :value="b.hId" v-for="b in brandDDL" v-bind:key="b.hId">{{ b.code }} - {{ b.name }}</option>
                        </VeeField>
                        <ErrorMessage name="brand_id" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputName" class="form-label">{{ t('views.product.fields.name') }}</label>
                        <VeeField id="inputName" name="name" type="text" :class="{'form-control':true, 'border-danger': errors['name']}" :placeholder="t('views.product.fields.name')" :label="t('views.product.fields.name')" rules="required" @blur="reValidate(errors)" v-model="product.name" />
                        <ErrorMessage name="name" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="product_type" class="form-label">{{ t('views.product.fields.product_type') }}</label>
                        <VeeField as="select" id="product_type" name="product_type" :class="{'form-control form-select':true, 'border-danger': errors['product_type']}" v-model="product.product_type" :label="t('views.product.fields.product_type')" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option :value="pt.code" v-for="pt in productTypeDDL" v-bind:key="pt.code">{{ t(pt.name) }}</option>
                        </VeeField>
                        <ErrorMessage name="product_type" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputTaxableSupply" class="form-label">{{ t('views.product.fields.taxable_supply') }}</label>
                        <div class="form-switch mt-2">
                            <input id="inputTaxableSupply" type="checkbox" class="form-check-input" name="taxable_supply" v-model="product.taxable_supply" :true-value="1" :false-value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="inputStandardRatedSupply" class="form-label">{{ t('views.product.fields.standard_rated_supply') }}</label>
                        <VeeField id="inputStandardRatedSupply" name="standard_rated_supply" type="text" :class="{'form-control':true, 'border-danger': errors['standard_rated_supply']}" :placeholder="t('views.product.fields.standard_rated_supply')" :label="t('views.product.fields.standard_rated_supply')" rules="required|numeric|max:100" @blur="reValidate(errors)" v-model="product.standard_rated_supply" />
                        <ErrorMessage name="standard_rated_supply" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputUnit" class="form-label">{{ t('views.product.fields.units.title') }}</label>
                        <div class="grid grid-cols-9 mb-3 bg-gray-700 dark:bg-dark-1 gap-2">
                            <div class="text-white p-3 font-bold col-span-2">{{ t('views.product.fields.units.table.cols.code') }}</div>
                            <div class="text-white p-3 font-bold col-span-2">{{ t('views.product.fields.units.table.cols.unit') }}</div>
                            <div class="text-white p-3 font-bold col-span-2 text-right">{{ t('views.product.fields.units.table.cols.conversion_value') }}</div>
                            <div class="flex justify-center text-white p-3 font-bold">{{ t('views.product.fields.units.table.cols.is_base') }}</div>
                            <div class="flex justify-center text-white p-3 font-bold">{{ t('views.product.fields.units.table.cols.is_primary') }}</div>
                            <div class="text-white p-3 font-bold"></div>
                        </div>
                        <div class="grid grid-cols-9 gap-2 mb-2" v-for="(pu, puIdx) in product.product_units">
                            <div class="col-span-2">
                                <input type="hidden" :name="'product_units_hId[' + puIdx + ']'" v-model="pu.hId" />
                                <div class="flex items-center">
                                    <VeeField type="text" :class="{'form-control': true, 'border-danger':errors['product_units_code[' + puIdx + ']']|errors['product_units_code.' + puIdx]}" v-model="pu.code" id="product_units_code" :name="'product_units_code[' + puIdx + ']'" :label="t('views.product.fields.units.table.cols.code') + ' ' + (puIdx+1)" rules="required" @blur="reValidate(errors)" :readonly="mode === 'create' && pu.code === '[AUTO]'" />
                                    <button type="button" class="btn btn-secondary mx-1" @click="generateCodeUnit(puIdx)" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                                </div>
                                <ErrorMessage :name="'product_units_code[' + puIdx + ']'" class="text-danger" />
                                <ErrorMessage :name="'product_units_code.' + puIdx" class="text-danger" />
                            </div>
                            <div class="col-span-2">
                                <VeeField as="select" :class="{'form-control form-select':true, 'border-danger':errors['unit_id[' + puIdx + ']']|errors['unit_id.' + puIdx]}" id="unit_id" :name="'unit_id[' + puIdx + ']'" :label="t('views.product.fields.units.table.cols.unit') + ' ' + (puIdx+1)" rules="required" @blur="reValidate(errors)" v-model="pu.unit.hId">
                                    <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                    <option :value="u.hId" v-for="u in unitDDL" v-bind:key="u.hId">{{ u.description }}</option>
                                </VeeField>
                                <ErrorMessage :name="'unit_id[' + puIdx + ']'" class="text-danger" />
                                <ErrorMessage :name="'unit_id.' + puIdx" class="text-danger" />
                            </div>
                            <div class="col-span-2">
                                <VeeField type="text" :class="{'form-control text-right':true, 'border-danger':errors['conv_value[' + puIdx +']']|errors['conv_value.' + puIdx]}" v-model="pu.conversion_value" id="conv_value" :name="'conv_value[' + puIdx +']'" :label="t('views.product.fields.units.table.cols.conversion_value') + ' ' + (puIdx+1)" rules="required" @focus="$event.target.select()" :readonly="pu.is_base" />
                                <ErrorMessage :name="'conv_value[' + puIdx +']'" class="text-danger" />
                                <ErrorMessage :name="'conv_value.' + puIdx" class="text-danger" />
                            </div>
                            <div class="flex items-center justify-center">
                                <input id="inputIsBase" class="form-check-input" type="checkbox" v-model="pu.is_base" :true-value="1" :false-value="0" @click="changeIsBase(puIdx)"> 
                                <input type="hidden" v-model="pu.is_base" :name="'is_base[' + puIdx + ']'" />
                            </div>
                            <div class="flex items-center justify-center">
                                <input id="inputIsPrimary" class="form-check-input" type="checkbox" v-model="pu.is_primary_unit" :true-value="1" :false-value="0" @click="changeIsPrimary(puIdx)">
                                <input type="hidden" v-model="pu.is_primary_unit" :name="'is_primary_unit[' + puIdx + ']'" />
                            </div>
                            <div class="flex items-center justify-center">
                                <button class="btn btn-sm btn-secondary" v-if="puIdx !== 0" @click.prevent="deleteUnitSelected(puIdx)"><TrashIcon class="w-3 h-4" /></button>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-secondary w-24" @click.prevent="createNewUnit"><PlusIcon class="w-3 h-4" /></button>
                    </div>
                    <div class="mb-3">
                        <label for="inputPoint" class="form-label">{{ t('views.product.fields.point') }}</label>
                        <VeeField id="inputPoint" name="point" type="text" :class="{'form-control':true, 'border-danger': errors['point']}" :placeholder="t('views.product.fields.point')" :label="t('views.product.fields.point')" rules="required|numeric|max_value:1000" v-model="product.point" />
                        <ErrorMessage name="point" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputUseSerialNumber" class="form-label">{{ t('views.product.fields.use_serial_number') }}</label>
                        <div class="form-switch mt-2">
                            <input id="inputUseSerialNumber" type="checkbox" class="form-check-input" name="use_serial_number" v-model="product.use_serial_number" :true-value="1" :false-value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="inputHasExpiryDate" class="form-label">{{ t('views.product.fields.has_expiry_date') }}</label>
                        <div class="form-switch mt-2">
                            <input id="inputHasExpiryDate" type="checkbox" class="form-check-input" name="has_expiry_date" v-model="product.has_expiry_date" :true-value="1" :false-value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="inputPriceIncludeVAT" class="form-label">{{ t('views.product.fields.price_include_vat') }}</label>
                        <div class="form-switch mt-2">
                            <input id="inputPriceIncludeVAT" type="checkbox" class="form-check-input" name="price_include_vat" v-model="product.price_include_vat" :true-value="1" :false-value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">{{ t('views.product.fields.status') }}</label>
                        <VeeField as="select" id="status" name="status" :class="{'form-control form-select':true, 'border-danger': errors['status']}" v-model="product.status" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </VeeField>
                        <ErrorMessage name="status" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputRemarks" class="form-label">{{ t('views.product.fields.remarks') }}</label>
                        <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.product.fields.remarks')" v-model="product.remarks" rows="3"></textarea>
                    </div>
                </div>
                <div class="pl-5" v-if="mode === 'create' || mode === 'edit'">
                    <button type="submit" class="btn btn-primary w-24 mr-3">{{ t('components.buttons.save') }}</button>
                    <button type="button" class="btn btn-secondary" @click="handleReset(); resetAlertErrors()">{{ t('components.buttons.reset') }}</button>
                </div>
            </VeeForm>
            <div class="loader-overlay" v-if="loading"></div>
        </div>
        <hr/>
        <div>
            <button type="button" class="btn btn-secondary w-15 m-3" @click="backToList">{{ t('components.buttons.back') }}</button>
        </div>
    </div>
</template>

<script setup>
//#region Imports
import { onMounted, ref, computed, watch } from "vue";
import axios from "@/axios";
import { useI18n } from "vue-i18n";
import { route } from "@/ziggy";
import dom from "@left4code/tw-starter/dist/js/dom";
import { useUserContextStore } from "@/stores/user-context";
import DataList from "@/global-components/data-list/Main";
import AlertPlaceholder from "@/global-components/alert-placeholder/Main";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - Pinia
const userContextStore = useUserContextStore();
const selectedUserCompany = computed(() => userContextStore.selectedUserCompany );
//#endregion

//#region Data - UI
const mode = ref('list');
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref('');
const deleteModalShow = ref(false);
const expandDetail = ref(null);
//#endregion

//#region Data - Views
const productList = ref({});
const product = ref({
    code: '',
    product_group: { hId: '' },
    brand: { 
        hId: '',
        name: '' 
    },
    name: '',
    product_units: [
        {
            hId: '',
            conversion_value: 0,
            unit: { hId: '' }
        }
    ],
    taxable_supply: false,
    standard_rated_supply: 0,
    price_include_vat: false,
    remarks: '',
    point: 0,
    is_use_serial: false,
    has_expiry_date: false,
    product_type: '',
    status: 1,
});
const statusDDL = ref([]);
const productGroupDDL = ref([]);
const brandDDL = ref([]);
const unitDDL = ref([]);
const productTypeDDL = ref([]);
//#endregion

//#region onMounted
onMounted(() => {
    if (selectedUserCompany.value !== '') {
        getAllProducts({ page: 1 });
        getDDLSync();
    } else  {
        
    }

    getDDL();

    loading.value = false;
});
//#endregion

//#region Methods
function getAllProducts(args) {
    productList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    let companyId = selectedUserCompany.value;

    axios.get(route('api.get.db.product.product.read', { "companyId": companyId, "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        productList.value = response.data;
        loading.value = false;
    });
}

function getDDL() {
    axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
        statusDDL.value = response.data;
    });

    axios.get(route('api.get.db.product.common.list.product_type', {
            type: 'products'
        })).then(response => {
            productTypeDDL.value = response.data;
    });
}

function getDDLSync() {
    axios.get(route('api.get.db.product.brand.read', {
            companyId: selectedUserCompany.value,
            paginate: false
        })).then(response => {
            brandDDL.value = response.data;
    });

    axios.get(route('api.get.db.product.product_group.read', {
            companyId: selectedUserCompany.value,
            paginate: false
        })).then(response => {
            productGroupDDL.value = response.data;
    });

    axios.get(route('api.get.db.product.unit.read', {
            companyId: selectedUserCompany.value,
            category: 1,
            paginate: false
        })).then(response => {
            unitDDL.value = response.data;
    });
}

function onSubmit(values, actions) {
    loading.value = true;

    var formData = new FormData(dom('#productForm')[0]); 
    formData.append('company_id', selectedUserCompany.value);
    
    if (mode.value === 'create') {
        axios.post(route('api.post.db.product.product.save'), formData).then(response => {
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === 'edit') {
        axios.post(route('api.post.db.product.product.edit', product.value.hId), formData).then(response => {
            actions.resetForm();
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else { }
}

function handleError(e, actions) {
    //Laravel Validations
    if (e.response.data.errors !== undefined && Object.keys(e.response.data.errors).length > 0) {
        for (var key in e.response.data.errors) {
            for (var i = 0; i < e.response.data.errors[key].length; i++) {
                actions.setFieldError(key, e.response.data.errors[key][i]);
            }
        }
        alertErrors.value = e.response.data.errors;
    } else {
        //Catch From Controller
        alertErrors.value = {
            controller: e.response.status + ' ' + e.response.statusText +': ' + e.response.data.message
        };
    }
}

function invalidSubmit(e) {
    alertErrors.value = e.errors;
}

function reValidate(errors) {
    alertErrors.value = errors;
}

function emptyProduct() {
    return {
        code: '[AUTO]',
        product_group: { hId: '' },
        brand: { 
            hId: '',
            name: '' 
        },
        name: '',
        product_units: [
            {
                hId: '',
                code: '[AUTO]',
                is_base: 1,
                conversion_value: 1,
                is_primary_unit: 1,
                unit: { hId: '' }
            }
        ],
        taxable_supply: false,
        standard_rated_supply: 0,
        price_include_vat: false,
        remarks: '',
        point: 0,
        use_serial_number: false,
        has_expiry_date: false,
        product_type: '',
        status: 1,
    }
}

function resetAlertErrors() {
    alertErrors.value = [];
}

function createNew() {
    mode.value = 'create';
    product.value = emptyProduct();
}

function createNewUnit() {
    let product_unit = {
        hId: '',
        code: '[AUTO]',
        conversion_value: 0,
        is_base: 0,
        is_primary_unit: 0,
        unit: { hId: '' }
    };

    product.value.product_units.push(product_unit);
}

function onDataListChange({page, pageSize, search}) {
    getAllProducts({page, pageSize, search});
}

function editSelected(index) {
    mode.value = 'edit';
    product.value = productList.value.data[index];

    if (product.value.product_group === null) {
        product.value.product_group = {
            hId: ''
        };
    }
}

function deleteSelected(index) {
    deleteId.value = productList.value.data[index].hId;
    deleteModalShow.value = true;
}

function deleteUnitSelected(index) {
    product.value.product_units.splice(index, 1);
}

function confirmDelete() {
    deleteModalShow.value = false;
    axios.post(route('api.post.db.product.product.delete', deleteId.value)).then(response => {
        backToList();
    }).catch(e => {
        alertErrors.value = e.response.data;
    }).finally(() => {

    });
}

function showSelected(index) {
    toggleDetail(index);
}

function backToList() {
    resetAlertErrors();
    mode.value = 'list';
    getAllProducts({ page: productList.value.current_page, pageSize: productList.value.per_page });
}

function toggleDetail(idx) {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

function generateCode() {
    if (product.value.code === '[AUTO]') product.value.code = '';
    else  product.value.code = '[AUTO]'
}

function generateCodeUnit(idx) {
    if (product.value.product_units[idx].code === '[AUTO]') product.value.product_units[idx].code = '';
    else  product.value.product_units[idx].code = '[AUTO]'
}

function changeIsBase(idx) {
    let checked_state = product.value.product_units[idx].is_base === 1 ? true:false;
    
    if (!checked_state) {
        for (let i = 0; i < product.value.product_units.length; i++) {
            if (i === idx) {
                product.value.product_units[i].conversion_value = 1;
            }
            product.value.product_units[i].is_base = 0;
        }
    } else {

    }
}

function changeIsPrimary(idx) {
    let checked_state = product.value.product_units[idx].is_primary_unit === 1 ? true:false;

    if (!checked_state) {
        for (let i = 0; i < product.value.product_units.length; i++) {
            if (i === idx) continue;
            product.value.product_units[i].is_primary_unit = 0;
        }
    } else {
        
    }
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value !== '') {
        getAllProducts({ page: 1 });
        getDDLSync();
    }
});
//#endregion
</script>