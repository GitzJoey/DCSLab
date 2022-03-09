<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.employee.table.title')" :data="employeeList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
           <template v-slot:table="tableProps">
                <table class="table table-report -mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.employee.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.employee.table.cols.email') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.employee.table.cols.status') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data">
                            <tr class="intro-x">
                                <td><a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse">{{ item.user.name }}</a></td>
                                <td>{{ item.user.email }}</td>
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
                                <td colspan="6">
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.name') }}</div>
                                        <div class="flex-1">{{ item.user.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.remarks') }}</div>
                                        <div class="flex-1">{{ item.user.email }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.status') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.status === 1">{{ t('components.dropdown.values.statusDDL.active') }}</span>
                                            <span v-if="item.status === 0">{{ t('components.dropdown.values.statusDDL.inactive') }}</span>
                                        </div>
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
                    <!-- name -->
                    <div class="mb-3">
                        <label for="inputName" class="form-label">{{ t('views.employee.fields.name') }}</label>
                        <VeeField id="inputName" name="name" as="input" :class="{'form-control':true, 'border-theme-21': errors['name']}" :placeholder="t('views.employee.fields.name')" :label="t('views.employee.fields.name')" rules="required" @blur="reValidate(errors)" v-model="employee.user.name" />
                        <ErrorMessage name="name" class="text-theme-21" />
                    </div>
                    <!-- email -->
                    <div class="mb-3">
                        <label for="inputEmail" class="form-label">{{ t('views.employee.fields.email') }}</label>
                        <VeeField id="inputEmail" name="email" as="input" :class="{'form-control':true, 'border-theme-21': errors['email']}" rules="required|email" :placeholder="t('views.employee.fields.email')" :label="t('views.employee.fields.email')" @blur="reValidate(errors)" v-model="employee.user.email" :readonly="mode === 'edit'" />
                        <ErrorMessage name="email" class="text-theme-21" />
                    </div>
                    <!-- input img -->
                    <div class="mb-3">
                        <label for="inputImg" class="form-label">{{ t('views.employee.fields.picture') }}</label>
                        <div class="">
                            <div class="my-1">
                                <img id="inputImg" alt="" class="" :src="retrieveImage">
                            </div>
                            <div class="">
                                <input type="file" class="h-full w-full" name="img_path" v-if="mode === 'create' || mode === 'edit'" v-on:change="handleUpload" />
                            </div>
                        </div>
                    </div>
                    <!-- address -->
                    <div class="mb-3">
                        <label for="inputAddress" class="form-label">{{ t('views.employee.fields.address') }}</label>
                        <textarea id="inputAddress" name="address" type="text" class="form-control" :placeholder="t('views.employee.fields.address')" v-model="employee.user.profile.address"></textarea>
                    </div>
                    <!-- city -->
                    <div class="mb-3">
                        <label for="inputCity" class="form-label">{{ t('views.employee.fields.city') }}</label>
                        <textarea id="inputCity" name="city" type="text" class="form-control" :placeholder="t('views.employee.fields.city')" v-model="employee.user.profile.city"></textarea>
                    </div>
                    <!-- postal code -->
                    <div class="mb-3">
                        <label for="inputPostalCode" class="form-label">{{ t('views.employee.fields.postal_code') }}</label>
                        <textarea id="inputPostalCode" name="postal_code" type="text" class="form-control" :placeholder="t('views.employee.fields.postal_code')" v-model="employee.user.profile.postal_code"></textarea>
                    </div>
                    <!-- country -->
                    <div class="mb-3">
                        <label for="inputCountry" class="form-label">{{ t('views.employee.fields.country') }}</label>
                        <VeeField as="select" id="inputCountry" name="country" :class="{'form-control form-select':true, 'border-theme-21': errors['country']}" v-model="employee.user.profile.country" rules="required" :placeholder="t('views.employee.fields.country')" :label="t('views.employee.fields.country')" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in countriesDDL" :key="c.name" :value="c.code">{{ c.name }}</option>
                        </VeeField>
                        <ErrorMessage name="country" class="text-theme-21" />
                    </div>
                    <!-- tax id -->
                    <div class="mb-3">
                        <label for="inputTaxId" class="form-label">{{ t('views.employee.fields.tax_id') }}</label>
                        <VeeField id="inputTaxId" name="tax_id" as="input" :class="{'form-control':true, 'border-theme-21': errors['tax_id']}" :placeholder="t('views.employee.fields.tax_id')" :label="t('views.employee.fields.tax_id')" rules="required" @blur="reValidate(errors)" v-model="employee.user.profile.tax_id" />
                        <ErrorMessage name="tax_id" class="text-theme-21" />
                    </div>
                    <!-- ic num -->
                    <div class="mb-3">
                        <label for="inputIcNum" class="form-label">{{ t('views.employee.fields.ic_num') }}</label>
                        <VeeField id="inputIcNum" name="ic_num" as="input" :class="{'form-control':true, 'border-theme-21': errors['ic_num']}" :placeholder="t('views.employee.fields.ic_num')" :label="t('views.employee.fields.ic_num')" rules="required" @blur="reValidate(errors)" v-model="employee.user.profile.ic_num" />
                        <ErrorMessage name="ic_num" class="text-theme-21" />
                    </div>
                    <!-- join date -->
                    <div class="mb-3">
                        <label for="inputJoinDate" class="form-label">{{ t('views.employee.fields.join_date') }}</label>
                        <VeeField name="join_date" v-slot="{ field }" rules="required" :label="t('views.employee.fields.join_date')">
                            <Litepicker v-model="employee.join_date" :class="{'form-control':true, 'border-theme-21': errors['join_date']}" v-bind="field" :options="{ autoApply: false, showWeekNumbers: false, dropdowns: { minYear: 1990, maxYear: null, months: true, years: true, }, format: 'YYYY-MM-DD'}" />
                        </VeeField>
                        <ErrorMessage name="join_date" class="text-theme-21" />
                    </div>
                    <!-- remarks -->
                    <div class="mb-3">
                        <label for="inputRemarks" class="form-label">{{ t('views.branch.fields.remarks') }}</label>
                        <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.branch.fields.remarks')" v-model="employee.user.profile.remarks" rows="3"></textarea>
                    </div>
                    <!-- status -->
                    <div class="mb-3">
                        <label for="inputStatus" class="form-label">{{ t('views.employee.fields.status') }}</label>
                        <VeeField as="select" class="form-control form-select" id="inputStatus" name="status" v-model="employee.status" rules="required" :label="t('views.employee.fields.status')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </VeeField>
                        <ErrorMessage name="status" class="text-theme-21" />
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
    user: {
        company: { 
            hId: '',
            name: '' 
        },
        hId: '',
        name: '',
        email: '',
        profile: {
            hId: '',
            img_path: '',
            address: '',
            city: '',
            postal_code: '',
            country: '',
            tax_id: '',
            ic_num: '',
            remarks: '',
            status: 1,
        },
    },
    join_date: '',
    status: 1,
});
const statusDDL = ref([]);
const countriesDDL = ref([]);

// onMounted
onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);
  
    if (selectedUserCompany.value !== '') {
        getAllEmployee({ page: 1 });
    } else  {
        
    }

    getDDL();

    loading.value = false;
});

