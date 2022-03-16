<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.branch.table.title')" :data="branchList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
           <template v-slot:table="tableProps">
                <table class="table table-report -mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.branch.table.cols.company') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.branch.table.cols.code') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.branch.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.branch.table.cols.remarks') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.branch.table.cols.status') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data">
                            <tr class="intro-x">
                                <td>{{ item.company.name }}</td>
                                <td>{{ item.code }}</td>
                                <td><a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse">{{ item.name }}</a></td>
                                <td>{{ item.remarks }}</td>
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
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.company_id') }}</div>
                                        <div class="flex-1">{{ item.company.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.code') }}</div>
                                        <div class="flex-1">{{ item.code }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.name') }}</div>
                                        <div class="flex-1">{{ item.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.remarks') }}</div>
                                        <div class="flex-1">{{ item.remarks }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.branch.fields.status') }}</div>
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
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.branch.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.branch.actions.edit') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="branchForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <div class="p-5">
                    <!-- #region company -->
                        <div class="mb-3">
                            <label class="form-label" for="inputCompany_id">{{ t('views.branch.fields.company_id') }}</label>
                            <VeeField as="select" id="company_id" name="company_id" :class="{'form-control form-select':true, 'border-theme-21': errors['company_id']}" v-model="branch.company.hId" :label="t('views.branch.fields.company_id')" rules="required" @blur="reValidate(errors)">
                                <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                <option v-for="c in companyDDL" :value="c.hId">{{ c.name }}</option>
                            </VeeField>
                            <ErrorMessage name="company_id" class="text-theme-21" />
                        </div>
                    <!-- #endregion -->
                    
                    <!-- #region code -->
                        <div class="mb-3">
                            <label for="inputCode" class="form-label">{{ t('views.branch.fields.code') }}</label>
                            <div class="flex items-center">
                                <VeeField id="inputCode" name="code" as="input" :class="{'form-control':true, 'border-theme-21': errors['code']}" :placeholder="t('views.branch.fields.code')" :label="t('views.branch.fields.code')" rules="required" @blur="reValidate(errors)" v-model="branch.code" :readonly="branch.code === '[AUTO]'" />
                                <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                            </div>
                            <ErrorMessage name="code" class="text-theme-21" />
                        </div>
                    <!-- #endregion -->
                    
                    <!-- #region name -->
                        <div class="mb-3">
                            <label for="inputName" class="form-label">{{ t('views.branch.fields.name') }}</label>
                            <VeeField id="inputName" name="name" as="input" :class="{'form-control':true, 'border-theme-21': errors['name']}" :placeholder="t('views.branch.fields.name')" :label="t('views.branch.fields.name')" rules="required" @blur="reValidate(errors)" v-model="branch.name" />
                            <ErrorMessage name="name" class="text-theme-21" />
                        </div>
                    <!-- #endregion -->
                    
                    <!-- #region addresss -->
                        <div class="mb-3">
                            <label for="inputAddress" class="form-label">{{ t('views.branch.fields.address') }}</label>
                            <textarea id="inputAddress" name="address" type="text" class="form-control" :placeholder="t('views.branch.fields.address')" v-model="branch.address" rows="3"></textarea>
                        </div>
                    <!-- #endregion -->
                    
                    <!-- #region city -->
                        <div class="mb-3">
                            <label for="inputCity" class="form-label">{{ t('views.branch.fields.city') }}</label>
                            <input id="inputCity" name="city" type="text" class="form-control" :placeholder="t('views.branch.fields.city')" v-model="branch.city"/>
                        </div>
                    <!-- #endregion -->
                    
                    <!-- #region contact -->
                        <div class="mb-3">
                            <label for="inputContact" class="form-label">{{ t('views.branch.fields.contact') }}</label>
                            <input id="inputContact" name="contact" type="text" class="form-control" :placeholder="t('views.branch.fields.contact')" v-model="branch.contact"/>
                        </div>
                    <!-- #endregion -->
                    
                    <!-- #region remarks -->
                        <div class="mb-3">
                            <label for="inputRemarks" class="form-label">{{ t('views.branch.fields.remarks') }}</label>
                            <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.branch.fields.remarks')" v-model="branch.remarks" rows="3"></textarea>
                        </div>
                    <!-- #endregion -->
                    
                    <!-- #region status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">{{ t('views.branch.fields.status') }}</label>
                            <VeeField as="select" id="status" name="status" :class="{'form-control form-select':true, 'border-theme-21': errors['status']}" v-model="branch.status" rules="required" @blur="reValidate(errors)">
                                <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                            </VeeField>
                            <ErrorMessage name="status" class="text-theme-21" />
                        </div>
                    <!-- #endregion -->
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
const branchList = ref({});
const branch = ref({
    company: { 
        hId: '',
        name: '' 
    },
    code: '',
    name: '',
    address: '',
    city: '',
    contact: '',
    remarks: '',
    status: 1,
});
const statusDDL = ref([]);
const companyDDL = ref([]);

// onMounted
onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);
  
    if (selectedUserCompany.value !== '') {
        getAllBranch({ page: 1 });
        getDDLSync();
    } else  {
        
    }

    getDDL();

    loading.value = false;
});

// Methods
function getAllBranch(args) {
    branchList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    let companyId = selectedUserCompany.value;

    axios.get(route('api.get.db.company.branch.read', { "companyId": companyId, "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        branchList.value = response.data;
        loading.value = false;
    });
}

function getDDL() {
    axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
        statusDDL.value = response.data;
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

    var formData = new FormData(cash('#branchForm')[0]); 
    formData.append('company_id', selectedUserCompany.value);
    
    if (mode.value === 'create') {
        axios.post(route('api.post.db.company.branch.save'), formData).then(response => {
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === 'edit') {
        axios.post(route('api.post.db.company.branch.edit', branch.value.hId), formData).then(response => {
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

function emptybranch() {
    return {
        company: {
            hId: '',
            name: ''
        },
        code: '[AUTO]',
        name: '',
        address: '',
        city: '',
        contact: '',
        remarks: '',
        status: 1,
    }
}

function resetAlertErrors() {
    alertErrors.value = [];
}

function createNew() {
    mode.value = 'create';
    branch.value = emptybranch();
}

function onDataListChange({page, pageSize, search}) {
    getAllBranch({page, pageSize, search});
}

function editSelected(index) {
    mode.value = 'edit';
    branch.value = branchList.value.data[index];
}

function deleteSelected(index) {
    deleteId.value = branchList.value.data[index].hId;
}

function confirmDelete() {
    axios.post(route('api.post.db.company.branch.delete', deleteId.value)).then(response => {
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
    getAllBranch({ page: branchList.value.current_page, pageSize: branchList.value.per_page });
}

function toggleDetail(idx) {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

function generateCode() {
    if (branch.value.code === '[AUTO]') branch.value.code = '';
    else  branch.value.code = '[AUTO]'
}

// Computed
// Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value !== '') {
        getAllBranch({ page: 1 });
        getDDLSync();
    }
});
</script>