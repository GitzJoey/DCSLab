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
                                        <a class="flex items-center text-theme-21" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal" @click="deleteSelected(itemIdx)">
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
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.product.fields.name') }}</div>
                                        <div class="flex-1">{{ item.name }}</div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </template>
        </DataList>
    </div>

    <div class="intro-y box" v-if="mode !== 'list'">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.product.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.product.actions.edit') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'show'">{{ t('views.product.actions.show') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="productForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
                <div class="p-5">
                    <div class="mb-3">
                        <label for="inputCode" class="form-label">{{ t('views.product.fields.code') }}</label>
                        <div class="flex items-center">
                            <VeeField id="inputCode" name="code" as="input" :class="{'form-control':true, 'border-theme-21': errors['code']}" :placeholder="t('views.product.fields.code')" :label="t('views.product.fields.code')" v-model="product.code" v-show="mode === 'create' || mode === 'edit'" :readonly="product.code === '[AUTO]'"/>
                            <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                        </div>
                        <ErrorMessage name="code" class="text-theme-21" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="product_group_id">{{ t('views.product.fields.product_group_id') }}</label>
                        <select id="product_group_id" name="product_group_id" :class="{'form-control form-select':true, 'border-theme-21': errors['product_group_id']}" :label="t('views.product.fields.product_group_id')" v-model="product.product_group.hId" v-show="mode === 'create' || mode === 'edit'">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option :value="g.hId" v-for="g in productGroupDDL" v-bind:key="g.hId">{{ g.code }} - {{ g.name }}</option>
                        </select>
                        <ErrorMessage name="product_group_id" class="text-theme-21" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="brand_id">{{ t('views.product.fields.brand_id') }}</label>
                        <select id="brand_id" name="brand_id" :class="{'form-control form-select':true, 'border-theme-21': errors['brand_id']}" v-model="product.brand.hId" :label="t('views.product.fields.brand_id')" v-show="mode === 'create' || mode === 'edit'">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option :value="b.hId" v-for="b in brandDDL" v-bind:key="b.hId">{{ b.code }} - {{ b.name }}</option>
                        </select>
                        <ErrorMessage name="brand_id" class="text-theme-21" />
                    </div>
                    <div class="mb-3">
                        <label for="inputName" class="form-label">{{ t('views.product.fields.name') }}</label>
                        <VeeField id="inputName" name="name" as="input" :class="{'form-control':true, 'border-theme-21': errors['name']}" :placeholder="t('views.product.fields.name')" :label="t('views.product.fields.name')" v-model="product.name" v-show="mode === 'create' || mode === 'edit'"/>
                        <ErrorMessage name="name" class="text-theme-21" />
                    </div>
                    <div class="mb-3">
                        <label for="product_type" class="form-label">{{ t('views.product.fields.product_type') }}</label>
                        <select id="product_type" name="product_type" :class="{'form-control form-select':true, 'border-theme-21': errors['product_type']}" v-model="product.product_type" :label="t('views.product.fields.product_type')" v-show="mode === 'create' || mode === 'edit'">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option :value="pt.code" v-for="pt in productTypeDDL" v-bind:key="pt.code">{{ t(pt.name) }}</option>
                        </select>
                        <ErrorMessage name="product_type" class="text-theme-21" />
                    </div>
                    <div class="mb-3">
                        <label for="inputUnit" class="form-label">{{ t('views.product.fields.units.title') }}</label>
                        <div class="grid grid-cols-9 mb-3 bg-gray-700 dark:bg-dark-1 gap-2">
                            <div class="text-white p-3 font-bold col-span-2">{{ t('views.product.fields.units.table.cols.code') }}</div>
                            <div class="text-white p-3 font-bold col-span-2">{{ t('views.product.fields.units.table.cols.unit') }}</div>
                            <div class="text-white p-3 font-bold col-span-2">{{ t('views.product.fields.units.table.cols.conversion_value') }}</div>
                            <div class="text-white p-3 font-bold">{{ t('views.product.fields.units.table.cols.is_base') }}</div>
                            <div class="text-white p-3 font-bold">{{ t('views.product.fields.units.table.cols.is_primary') }}</div>
                            <div class="text-white p-3 font-bold"></div>
                        </div>
                        <div class="grid grid-cols-9 gap-2 mb-2" v-for="(pu, puIdx) in product.product_units">
                            <div class="col-span-2">
                                <div class="flex items-center">
                                    <input type="text" class="form-control" v-model="pu.code" id="product_units_code" name="product_units_code[]" :readonly="mode === 'create' && pu.code === '[AUTO]'"/>
                                    <button type="button" class="btn btn-secondary mx-1" @click="generateCodeUnit(puIdx)" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                                </div>
                            </div>
                            <div class="col-span-2">
                                <select class="form-control form-select" id="unit_id" name="unit_id[]" v-model="pu.unit.hId">
                                    <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                    <option :value="u.hId" v-for="u in unitDDL" v-bind:key="u.hId">{{ u.name }}</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <input type="text" class="form-control text-right" v-model="pu.conversion_value" id="conv_value" name="conv_value[]" :readonly="pu.is_base"/>
                                <ErrorMessage :name="'conv_value.' + puIdx" class="text-theme-21"/>
                            </div>
                            <div class="flex items-center justify-center">
                                <input id="inputIsBase" class="form-check-input" type="checkbox" v-model="pu.is_base" true-value="1" false-value="0" @click="changeIsBase(puIdx)"> 
                                <input type="hidden" v-model="pu.is_base" name="is_base[]"/>
                            </div>
                            <div class="flex items-center justify-center">
                                <input id="inputIsPrimary" class="form-check-input" type="checkbox" v-model="pu.is_primary_unit" true-value="1" false-value="0" @click="changeIsPrimary(puIdx)">
                                <input type="hidden" v-model="pu.is_primary_unit" name="is_primary_unit[]"/>
                            </div>
                            <div class="flex items-center justify-center">
                                <button class="btn btn-sm btn-secondary" @click.prevent="deleteUnitSelected(puIdx)"><TrashIcon class="w-3 h-4"/></button>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-secondary w-24" @click.prevent="createNewUnit"><PlusIcon class="w-3 h-4" /></button>
                    </div>
                    <div class="mb-3">
                        <label for="inputPoint" class="form-label">{{ t('views.product.fields.point') }}</label>
                        <VeeField id="inputPoint" name="point" as="input" :class="{'form-control':true, 'border-theme-21': errors['point']}" :placeholder="t('views.product.fields.point')" :label="t('views.product.fields.point')" v-model="product.point" v-show="mode === 'create' || mode === 'edit'"/>
                        <ErrorMessage name="point" class="text-theme-21" />
                    </div>
                    <div class="mb-3">
                        <label for="inputUseSerialNumber" class="form-label">{{ t('views.product.fields.use_serial_number') }}</label>
                        <div class="mt-2">
                            <input id="inputUseSerialNumber" type="checkbox" class="form-check-switch" name="use_serial_number" v-model="product.use_serial_number" v-show="mode === 'create' || mode === 'edit'" true-value="1" false-value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="inputHasExpiryDate" class="form-label">{{ t('views.product.fields.has_expiry_date') }}</label>
                        <div class="mt-2">
                            <input id="inputHasExpiryDate" type="checkbox" class="form-check-switch" name="has_expiry_date" v-model="product.has_expiry_date" v-show="mode === 'create' || mode === 'edit'" true-value="1" false-value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="inputPriceIncludeVAT" class="form-label">{{ t('views.product.fields.price_include_vat') }}</label>
                        <div class="mt-2">
                            <input id="inputPriceIncludeVAT" type="checkbox" class="form-check-switch" name="price_include_vat" v-model="product.price_include_vat" v-show="mode === 'create' || mode === 'edit'" true-value="1" false-value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">{{ t('views.product.fields.status') }}</label>
                        <select id="status" name="status" :class="{'form-control form-select':true, 'border-theme-21': errors['status']}" v-model="product.status" v-show="mode === 'create' || mode === 'edit'">
                            <option value='1'>{{ t('components.dropdown.values.statusDDL.active') }}</option>
                            <option value='0'>{{ t('components.dropdown.values.statusDDL.inactive') }}</option>
                        </select>
                        <ErrorMessage name="status" class="text-theme-21" />
                    </div>
                    <div class="mb-3">
                        <label for="inputRemarks" class="form-label">{{ t('views.product.fields.remarks') }}</label>
                        <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.product.fields.remarks')" v-model="product.remarks" v-show="mode === 'create' || mode === 'edit'" rows="3"></textarea>
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
import mainMixins from '../../mixins';
// Core Components Import
import { useStore } from '../../store/index';
// Components Import
import DataList from '../../global-components/data-list/Main'
import AlertPlaceholder from '../../global-components/alert-placeholder/Main'