// Methods
function getAllEmployee(args) {
    employeeList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    let companyId = selectedUserCompany.value;

    axios.get(route('api.get.db.company.employee.read', { "companyId": companyId, "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        employeeList.value = response.data;
        loading.value = false;

        console.log(employeeList.value);
    });
}

function getDDL() {
    axios.get(route('api.get.db.common.ddl.list.countries')).then(response => {
        countriesDDL.value = response.data;
    });

    axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
        statusDDL.value = response.data;
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
                img_path: '',
                address: '',
                city: '',
                postal_code: '',
                country: '',
                tax_id: '',
                ic_num: '',
                remarks: '',
                status: 1,
            },
        },
        join_date: '',
        status: 1,
    }
}

function resetAlertErrors() {
    alertErrors.value = [];
}

function createNew() {
    mode.value = 'create';
    employee.value = emptyEmployee();

    // employee.value.company = _.find(companyDDL.value, { 'hId': selectedUserCompany.value });
}

function onDataListChange({page, pageSize, search}) {
    getAllEmployee({page, pageSize, search});
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
    getAllEmployee({ page: employeeList.value.current_page, pageSize: employeeList.value.per_page });
}

function toggleDetail(idx) {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

function handleUpload(e) {
    const files = e.target.files;

    let filename = files[0].name;

    const fileReader = new FileReader()
    fileReader.addEventListener('load', () => {
        employee.value.user.profile.img_path = fileReader.result
    })
    fileReader.readAsDataURL(files[0])
}

// Computed
const retrieveImage = computed(() => {
    if (employee.value.user.profile.img_path && employee.value.user.profile.img_path !== '') {
        if (employee.value.user.profile.img_path.includes('data:image')) {
            return employee.value.user.profile.img_path;
        } else {
            return '/storage/' + employee.value.user.profile.img_path;
        }
    } else {
        return '/images/def-user.png';
    }
});

// Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value !== '') {
        getAllEmployee({ page: 1 });
    }
});
</script>