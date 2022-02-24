<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.employee.table.title')" :data="employeeList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
           <template v-slot:table="tableProps">
                <table class="table table-report -mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.employee.table.cols.company') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.employee.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.employee.table.cols.remarks') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data">
                            <tr class="intro-x">
                                <td>{{ item.company.name }}</td>
                                <td><a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse">{{ item.name }}</a></td>
                                <td>{{ item.remarks }}</td>
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
                                <td colspan="6">
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.company_id') }}</div>
                                        <div class="flex-1">{{ item.company.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.name') }}</div>
                                        <div class="flex-1">{{ item.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.remarks') }}</div>
                                        <div class="flex-1">{{ item.remarks }}</div>
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
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.employee.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.employee.actions.edit') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="employeeForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <div class="p-5">
                    <div class="mb-3">
                        <label class="form-label" for="inputCompany_id">{{ t('views.employee.fields.company_id') }}</label>
                        <VeeField as="select" id="company_id" name="company_id" :class="{'form-control form-select':true, 'border-theme-21': errors['company_id']}" v-model="employee.company.hId" :label="t('views.employee.fields.company_id')" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in companyDDL" :value="c.hId">{{ c.name }}</option>
                        </VeeField>
                        <ErrorMessage name="company_id" class="text-theme-21" />
                    </div>
                    <div class="form-group row">
                        <label for="inputAddress" class="col-2 col-form-label">{{ $t('fields.address') }}</label>
                        <div class="col-md-10">
                            <input id="inputAddress" name="address" type="text" class="form-control" :placeholder="$t('fields.address')" v-model="employee.user.profile.address" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                            <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ employee.user.profile.address }}</div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="inputCity" class="col-2 col-form-label">{{ $t('fields.city') }}</label>
                        <div class="col-md-10">
                            <input id="inputCity" name="city" type="text" class="form-control" :placeholder="$t('fields.city')" v-model="employee.user.profile.city" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                            <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ employee.user.profile.city }}</div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="inputPostalCode" class="col-2 col-form-label">{{ $t('fields.postal_code') }}</label>
                        <div class="col-md-10">
                            <input id="inputPostalCode" name="postal_code" type="text" class="form-control" :placeholder="$t('fields.postal_code')" v-model="employee.user.profile.postal_code" v-show="this.mode === 'create' || this.mode === 'edit'"/>
                            <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ employee.user.profile.postal_code }}</div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="inputCountry" class="col-2 col-form-label">{{ $t('fields.country') }}</label>
                        <div class="col-md-10">
                            <select id="inputCountry" name="country" class="form-control" v-model="employee.user.profile.country" :placeholder="$t('fields.country')" v-show="this.mode === 'create' || this.mode === 'edit'">
                                <option value="">{{ $t('placeholder.please_select') }}</option>
                                <option v-for="c in countriesDDL" :key="c.name">{{ c.name }}</option>
                            </select>
                            <div class="form-control-plaintext" v-if="this.mode === 'show'">{{ employee.user.profile.country }}</div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="country">{{ $t('fields.country') }}</label>
                            <input type="text" class="form-control form-control-lg" id="country" name="country" readonly v-model="employee.user.profile.country"/>
                        </div>
                    </div>
                    <!-- <div class="form-group row">
                        <div class="col-12">
                            <label for="tax_id">{{ $t('fields.tax_id') }}</label>
                            <Field as="input" :class="{'form-control form-control-lg':true, 'is-invalid':errors['tax_id']}" :label="$t('fields.tax_id')" id="tax_id" name="tax_id" v-model="employee.user.profile.tax_id"/>
                            <ErrorMessage name="tax_id" class="invalid-feedback" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="ic_num">{{ $t('fields.ic_num') }}</label>
                            <Field as="input" :class="{'form-control form-control-lg':true, 'is-invalid':errors['ic_num']}" :label="$t('fields.ic_num')" id="ic_num" name="ic_num" v-model="employee.user.profile.ic_num"/>
                            <ErrorMessage name="ic_num" class="invalid-feedback" />
                        </div>
                    </div> -->
                    <div class="form-group row">
                        <div class="col-12">
                            <label for="remarks">{{ $t('fields.remarks') }}</label>
                            <textarea type="text" rows="3" class="form-control form-control-lg" id="remarks" name="remarks" v-model="employee.user.profile.remarks"/>
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
// Vue Import
import { inject, onMounted, ref, computed, watch } from 'vue'
// Helper Import
import axios from '../../axios';
import mainMixins from '../../mixins';
// Core Components Import
import { useStore } from '../../store/index';
// Components Import
import DataList from '../../global-components/data-list/Main'
import AlertPlaceholder from '../../global-components/alert-placeholder/Main'

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
const employeeList = ref({});
const employee = ref({
    company: { 
        hId: '',
        name: '' 
    },
    user: {
        hId: '',
        name: '',
        email: '',
        profile: {
            hId: '',
            address: '',
            city: '',
            postal_code: '',
            country: '',
            tax_id: '',
            ic_num: '',
            status: 1,
            remarks: '',
        },
    },
});
const companyDDL = ref([]);

// onMounted
onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);
  
    if (selectedUserCompany.value !== '') {
        getAllEmployees({ page: 1 });
        getDDLSync();
    } else  {
        
    }

    loading.value = false;
});

// Methods
function getAllEmployees(args) {
    employeeList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    let companyId = selectedUserCompany.value;

    axios.get(route('api.get.db.company.employee.read', { "companyId": companyId, "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        employeeList.value = response.data;
        loading.value = false;
    });
}

function getDDLSync() {
    axios.get(route('api.get.db.company.company.read.all_active', {
            companyId: selectedUserCompany.value,
            paginate: false
        })).then(response => {
            companyDDL.value = response.data;
    });
}

function onSubmit(values, actions) {
    loading.value = true;

    var formData = new FormData(cash('#employeeForm')[0]); 
    formData.append('company_id', selectedUserCompany.value);
    
    if (mode.value === 'create') {
        axios.post(route('api.post.db.company.employee.save'), formData).then(response => {
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === 'edit') {
        axios.post(route('api.post.db.company.employee.edit', employee.value.hId), formData).then(response => {
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

function emptyEmployee() {
    return {
        company: { 
        hId: '',
        name: '' 
    },
        user: {
            hId: '',
            name: '',
            email: '',
            profile: {
                hId: '',
                address: '',
                city: '',
                postal_code: '',
                country: '',
                tax_id: '',
                ic_num: '',
                status: 1,
                remarks: '',
            },
        }
    }
}

function resetAlertErrors() {
    alertErrors.value = [];
}

function createNew() {
    mode.value = 'create';
    // employee.value = emptyEmployee();

    // employee.value.company = _.find(companyDDL.value, { 'hId': selectedUserCompany.value });
}

function onDataListChange({page, pageSize, search}) {
    getAllEmployees({page, pageSize, search});
}

function editSelected(index) {
    mode.value = 'edit';
    employee.value = employeeList.value.data[index];
}

function deleteSelected(index) {
    deleteId.value = employeeList.value.data[index].hId;
}

function confirmDelete() {
    axios.post(route('api.post.db.company.employee.delete', deleteId.value)).then(response => {
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
    getAllEmployees({ page: employeeList.value.current_page, pageSize: employeeList.value.per_page });
}

function toggleDetail(idx) {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

function generateCode() {
    if (employee.value.code === '[AUTO]') employee.value.code = '';
    else  employee.value.code = '[AUTO]'
}

// Computed
// Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value !== '') {
        getAllEmployees({ page: 1 });
        getDDLSync();
    }
});
</script>