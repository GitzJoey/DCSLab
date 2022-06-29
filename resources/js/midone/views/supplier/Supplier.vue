<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.supplier.table.title')" :data="supplierList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
            <template v-slot:table="tableProps">
                <table class="table table-report -mt-2" aria-describedby="">
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
                                    <CheckCircleIcon v-if="item.status === 'ACTIVE'" />
                                    <XIcon v-if="item.status === 'INACTIVE'" />
                                </td>
                                <td class="table-report__action w-12">
                                    <div class="flex justify-center items-center">
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.view')" @click.prevent="showSelected(itemIdx)">
                                            <InfoIcon class="w-4 h-4" />
                                        </Tippy>
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.edit')" @click.prevent="editSelected(itemIdx)">
                                            <CheckSquareIcon class="w-4 h-4" />
                                        </Tippy>
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.delete')" @click.prevent="deleteSelected(itemIdx)">
                                            <Trash2Icon class="w-4 h-4 text-danger" />
                                        </Tippy>
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
                                            <span v-if="item.status === 'ACTIVE'">{{ t('components.dropdown.values.statusDDL.active') }}</span>
                                            <span v-if="item.status === 'INACTIVE'">{{ t('components.dropdown.values.statusDDL.inactive') }}</span>
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
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.supplier.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.supplier.actions.edit') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="supplierForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <ul class="nav nav-tabs" role="tablist">
                    <li id="tab-supplier" class="nav-item flex-1" role="presentation">
                        <button class="nav-link w-full py-2 active" data-tw-toggle="pill" data-tw-target="#tab-supplier-content" type="button" role="tab" aria-controls="tab-supplier-content" aria-selected="true">
                            <span :class="{'text-danger':errors['code']||errors['name']|errors['payment_term_type']|errors['status']}">{{ t('views.supplier.tabs.supplier') }}</span>
                        </button>
                    </li>
                    <li id="tab-poc" class="nav-item flex-1" role="presentation">
                        <button class="nav-link w-full py-2" data-tw-toggle="pill" data-tw-target="#tab-poc-content" type="button" role="tab" aria-controls="tab-poc-content" aria-selected="false">
                            <span :class="{'text-danger':errors['poc_name']||errors['email']}">{{ t('views.supplier.tabs.poc') }}</span>
                        </button>
                    </li>
                    <li id="tab-product" class="nav-item flex-1" role="presentation">
                        <button class="nav-link w-full py-2" data-tw-toggle="pill" data-tw-target="#tab-product-content" type="button" role="tab" aria-controls="tab-product-content" aria-selected="false">
                            {{ t('views.supplier.tabs.products') }}
                        </button>
                    </li>
                </ul>
                <div class="tab-content border-l border-r border-b">
                    <div id="tab-supplier-content" class="tab-pane leading-relaxed p-5 active" role="tabpanel" aria-labelledby="tab-supplier">
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
                            <label for="inputPaymentTermType" class="form-label">{{ t('views.supplier.fields.payment_term_type') }}</label>
                            <VeeField as="select" :class="{'form-control form-select':true, 'border-danger':errors['payment_term_type']}" id="inputPaymentTermType" name="payment_term_type" :label="t('views.supplier.fields.payment_term_type')" rules="required" @blur="reValidate(errors)" v-model="supplier.payment_term_type">
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
                    <div id="tab-poc-content" class="tab-pane leading-relaxed p-5" role="tabpanel" aria-labelledby="tab-poc">
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
                    <div id="tab-product-content" class="tab-pane leading-relaxed p-5" role="tabpanel" aria-labelledby="tab-product">
                        <div class="mb-3">
                            <label for="inputProductLists" class="form-label">{{ t('views.supplier.fields.products.product_lists') }}</label>                            
                            <table class="table table--sm" aria-describedby="">
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
                <div class="mt-8" v-if="mode === 'create' || mode === 'edit'">
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
import { onMounted, onUnmounted, ref, computed, watch } from "vue";
import axios from "@/axios";
import { useI18n } from "vue-i18n";
import { route } from "@/ziggy";
import dom from "@left4code/tw-starter/dist/js/dom";
import { useUserContextStore } from "@/stores/user-context";
import DataList from "@/global-components/data-list/Main";
import AlertPlaceholder from "@/global-components/alert-placeholder/Main";
import { getCachedDDL, setCachedDDL } from "@/mixins";
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
    status: 'ACTIVE',
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

    setMode();

    loading.value = false;
});