// Vee-Validate Schema
const schema = {
    code: 'required',
    name: 'required',
    point: 'required'
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
const productList = ref([]);
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
    taxable_supplies: '',
    rate_supplies: '',
    price_include_vat: '',
    remarks: '',
    point: '',
    is_use_serial: '',
    product_type: '',
    status: '',
});
const statusDDL = ref([]);
const productGroupDDL = ref([]);
const brandDDL = ref([]);
const unitDDL = ref([]);
const supplierDDL = ref([]);
const productTypeDDL = ref([]);

// onMounted
onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);

    if (selectedUserCompany.value !== '') {
        getAllProducts({ page: 1 });
        getDDLSync();
    }

    getDDL();

    loading.value = false;
});

// Methods
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

    var formData = new FormData(cash('#productForm')[0]); 
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
        taxable_supplies: '',
        rate_supplies: '',
        price_include_vat: '',
        remarks: '',
        point: '',
        use_serial_number: '',
        has_expiry_date: '',
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

    if (product.value.supplier === null) {
        product.value.supplier = {
            hId: ''
        };
    }
}

function deleteSelected(index) {
    deleteId.value = productList.value.data[index].hId;
}

function deleteUnitSelected(index) {
    product.value.product_units.splice(index, 1);
}

function confirmDelete() {
    axios.post(route('api.post.db.product.product.delete', deleteId.value)).then(response => {
        backToList();
    }).catch(e => {
        alertErrors.value = e.response.data;
    }).finally(() => {

    });
}

function showSelected(index) {
    mode.value = 'show';
    product.value = productList.value.data[index];
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
    for (let i = 0; i < product.value.product_units.length; i++) {
        if (i === idx) {
            product.value.product_units[i].conversion_value = 1;
        }
        product.value.product_units[i].is_base = 0;
    }
}

function changeIsPrimary(idx) {
    for (let i = 0; i < product.value.product_units.length; i++) {
        if (i === idx) continue;
        product.value.product_units[i].is_primary_unit = 0;
    }
}

// Computed
// Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value !== '') {
        getAllProducts({ page: 1 });
        getDDLSync();
    }
});
</script>