<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.supplier.table.title')" :data="supplierList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
            <template v-slot:table="tableProps">
                <table class="table table-report -mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.supplier.table.cols.code') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.supplier.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.supplier.table.cols.poc') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.supplier.table.cols.status') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data">
                            <tr class="intro-x">
                                <td>{{ item.code }}</td>
                                <td><a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse">{{ item.name }}</a></td>
                                <td>{{ item.supplier_poc.name }}</td>
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
                                        <a class="flex items-center text-danger" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal" @click="deleteSelected(itemIdx)">
                                            <Trash2Icon class="w-4 h-4 mr-1" /> {{ t('components.data-list.delete') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr :class="{'intro-x':true, 'hidden transition-all': expandDetail !== itemIdx}">
                                <td colspan="5">
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.supplier.fields.code') }}</div>
                                        <div class="flex-1">{{ item.code }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.supplier.fields.name') }}</div>
                                        <div class="flex-1">{{ item.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.supplier.fields.address') }}</div>
                                        <div class="flex-1">{{ item.address }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.supplier.fields.city') }}</div>
                                        <div class="flex-1">{{ item.city }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.supplier.fields.taxable_enterprise') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.taxable_enterprise">{{ t('components.dropdown.values.yesNoDDL.yes') }}</span>
                                            <span v-else>{{ t('components.dropdown.values.yesNoDDL.no') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.supplier.fields.payment_term_type') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.payment_term_type === 'PIA'">{{ t('components.dropdown.values.paymentTermTypeDDL.pia') }}</span>
                                            <span v-if="item.payment_term_type === 'NET'">{{ t('components.dropdown.values.paymentTermTypeDDL.net') }}</span>
                                            <span v-if="item.payment_term_type === 'EOM'">{{ t('components.dropdown.values.paymentTermTypeDDL.eom') }}</span>
                                            <span v-if="item.payment_term_type === 'COD'">{{ t('components.dropdown.values.paymentTermTypeDDL.cod') }}</span>
                                            <span v-if="item.payment_term_type === 'CND'">{{ t('components.dropdown.values.paymentTermTypeDDL.cnd') }}</span>
                                            <span v-if="item.payment_term_type === 'CBS'">{{ t('components.dropdown.values.paymentTermTypeDDL.cbs') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.supplier.fields.payment_term') }}</div>
                                        <div class="flex-1">{{ item.payment_term }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.supplier.fields.tax_id') }}</div>
                                        <div class="flex-1">{{ item.tax_id }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.supplier.fields.remarks') }}</div>
                                        <div class="flex-1">{{ item.remarks }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.supplier.fields.status') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.status === 1">{{ t('components.dropdown.values.statusDDL.active') }}</span>
                                            <span v-if="item.status === 0">{{ t('components.dropdown.values.statusDDL.inactive') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.supplier.fields.poc.label') }}</div>
                                        <div class="flex-1">{{ item.supplier_poc.name }} - {{ item.supplier_poc.email }}</div>
                                    </div>                                    
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div id="delete-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <div class="p-5 text-center">
                                    <XCircleIcon class="w-16 h-16 text-danger mx-auto mt-3" />
                                    <div class="text-3xl mt-5">{{ t('components.data-list.delete_confirmation.title') }}</div>
                                    <div class="text-gray-600 mt-2">
                                        {{ t('components.data-list.delete_confirmation.desc_1') }}<br />{{ t('components.data-list.delete_confirmation.desc_2') }}
                                    </div>
                                </div>
                                <div class="px-5 pb-8 text-center">
                                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">
                                        {{ t('components.buttons.cancel') }}
                                    </button>
                                    <button type="button" data-tw-dismiss="modal" class="btn btn-danger w-24" @click="confirmDelete">{{ t('components.buttons.delete') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </DataList>
    </div>

    <div class="intro-y box" v-if="mode !== 'list'">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.supplier.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.supplier.actions.edit') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="supplierForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <div class="nav nav-tabs flex-col sm:flex-row bg-gray-300 dark:bg-dark-2 text-gray-600" role="tablist">
                    <Tippy id="supplier-tab" tag="a" :content="t('views.supplier.tabs.supplier')" data-tw-toggle="tab" data-tw-target="#supplier" href="javascript:;" class="w-full sm:w-40 py-4 text-center flex justify-center items-center active" role="tab" aria-controls="supplier" aria-selected="true">
                        <span :class="{'text-danger':errors['code']||errors['name']|errors['payment_term_type']|errors['status']}">{{ t('views.supplier.tabs.supplier') }}</span>
                    </Tippy>
                    <Tippy id="poc-tab" tag="a" :content="t('views.supplier.tabs.poc')" data-tw-toggle="tab" data-tw-target="#poc" href="javascript:;" class="w-full sm:w-40 py-4 text-center flex justify-center items-center" role="tab" aria-controls="poc" aria-selected="false">
                        <span :class="{'text-danger':errors['poc_name']||errors['email']}">{{ t('views.supplier.tabs.poc') }}</span>
                    </Tippy>
                    <Tippy id="products-tab" tag="a" :content="t('views.supplier.tabs.products')" data-tw-toggle="tab" data-tw-target="#products" href="javascript:;" class="w-full sm:w-40 py-4 text-center flex justify-center items-center" role="tab" aria-controls="products" aria-selected="false">
                        {{ t('views.supplier.tabs.products') }}
                    </Tippy>
                </div>
                <div class="tab-content">
                    <div id="supplier" class="tab-pane p-5 active" role="tabpanel" aria-labelledby="supplier-tab">
                        <div class="mb-3">
                            <label for="inputCode" class="form-label">{{ t('views.supplier.fields.code') }}</label>
                            <div class="flex items-center">
                                <VeeField id="inputCode" name="code" type="text" :class="{'form-control':true, 'border-danger': errors['code']}" :placeholder="t('views.supplier.fields.code')" :label="t('views.supplier.fields.code')" rules="required" @blur="reValidate(errors)" v-model="supplier.code" :readonly="mode === 'create' && supplier.code === '[AUTO]'" />
                                <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                            </div>
                            <ErrorMessage name="code" class="text-danger" />
                        </div>
                        <div class="mb-3">
                            <label for="inputName" class="form-label">{{ t('views.supplier.fields.name') }}</label>
                            <VeeField id="inputName" name="name" type="text" :class="{'form-control':true, 'border-danger': errors['name']}" :placeholder="t('views.supplier.fields.name')" :label="t('views.supplier.fields.name')" rules="required" @blur="reValidate(errors)" v-model="supplier.name" />
                            <ErrorMessage name="name" class="text-danger" />
                        </div>
                        <div class="mb-3">
                            <label for="inputAddress" class="form-label">{{ t('views.supplier.fields.address') }}</label>
                            <textarea id="inputAddress" name="address" type="text" class="form-control" :placeholder="t('views.supplier.fields.address')" v-model="supplier.address" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="inputContact" class="form-label">{{ t('views.supplier.fields.contact') }}</label>
                            <input id="inputContact" name="contact" type="text" class="form-control" :placeholder="t('views.supplier.fields.contact')" v-model="supplier.contact" />
                        </div>
                        <div class="mb-3">
                            <label for="inputCity" class="form-label">{{ t('views.supplier.fields.city') }}</label>
                            <input id="inputCity" name="city" type="text" class="form-control" :placeholder="t('views.supplier.fields.city')" v-model="supplier.city" />
                        </div>
                        <div class="mb-3">
                            <label for="inputTaxableEnterprise">{{ t('views.supplier.fields.taxable_enterprise') }}</label>
                            <div class="form-switch mt-2">
                                <input id="inputTaxableEnterprise" type="checkbox" class="form-check-input" name="taxable_enterprise" v-model="supplier.taxable_enterprise" :true-value="1" :false-value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="inputTaxId" class="form-label">{{ t('views.supplier.fields.tax_id') }}</label>
                            <VeeField id="inputTaxId" name="tax_id" type="text" :class="{'form-control':true, 'border-danger': errors['tax_id']}" rules="required" :placeholder="t('views.supplier.fields.tax_id')" :label="t('views.supplier.fields.tax_id')" @blur="reValidate(errors)" v-model="supplier.tax_id" />
                            <ErrorMessage name="tax_id" class="text-danger" />
                        </div>                        
                        <div class="mb-3">
                            <label for="inputPaymnetTermType" class="form-label">{{ t('views.supplier.fields.payment_term_type') }}</label>
                            <VeeField as="select" :class="{'form-control form-select':true, 'border-danger':errors['payment_term_type']}" id="inputPaymnetTermType" name="payment_term_type" :label="t('views.supplier.fields.payment_term_type')" rules="required" @blur="reValidate(errors)" v-model="supplier.payment_term_type">
                                <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                <option v-for="c in paymentTermDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                            </VeeField>
                            <ErrorMessage name="payment_term_type" class="text-danger" />
                        </div>
                        <div class="mb-3">
                            <label for="inputPaymentTerm">{{ t('views.supplier.fields.payment_term') }}</label>
                            <div class="mt-2">
                                <VeeField id="inputPaymentTerm" type="text" rules="required|numeric|max:365" class="form-control" name="payment_term" v-model="supplier.payment_term" />
                                <ErrorMessage name="payment_term_type" class="text-danger" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="inputStatus" class="form-label">{{ t('views.supplier.fields.status') }}</label>
                            <VeeField as="select" :class="{'form-control form-select':true, 'border-danger':errors['status']}" id="inputStatus" name="status" :label="t('views.supplier.fields.status')" rules="required" @blur="reValidate(errors)" v-model="supplier.status">
                                <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                            </VeeField>
                            <ErrorMessage name="status" class="text-danger" />
                        </div>
                        <div class="mb-3">
                            <label for="inputRemarks" class="form-label">{{ t('views.supplier.fields.remarks') }}</label>
                            <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.supplier.fields.remarks')" v-model="supplier.remarks" rows="3"></textarea>
                        </div>
                    </div>
                    <div id="poc" class="tab-pane p-5" role="tabpanel" aria-labelledby="poc-tab">
                        <div class="mb-3">
                            <label for="inputPOCName" class="form-label">{{ t('views.supplier.fields.poc.name') }}</label>
                            <VeeField id="inputPOCName" name="poc_name" type="text" :class="{'form-control':true, 'border-danger': errors['poc_name']}" :placeholder="t('views.supplier.fields.poc.name')" :label="t('views.supplier.fields.poc.name')" rules="required" @blur="reValidate(errors)" v-model="supplier.supplier_poc.name" />
                            <ErrorMessage name="poc_name" class="text-danger" />
                        </div>
                        <div class="mb-3">
                            <label for="inputEmail" class="form-label">{{ t('views.supplier.fields.poc.email') }}</label>
                            <VeeField id="inputEmail" name="email" type="text" :class="{'form-control':true, 'border-danger': errors['email']}" :placeholder="t('views.supplier.fields.poc.email')" :label="t('views.supplier.fields.poc.email')" rules="required|email" @blur="reValidate(errors)" v-model="supplier.supplier_poc.email" :readonly="mode === 'edit'" />
                            <ErrorMessage name="email" class="text-danger" />
                        </div>
                    </div>
                    <div id="products" class="tab-pane p-5" role="tabpanel" aria-labelledby="products-tab">
                        <div class="mb-3">
                            <label for="inputProductLists" class="form-label">{{ t('views.supplier.fields.products.product_lists') }}</label>                            
                            <table class="table table--sm">
                                <thead>
                                    <tr>
                                        <th>{{ t('views.supplier.fields.products.table.cols.selected') }}</th>
                                        <th>{{ t('views.supplier.fields.products.table.cols.main_product') }}</th>
                                        <th>{{ t('views.supplier.fields.products.table.cols.product_name') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(p, pIdx) in productLists">
                                        <td class="border-b dark:border-dark-5">
                                            <div class="form-switch">
                                                <input :id="'inputProduct_' + p.hId" type="checkbox" name="productIds[]" v-model="supplier.selected_products" :value="p.hId" class="form-check-input">
                                            </div>
                                        </td>
                                        <td class="border-b dark:border-dark-5">
                                            <div class="form-switch">
                                                <input :id="'inputMainProduct_' + p.hId" type="checkbox" name="mainProducts[]" v-model="supplier.main_products" :value="p.hId" class="form-check-input">
                                            </div>
                                        </td>
                                        <td>
                                            {{ p.name }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
import { inject, onMounted, ref, computed, watch } from "vue";
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
const expandDetail = ref(null);
//#endregion

//#region Data - Views
const supplierList = ref([]);
const supplier = ref({
    code: '',
    name: '',
    term: '',
    contact: '',
    address: '',
    supplier_poc: {
        hId: '',
        profile: {
            first_name: ''
        }
    },
    city: '',
    taxable_enterprise: 1,
    tax_id: '',
    remarks: '',
    supplier_products: [],
    payment_term_type: '',
    payment_term: 0,
    selected_products: [],
    main_products: [],
    status: 1,
});
const statusDDL = ref([]);
const paymentTermDDL = ref([]);
const productLists = ref([]);
//#endregion

//#region onMounted
onMounted(() => {
    if (selectedUserCompany.value !== '') {
        getAllSupplier({ page: 1});
        getDDLSync();
    } else  {
        
    }

    getDDL();

    loading.value = false;
});
//#endregion

//#region Methods
function getAllSupplier(args) {
    supplierList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    let companyId = selectedUserCompany.value;

    axios.get(route('api.get.db.supplier.supplier.read', { "companyId": companyId, "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        supplierList.value = response.data;
        loading.value = false;
    });
}

function getDDL() {
    axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
        statusDDL.value = response.data;
    });

    axios.get(route('api.get.db.supplier.common.list.payment_term')).then(response => {
        paymentTermDDL.value = response.data;
    });
}

function getDDLSync() {
    axios.get(route('api.get.db.product.product.read', { "companyId": selectedUserCompany.value, "paginate": false })).then(response => {
        productLists.value = response.data;
    });
}

function onSubmit(values, actions) {
    loading.value = true;

    var formData = new FormData(dom('#supplierForm')[0]); 
    formData.append('company_id', selectedUserCompany.value);

    if (mode.value === 'create') {
        axios.post(route('api.post.db.supplier.supplier.save'), formData).then(response => {
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === 'edit') {
        axios.post(route('api.post.db.supplier.supplier.edit', supplier.value.hId), formData).then(response => {
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

function emptySupplier() {
    return {
        code: '[AUTO]',
        name: '',
        term: '',
        contact: '',
        address: '',
        city: '',
        supplier_poc: {
            hId: '',
            profile: {
                first_name: ''
            }
        },
        supplier_products: [],
        taxable_enterprise: 1,
        tax_id: '',
        remarks: '',
        payment_term_type: '',
        payment_term: 0,
        selected_products: [],
        main_products: [],
        status: 1,
    }
}

function resetAlertErrors() {
    alertErrors.value = [];
}

function createNew() {
    mode.value = 'create';
    supplier.value = emptySupplier();
}

function onDataListChange({page, pageSize, search}) {
    getAllSupplier({page, pageSize, search});
}

function editSelected(index) {
    mode.value = 'edit';
    supplier.value = supplierList.value.data[index];
}

function deleteSelected(index) {
    deleteId.value = supplierList.value.data[index].hId;
}

function confirmDelete() {
    axios.post(route('api.post.db.supplier.supplier.delete', deleteId.value)).then(response => {
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
    getAllSupplier({ page: supplierList.value.current_page, pageSize: supplierList.value.per_page });
}

function toggleDetail(idx) {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

function generateCode() {
    if (supplier.value.code === '[AUTO]') supplier.value.code = '';
    else  supplier.value.code = '[AUTO]'
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value !== '') {
        getAllSupplier({ page: 1 });
        getDDLSync();
    }
});

watch(computed(() => supplier.value.main_products), () => {
    if (supplier.value.main_products.length != 0) {
        _.forEach(supplier.value.main_products, function(val) {
            if (_.findIndex(supplier.value.selected_products, (item) => { return item === val }) === -1) {
                supplier.value.selected_products.push(val);
            }
        });
    }
});

watch(computed(() => supplier.value.selected_products), (n, o) => {
    if (supplier.value.main_products.length != 0) {
        _.forEach(supplier.value.main_products, function(val) {
            if (_.findIndex(supplier.value.selected_products, (item) => { return item === val }) === -1) {
                if (_.findIndex(supplier.value.main_products, (item) => { return item === val }) > -1) {
                    supplier.value.main_products.splice(_.findIndex(supplier.value.main_products, (item) => { return item === val }), 1);
                }
            }
        });
    }
});
//#endregion
</script>