onUnmounted(() => {
    sessionStorage.removeItem('DCSLAB_LAST_ENTITY');
});
//#endregion

//#region Methods
const setMode = () => {
    if (sessionStorage.getItem('DCSLAB_LAST_ENTITY') !== null) createNew();
}

const getAllSupplier = (args) => {
    supplierList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    let companyId = selectedUserCompany.value;

    axios.get(route('api.get.db.supplier.supplier.list', { "companyId": companyId, "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        supplierList.value = response.data;
        loading.value = false;
    });
}

const getDDL = () => {
    if (getCachedDDL('statusDDL') == null) {
        axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
            statusDDL.value = response.data;
            setCachedDDL('statusDDL', response.data);
        });    
    } else {
        statusDDL.value = getCachedDDL('statusDDL');
    }

    if (getCachedDDL('api.get.db.supplier.common.list.payment_term') == null) {
        axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
            paymentTermDDL.value = response.data;
            setCachedDDL('paymentTermDDL', response.data);
        });    
    } else {
        paymentTermDDL.value = getCachedDDL('paymentTermDDL');
    }
}

const getDDLSync = () => {
    axios.get(route('api.get.db.product.product.list', { "companyId": selectedUserCompany.value, "paginate": false, "search": '' })).then(response => {
        productLists.value = response.data;
    });
}

const onSubmit = (values, actions) => {
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
        axios.post(route('api.post.db.supplier.supplier.edit', supplier.value.uuid), formData).then(response => {
            actions.resetForm();
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else { }
}

const handleError = (e, actions) => {
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

const invalidSubmit = (e) => {
    alertErrors.value = e.errors;
    if (dom('.border-danger').length !== 0) dom('.border-danger')[0].scrollIntoView({ behavior: "smooth" });
}

const reValidate = (errors) => {
    alertErrors.value = errors;
}

const emptySupplier = () => {
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
        status: 'ACTIVE',
    }
}

const resetAlertErrors = () => {
    alertErrors.value = [];
}

const createNew = () => {
    mode.value = 'create';
    
    if (sessionStorage.getItem('DCSLAB_LAST_ENTITY') !== null) {
        supplier.value = JSON.parse(sessionStorage.getItem('DCSLAB_LAST_ENTITY'));
        sessionStorage.removeItem('DCSLAB_LAST_ENTITY');
    } else {
        supplier.value = emptySupplier();
    }
}

const onDataListChange = ({page, pageSize, search}) => {
    getAllSupplier({page, pageSize, search});
}

const editSelected = (index) => {
    mode.value = 'edit';
    supplier.value = supplierList.value.data[index];
}

const deleteSelected = (index) => {
    deleteId.value = supplierList.value.data[index].uuid;
    deleteModalShow.value = true;
}

const confirmDelete = () => {
    deleteModalShow.value = false;
    axios.post(route('api.post.db.supplier.supplier.delete', deleteId.value)).then(response => {
        backToList();
    }).catch(e => {
        alertErrors.value = e.response.data;
    }).finally(() => {

    });
}

const showSelected = (index) => {
    toggleDetail(index);
}

const backToList = () => {
    resetAlertErrors();
    sessionStorage.removeItem('DCSLAB_LAST_ENTITY');

    mode.value = 'list';
    getAllSupplier({ page: supplierList.value.meta.current_page, pageSize: supplierList.value.meta.per_page });
}

const toggleDetail = (idx) => {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

const generateCode = () => {
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

watch(supplier, (newV) => {
    if (mode.value == 'create') sessionStorage.setItem('DCSLAB_LAST_ENTITY', JSON.stringify(newV));
}, { deep: true });
//#endregion
</script>
