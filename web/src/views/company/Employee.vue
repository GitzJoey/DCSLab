<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.employee.table.title')" :data="employeeList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
           <template v-slot:table="tableProps">
                <table class="table table-report -mt-2" aria-describedby="">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.employee.table.cols.code') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.employee.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.employee.table.cols.join_date') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.employee.table.cols.status') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data">
                            <tr :class="{ 'intro-x':true, 'line-through':item.status === 'DELETED' }">
                                <td>{{ item.code }}</td>
                                <td><a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse">{{ item.user.name }}</a></td>
                                <td>{{ item.join_date }}</td>
                                <td>
                                    <CheckCircleIcon v-if="item.status === 'ACTIVE'" />
                                    <XIcon v-if="item.status === 'INACTIVE'" />
                                    <XIcon v-if="item.status === 'DELETED'" />
                                </td>
                                <td class="table-report__action w-12">
                                    <div class="flex justify-center items-center">
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.view')" @click.prevent="showSelected(itemIdx)">
                                            <InfoIcon class="w-4 h-4" />
                                        </Tippy>
                                        <template v-if="item.status !== 'DELETED'">
                                            <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.edit')" @click.prevent="editSelected(itemIdx)">
                                                <CheckSquareIcon class="w-4 h-4" />
                                            </Tippy>
                                            <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.delete')" @click.prevent="deleteSelected(itemIdx)">
                                                <Trash2Icon class="w-4 h-4 text-danger" />
                                            </Tippy>
                                        </template>
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
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.code') }}</div>
                                        <div class="flex-1">{{ item.code }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.name') }}</div>
                                        <div class="flex-1">{{ item.user.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.email') }}</div>
                                        <div class="flex-1">{{ item.user.email }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.address') }}</div>
                                        <div class="flex-1">{{ item.user.profile.address }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.city') }}</div>
                                        <div class="flex-1">{{ item.user.profile.city }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.postal_code') }}</div>
                                        <div class="flex-1">{{ item.user.profile.postal_code }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.country') }}</div>
                                        <div class="flex-1">{{ item.user.profile.country }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.tax_id') }}</div>
                                        <div class="flex-1">{{ item.user.profile.tax_id }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.ic_num') }}</div>
                                        <div class="flex-1">{{ item.user.profile.ic_num }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.status') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.status === 'ACTIVE'">{{ t('components.dropdown.values.statusDDL.active') }}</span>
                                            <span v-if="item.status === 'INACTIVE'">{{ t('components.dropdown.values.statusDDL.inactive') }}</span>
                                            <span v-if="item.status === 'DELETED'">{{ t('components.dropdown.values.statusDDL.deleted') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.join_date') }}</div>
                                        <div class="flex-1">{{ item.join_date }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.employee.fields.remarks') }}</div>
                                        <div class="flex-1">{{ item.user.profile.remarks }}</div>
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
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.employee.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.employee.actions.edit') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="employeeForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <ul class="nav nav-tabs" role="tablist">
                    <li id="tab-employee" class="nav-item flex-1" role="presentation">
                        <button class="nav-link w-full py-2 active" data-tw-toggle="pill" data-tw-target="#tab-employee-content" type="button" role="tab" aria-controls="tab-employee-content" aria-selected="true">
                            <span :class="{'text-danger':errors['code']||errors['name']|errors['email']|errors['country']|errors['tax_id']|errors['ic_num']}">{{ t('views.employee.tabs.employee') }}</span>
                        </button>
                    </li>
                    <li id="tab-access" class="nav-item flex-1" role="presentation">
                        <button class="nav-link w-full py-2" data-tw-toggle="pill" data-tw-target="#tab-access-content" type="button" role="tab" aria-controls="tab-access-content" aria-selected="false">
                            {{ t('views.employee.tabs.access') }}
                        </button>
                    </li>
                </ul>

                <div class="tab-content border-l border-r border-b">
                    <div id="tab-employee-content" class="tab-pane leading-relaxed p-5 active" role="tabpanel" aria-labelledby="tab-employee">
                        <div class="mb-3">
                            <label for="inputCode" class="form-label">{{ t('views.employee.fields.code') }}</label>
                            <div class="flex items-center">
                                <VeeField id="inputCode" name="code" type="text" :class="{'form-control':true, 'border-danger': errors['code']}" :placeholder="t('views.employee.fields.code')" :label="t('views.employee.fields.code')" rules="required" @blur="reValidate(errors)" v-model="employee.code" :readonly="employee.code === '[AUTO]'" />
                                <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                            </div>
                            <ErrorMessage name="code" class="text-danger" />
                        </div>
                        <div class="mb-3">
                            <label for="inputName" class="form-label">{{ t('views.employee.fields.name') }}</label>
                            <VeeField id="inputName" name="name" type="text" :class="{'form-control':true, 'border-danger': errors['name']}" :placeholder="t('views.employee.fields.name')" :label="t('views.employee.fields.name')" rules="required" @blur="reValidate(errors)" v-model="employee.user.name" />
                            <ErrorMessage name="name" class="text-danger" />
                        </div>
                        <div class="mb-3">
                            <label for="inputEmail" class="form-label">{{ t('views.employee.fields.email') }}</label>
                            <VeeField id="inputEmail" name="email" type="text" :class="{'form-control':true, 'border-danger': errors['email']}" rules="required|email" :placeholder="t('views.employee.fields.email')" :label="t('views.employee.fields.email')" @blur="reValidate(errors)" v-model="employee.user.email" :readonly="mode === 'edit'" />
                            <ErrorMessage name="email" class="text-danger" />
                        </div>
                    
                        <!-- 
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
                        -->
                    
                        <div class="mb-3">
                            <label for="inputAddress" class="form-label">{{ t('views.employee.fields.address') }}</label>
                            <textarea id="inputAddress" name="address" type="text" class="form-control" :placeholder="t('views.employee.fields.address')" v-model="employee.user.profile.address" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="inputCity" class="form-label">{{ t('views.employee.fields.city') }}</label>
                            <input id="inputCity" name="city" type="text" class="form-control" :placeholder="t('views.employee.fields.city')" v-model="employee.user.profile.city"/>
                        </div>
                        <div class="mb-3">
                            <label for="inputPostalCode" class="form-label">{{ t('views.employee.fields.postal_code') }}</label>
                            <input id="inputPostalCode" name="postal_code" type="text" class="form-control" :placeholder="t('views.employee.fields.postal_code')" v-model="employee.user.profile.postal_code"/>
                        </div>
                        <div class="mb-3">
                            <label for="inputCountry" class="form-label">{{ t('views.employee.fields.country') }}</label>
                            <VeeField as="select" id="inputCountry" name="country" :class="{'form-control form-select':true, 'border-danger': errors['country']}" v-model="employee.user.profile.country" rules="required" :placeholder="t('views.employee.fields.country')" :label="t('views.employee.fields.country')" @blur="reValidate(errors)">
                                <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                <option v-for="c in countriesDDL" :key="c.name" :value="c.name">{{ c.name }}</option>
                            </VeeField>
                            <ErrorMessage name="country" class="text-danger" />
                        </div>
                        <div class="mb-3">
                            <label for="inputTaxId" class="form-label">{{ t('views.employee.fields.tax_id') }}</label>
                            <VeeField id="inputTaxId" name="tax_id" type="text" :class="{'form-control':true, 'border-danger': errors['tax_id']}" :placeholder="t('views.employee.fields.tax_id')" :label="t('views.employee.fields.tax_id')" rules="required" @blur="reValidate(errors)" v-model="employee.user.profile.tax_id" />
                            <ErrorMessage name="tax_id" class="text-danger" />
                        </div>
                        <div class="mb-3">
                            <label for="inputIcNum" class="form-label">{{ t('views.employee.fields.ic_num') }}</label>
                            <VeeField id="inputIcNum" name="ic_num" type="text" :class="{'form-control':true, 'border-danger': errors['ic_num']}" :placeholder="t('views.employee.fields.ic_num')" :label="t('views.employee.fields.ic_num')" rules="required" @blur="reValidate(errors)" v-model="employee.user.profile.ic_num" />
                            <ErrorMessage name="ic_num" class="text-danger" />
                        </div>
                        <div class="mb-3" v-if="mode === 'create'">
                            <label for="inputJoinDate" class="form-label">{{ t('views.employee.fields.join_date') }}</label>
                            <VeeField name="join_date" v-slot="{ field }" rules="required" :label="t('views.employee.fields.join_date')">
                                <Litepicker v-model="employee.join_date" class="form-control" v-bind="field" :options="{ autoApply: false, showWeekNumbers: false, dropdowns: { minYear: 1990, maxYear: null, months: true, years: true, }, format: 'YYYY-MM-DD'}" />
                            </VeeField>
                            <ErrorMessage name="join_date" class="text-danger" />
                        </div>
                        <div class="mb-3">
                            <label for="inputRemarks" class="form-label">{{ t('views.employee.fields.remarks') }}</label>
                            <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.employee.fields.remarks')" v-model="employee.user.profile.remarks" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="inputStatus" class="form-label">{{ t('views.employee.fields.status') }}</label>
                            <VeeField as="select" class="form-control form-select" id="inputStatus" name="status" v-model="employee.status" rules="required" :label="t('views.employee.fields.status')">
                                <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                            </VeeField>
                            <ErrorMessage name="status" class="text-danger" />
                        </div>
                    </div>
                    <div id="tab-access-content" class="tab-pane leading-relaxed p-5" role="tabpanel" aria-labelledby="tab-access">
                        <div class="mb-3">
                            <label for="inputAccessLists" class="form-label">{{ t('views.employee.fields.access.access_lists') }}</label>                            
                            <table class="table table--sm" aria-describedby="">
                                <thead>
                                    <tr>
                                        <th colspan="2">{{ t('views.employee.fields.access.table.cols.selected') }}</th>
                                        <th>{{ t('views.employee.fields.access.table.cols.available_access') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="(a, aIdx) in accessLists">
                                        <tr>
                                            <td class="border-b dark:border-dark-5 w-10">
                                                <div class="form-switch">
                                                    <input :id="'inputAccess_' + ''" type="checkbox" name="accessCompanyIds[]" v-model="employee.selected_companies" :value="a.hId" class="form-check-input">
                                                </div>
                                            </td>
                                            <td class="w-10"></td>
                                            <td :class="{ 'line-through': ['INACTIVE', 'DELETED'].includes(a.status), 'underline': a.default }">
                                                <strong>{{ a.name }}</strong>
                                            </td>
                                        </tr>
                                        <tr v-for="(b, bIdx) in a.branches">
                                            <td class="w-10"></td>
                                            <td class="border-b dark:border-dark-5 w-10">
                                                <div class="form-switch">
                                                    <input :id="'inputAccess_' + ''" type="checkbox" name="accessBranchIds[]" v-model="employee.selected_accesses" :value="b.hId" class="form-check-input">
                                                </div>
                                            </td>
                                            <td :class="{ 'line-through': ['INACTIVE', 'DELETED'].includes(b.status), 'underline': b.is_main }">
                                                <div class="pl-5">{{ b.name }}</div>
                                            </td>
                                        </tr>
                                    </template>
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
import route from "@/ziggy";
import dom from "@left4code/tw-starter/dist/js/dom";
import { useUserContextStore } from "@/stores/user-context";
import DataList from "@/global-components/data-list/Main.vue";
import AlertPlaceholder from "@/global-components/alert-placeholder/Main.vue";
import { getCachedDDL, setCachedDDL } from "@/mixins";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - Pinia
const userContextStore = useUserContextStore();
const userContext = computed( () => userContextStore.userContext );
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
const employeeList = ref({});
const employee = ref({
    code: '',
    company: { 
            hId: '',
            name: '',
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
            status: 'ACTIVE',
        }
    },
    employee_accesses: [
        {
            hId: '',
            branch: {
                hId: '',
                name: ''
            }
        }
    ],
    selected_companies: [],
    selected_accesses: [],
    join_date: '',
    status: 'ACTIVE',
});
const countriesDDL = ref([]);
const statusDDL = ref([]);
const accessLists = ref([]);
//#endregion

//#region onMounted
onMounted(() => {
    if (selectedUserCompany.value.hId !== '') {
        getAllEmployees({ page: 1 });
        getDDLSync();
    } else  {
        
    }

    setMode();
    
    getDDL();

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

const getAllEmployees = (args) => {
    employeeList.value = {};
    let companyId = selectedUserCompany.value.hId;
    if (args.search === undefined) args.search = '';
    if (args.paginate === undefined) args.paginate = 1;
    if (args.page === undefined) args.page = 1;
    if (args.pageSize === undefined) args.pageSize = 10;

    axios.get(route('api.get.db.company.employee.list', { 
        "companyId": companyId,
        "search": args.search,
        "paginate" : 1,
        "page": args.page,
        "perPage": args.pageSize
    })).then(response => {
        employeeList.value = response.data;
        loading.value = false;
    });
}

const getDDL = () => {
    if (getCachedDDL('countriesDDL') == null) {
        axios.get(route('api.get.db.common.ddl.list.countries')).then(response => {
            countriesDDL.value = response.data;
            setCachedDDL('countriesDDL', response.data);
        });
    } else {
        countriesDDL.value = getCachedDDL('countriesDDL');
    }
    
    if (getCachedDDL('statusDDL') == null) {
        axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
            statusDDL.value = response.data;
            setCachedDDL('statusDDL', response.data);
        });    
    } else {
        statusDDL.value = getCachedDDL('statusDDL');
    }
}

const getDDLSync = () => {
    axios.get(route('api.get.db.company.company.list', {
        userId: userContext.value.hId,
        search: '',
        paginate: false
    })).then(response => {
        accessLists.value = response.data;
    });
}

const onSubmit = (values, actions) => {
    loading.value = true;

    var formData = new FormData(dom('#employeeForm')[0]); 
    formData.append('company_id', selectedUserCompany.value.hId);
    
    if (mode.value === 'create') {
        axios.post(route('api.post.db.company.employee.save'), formData).then(response => {
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === 'edit') {
        formData.append('company_id', selectedUserCompany.value.hId);

        axios.post(route('api.post.db.company.employee.edit', employee.value.uuid), formData).then(response => {
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

const emptyEmployee = () => {
    return {
        company: { 
            hId: '',
            name: '',
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
                status: 'ACTIVE',
            }
        },
        employee_accesses: [
            {
                hId: '',
                branch: {
                    hId: '',
                    name: ''
                }
            }
        ],
        selected_companies: [],
        selected_accesses: [],
        code: '[AUTO]',
        join_date: '',
        status: 'ACTIVE'
    }
}

const resetAlertErrors = () => {
    alertErrors.value = [];
}

const createNew = () => {
    mode.value = 'create';
    
    if (sessionStorage.getItem('DCSLAB_LAST_ENTITY') !== null) {
        employee.value = JSON.parse(sessionStorage.getItem('DCSLAB_LAST_ENTITY'));
        sessionStorage.removeItem('DCSLAB_LAST_ENTITY');
    } else {
        employee.value = emptyEmployee();

        employee.value.company.hId = selectedUserCompany.value.hId;
    }
}

const onDataListChange = ({page, pageSize, search}) => {
    getAllEmployees({page, pageSize, search});
}

const editSelected = (index) => {
    mode.value = 'edit';
    employee.value = employeeList.value.data[index];
}

const deleteSelected = (index) => {
    deleteId.value = employeeList.value.data[index].uuid;
    deleteModalShow.value = true;
}

const confirmDelete = () => {
    deleteModalShow.value = false;
    axios.post(route('api.post.db.company.employee.delete', deleteId.value)).then(response => {
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
    getAllEmployees({
        paginate : 1,
        page: employeeList.value.meta.current_page,
        pageSize: employeeList.value.meta.per_page 
    });
}

const toggleDetail = (idx) => {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

const handleUpload = (e) => {
    const files = e.target.files;

    let filename = files[0].name;

    const fileReader = new FileReader()
    fileReader.addEventListener('load', () => {
        employee.user.value.profile.img_path = fileReader.result
    })
    fileReader.readAsDataURL(files[0])
}

//#region Computed
const retrieveImage = computed(() => {
    if (employee.user.value.profile.img_path && employee.user.value.profile.img_path !== '') {
        if (employee.user.value.profile.img_path.includes('data:image')) {
            return employee.user.value.profile.img_path;
        } else {
            return '/storage/' + employee.user.value.profile.img_path;
        }
    } else {
        return '/images/def-user.png';
    }
});
//#endregion

const generateCode = () => {
    if (employee.value.code === '[AUTO]') employee.value.code = '';
    else  employee.value.code = '[AUTO]'
}
//#endregion

//#region Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value.hId !== '') {
        getAllEmployees({ page: 1 });
        getDDLSync();
    }
}, { deep: true });

watch(employee, (newV) => {
    if (mode.value == 'create') sessionStorage.setItem('DCSLAB_LAST_ENTITY', JSON.stringify(newV));
}, { deep: true });
//#endregion
</script>