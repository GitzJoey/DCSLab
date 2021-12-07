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
                                <td>{{ item.user.name }}</td>
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
                                        <a class="flex items-center text-theme-21" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal" @click="deleteSelected(itemIdx)">
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
                                            <span v-if="item.taxable_enterprise === 1">{{ t('components.dropdown.values.yesNoDDL.yes') }}</span>
                                            <span v-if="item.taxable_enterprise === 0">{{ t('components.dropdown.values.yesNoDDL.no') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.supplier.fields.payment_term_type') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.payment_term_type === 'PIA'">{{ t('components.dropdown.values.paymentTermTypeDDL.pia') }}</span>
                                            <span v-if="item.payment_term_type === 'NET30'">{{ t('components.dropdown.values.paymentTermTypeDDL.pia') }}</span>
                                            <span v-if="item.payment_term_type === 'EOM'">{{ t('components.dropdown.values.paymentTermTypeDDL.eom') }}</span>
                                            <span v-if="item.payment_term_type === 'COD'">{{ t('components.dropdown.values.paymentTermTypeDDL.cod') }}</span>
                                            <span v-if="item.payment_term_type === 'CND'">{{ t('components.dropdown.values.paymentTermTypeDDL.cnd') }}</span>
                                            <span v-if="item.payment_term_type === 'CBS'">{{ t('components.dropdown.values.paymentTermTypeDDL.cbs') }}</span>
                                        </div>
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
                                        <div class="flex-1">{{ item.user.name }} - {{ item.user.email }}</div>
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
                                    <XCircleIcon class="w-16 h-16 text-theme-21 mx-auto mt-3" />
                                    <div class="text-3xl mt-5">{{ t('components.data-list.delete_confirmation.title') }}</div>
                                    <div class="text-gray-600 mt-2">
                                        {{ t('components.data-list.delete_confirmation.desc_1') }}<br />{{ t('components.data-list.delete_confirmation.desc_2') }}
                                    </div>
                                </div>
                                <div class="px-5 pb-8 text-center">
                                    <button type="button" data-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">
                                        {{ t('components.buttons.cancel') }}
                                    </button>
                                    <button type="button" data-dismiss="modal" class="btn btn-danger w-24" @click="confirmDelete">{{ t('components.buttons.delete') }}</button>
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
            <VeeForm id="supplierForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
                <div class="nav nav-tabs flex-col sm:flex-row bg-gray-300 dark:bg-dark-2 text-gray-600" role="tablist">
                    <Tippy id="supplier-tab" tag="a" :content="t('views.supplier.tabs.supplier')" data-toggle="tab" data-target="#supplier" href="javascript:;" class="w-full sm:w-40 py-4 text-center flex justify-center items-center active" role="tab" aria-controls="content" aria-selected="true">
                        <span :class="{'text-theme-21':errors['code']||errors['name']}">{{ t('views.supplier.tabs.supplier') }}</span>
                    </Tippy>
                    <Tippy id="poc-tab" tag="a" :content="t('views.supplier.tabs.poc')" data-toggle="tab" data-target="#poc" href="javascript:;" class="w-full sm:w-40 py-4 text-center flex justify-center items-center" role="tab" aria-selected="false">
                        <span :class="{'text-theme-21':errors['poc_name']||errors['email']}">{{ t('views.supplier.tabs.poc') }}</span>
                    </Tippy>
                    <Tippy id="products-tab" tag="a" :content="t('views.supplier.tabs.products')" data-toggle="tab" data-target="#products" href="javascript:;" class="w-full sm:w-40 py-4 text-center flex justify-center items-center" role="tab" aria-selected="false">
                        {{ t('views.supplier.tabs.products') }}
                    </Tippy>
                </div>
                <div class="tab-content">
                    <div id="supplier" class="tab-pane p-5 active" role="tabpanel" aria-labelledby="supplier-tab">
                        <div class="mb-3">
                            <label for="inputCode" class="form-label">{{ t('views.supplier.fields.code') }}</label>
                            <div class="form-inline">
                                <VeeField id="inputCode" name="code" as="input" :class="{'form-control':true, 'border-theme-21': errors['code']}" :placeholder="t('views.supplier.fields.code')" :label="t('views.supplier.fields.code')" v-model="supplier.code" v-show="mode === 'create' || mode === 'edit'" :readonly="supplier.code === '[AUTO]'"/>
                                <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                            </div>
                            <ErrorMessage name="code" class="text-theme-21" />
                        </div>
                        <div class="mb-3">
                            <label for="inputName" class="form-label">{{ t('views.supplier.fields.name') }}</label>
                            <VeeField id="inputName" name="name" type="text" :class="{'form-control':true, 'border-theme-21': errors['name']}" :placeholder="t('views.supplier.fields.name')" :label="t('views.supplier.fields.name')" v-model="supplier.name" v-show="mode === 'create' || mode === 'edit'" />
                            <ErrorMessage name="name" class="text-theme-21" />
                        </div>
                        <div class="mb-3">
                            <label for="inputAddress" class="form-label">{{ t('views.supplier.fields.address') }}</label>
                            <textarea id="inputAddress" name="address" type="text" class="form-control" :placeholder="t('views.supplier.fields.address')" v-model="supplier.address" v-show="mode === 'create' || mode === 'edit'" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="inputContact" class="form-label">{{ t('views.supplier.fields.contact') }}</label>
                            <input id="inputContact" name="city" type="text" class="form-control" :placeholder="t('views.supplier.fields.contact')" v-model="supplier.city" v-show="mode === 'create' || mode === 'edit'"/>
                        </div>
                        <div class="mb-3">
                            <label for="inputCity" class="form-label">{{ t('views.supplier.fields.city') }}</label>
                            <input id="inputCity" name="city" type="text" class="form-control" :placeholder="t('views.supplier.fields.city')" v-model="supplier.city" v-show="mode === 'create' || mode === 'edit'"/>
                            <div class="" v-if="mode === 'show'">{{ supplier.city }}</div>
                        </div>
                        <div class="mb-3">
                            <label for="inputTaxableEnterprise">{{ t('views.supplier.fields.taxable_enterprise') }}</label>
                            <div class="mt-2">
                                <input id="inputTaxableEnterprise" type="checkbox" class="form-check-switch" name="taxable_enterprise" v-model="supplier.taxable_enterprise" v-show="mode === 'create' || mode === 'edit'" true-value="1" false-value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="inputPaymnetTermType" class="form-label">{{ t('views.supplier.fields.payment_term_type') }}</label>
                            <select class="form-control form-select" id="inputPaymnetTermType" name="payment_term_type" v-model="supplier.payment_term_type" v-show="mode === 'create' || mode === 'edit'">
                                <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                <option v-for="c in paymentTermDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                            </select>
                            <div class="" v-if="mode === 'show'">
                                <span v-if="supplier.payment_term_type === 'pia'">{{ t('components.dropdown.values.paymentTermTypeDDL.pia') }}</span>
                                <span v-if="supplier.payment_term_type === 'net30'">{{ t('components.dropdown.values.paymentTermTypeDDL.net30') }}</span>
                                <span v-if="supplier.payment_term_type === 'eom'">{{ t('components.dropdown.values.paymentTermTypeDDL.eom') }}</span>
                                <span v-if="supplier.payment_term_type === 'cod'">{{ t('components.dropdown.values.paymentTermTypeDDL.cod') }}</span>
                                <span v-if="supplier.payment_term_type === 'cnd'">{{ t('components.dropdown.values.paymentTermTypeDDL.cnd') }}</span>
                                <span v-if="supplier.payment_term_type === 'cbs'">{{ t('components.dropdown.values.paymentTermTypeDDL.cbs') }}</span>
                            </div>
                            <ErrorMessage name="status" class="text-theme-21" />
                        </div>
                        <div class="mb-3">
                            <label for="inputStatus" class="form-label">{{ t('views.supplier.fields.status') }}</label>
                            <select class="form-control form-select" id="inputStatus" name="status" v-model="supplier.status" v-show="mode === 'create' || mode === 'edit'">
                                <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                            </select>
                            <div class="" v-if="mode === 'show'">
                                <span v-if="supplier.status === 1">{{ t('components.dropdown.values.statusDDL.active') }}</span>
                                <span v-if="supplier.status === 0">{{ t('components.dropdown.values.statusDDL.inactive') }}</span>
                            </div>
                            <ErrorMessage name="status" class="text-theme-21" />
                        </div>
                        <div class="mb-3">
                            <label for="inputRemarks" class="form-label">{{ t('views.supplier.fields.remarks') }}</label>
                            <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.supplier.fields.remarks')" v-model="supplier.remarks" v-show="mode === 'create' || mode === 'edit'" rows="3"></textarea>
                        </div>
                    </div>
                    <div id="poc" class="tab-pane p-5" role="tabpanel" aria-labelledby="poc-tab">
                        <div class="mb-3">
                            <label for="inputPOCName" class="form-label">{{ t('views.supplier.fields.poc.name') }}</label>
                            <VeeField id="inputPOCName" name="poc_name" as="input" :class="{'form-control':true, 'border-theme-21': errors['poc_name']}" :placeholder="t('views.supplier.fields.poc.name')" :label="t('views.supplier.fields.poc.name')" v-model="supplier.user.name" v-show="mode === 'create' || mode === 'edit'" />
                            <ErrorMessage name="poc_name" class="text-theme-21" />
                        </div>
                        <div class="mb-3">
                            <label for="inputEmail" class="form-label">{{ t('views.supplier.fields.poc.email') }}</label>
                            <VeeField id="inputEmail" name="email" type="text" :class="{'form-control':true, 'border-theme-21': errors['email']}" :placeholder="t('views.supplier.fields.poc.email')" :label="t('views.supplier.fields.poc.email')" v-model="supplier.user.email" v-show="mode === 'create' || mode === 'edit'" :readonly="mode === 'edit'"/>
                            <ErrorMessage name="email" class="text-theme-21" />
                        </div>
                    </div>
                    <div id="products" class="tab-pane p-5" role="tabpanel" aria-labelledby="products-tab">
                        <div class="mb-3">
                            <label for="inputProductLists" class="form-label">{{ t('views.supplier.fields.products.product_lists') }}</label>                            
                            <table class="table table--sm">
                                <tbody>
                                    <tr v-for="(p, pIdx) in supplier.products">
                                        <td class="border-b dark:border-dark-5">
                                            <input :id="'inputProduct_' + p.hId" type="checkbox" class="form-check-switch" name="productIds">
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
                <div class="pl-5" v-if="this.mode === 'create' || this.mode === 'edit'">
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
// Vue Import
import { inject, onMounted, ref, computed, watch } from 'vue'
// Helper Import
import { getLang } from '../../lang';
import mainMixins from '../../mixins';
import { helper } from '../../utils/helper';
// Core Components Import
import { useStore } from '../../store/index';
// Components Import
import DataList from '../../global-components/data-list/Main'
import AlertPlaceholder from '../../global-components/alert-placeholder/Main'

// Vee-Validate Schema
const schema = {
    code: 'required',
    name: 'required',
    poc_name: 'required',
    email: 'required|email',
};

// Declarations
const store = useStore();

// Mixins
const { t, route } = mainMixins();

// Data - VueX
const selectedUserCompany = computed(() => store.state.main.selectedUserCompany );

// Data - UI
const mode = ref('list');
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref('');
const expandDetail = ref(null);

// Data - Views
const supplierList = ref([]);
const supplier = ref({
    code: '',
    name: '',
    term: '',
    contact: '',
    address: '',
    user: {
        hId: '',
        profile: {
            first_name: ''
        }
    },
    city: '',
    taxable_enterprise: 1,
    tax_id: '',
    remarks: '',
    payment_term_type: '',
    status: 1,
});
const statusDDL = ref([]);
const paymentTermDDL = ref([]);

// onMounted
onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);

    if (selectedUserCompany.value !== '')
        getAllSupplier({ page: 1});

    getDDL();

    loading.value = false;
});

// Methods
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

    axios.get(route('api.get.db.common.ddl.list.payment_term')).then(response => {
        paymentTermDDL.value = response.data;
    });
}

function onSubmit(values, actions) {
    loading.value = true;

    var formData = Object.fromEntries(new FormData(cash('#supplierForm')[0])); 
    formData.company_id = selectedUserCompany.value;

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

function emptySupplier() {
    return {
        code: '[AUTO]',
        name: '',
        term: '',
        contact: '',
        address: '',
        city: '',
        user: {
            hId: '',
            profile: {
                first_name: ''
            }
        },
        taxable_enterprise: 1,
        tax_id: '',
        remarks: '',
        payment_term_type: '',
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

// Computed
// Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value !== '') {
        getAllSupplier({ page: 1 });
    }
});
</script>
