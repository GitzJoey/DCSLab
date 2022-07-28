<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.warehouse.table.title')" :data="warehouseList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
           <template v-slot:table="tableProps">
                <table class="table table-report -mt-2" aria-describedby="">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.warehouse.table.cols.code') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.warehouse.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.warehouse.table.cols.status') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.warehouse.table.cols.remarks') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data">
                            <tr :class="{ 'intro-x':true, 'line-through':item.status === 'DELETED' }">
                                <td>{{ item.code }}</td>
                                <td><a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse">{{ item.name }}</a></td>
                                <td>
                                    <CheckCircleIcon v-if="item.status === 'ACTIVE'" />
                                    <XIcon v-if="item.status === 'INACTIVE'" />
                                    <XIcon v-if="item.status === 'DELETED'" />
                                </td>
                                <td>{{ item.remarks }}</td>
                                <td class="table-report__action w-12">
                                    <div class="flex justify-center items-center">
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.view')" @click.prevent="showSelected(itemIdx)">
                                            <InfoIcon class="w-4 h-4" />
                                        </Tippy>
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.edit')" @click.prevent="editSelected(itemIdx)">
                                            <CheckSquareIcon class="w-4 h-4" />
                                        </Tippy>
                                        <template v-if="item.status === 'DELETED'">
                                        </template>
                                        <template v-else>
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
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.warehouse.fields.company_id') }}</div>
                                        <div class="flex-1">{{ item.company.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.warehouse.fields.branch_id') }}</div>
                                        <div class="flex-1">{{ item.branch.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.warehouse.fields.code') }}</div>
                                        <div class="flex-1">{{ item.code }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.warehouse.fields.name') }}</div>
                                        <div class="flex-1">{{ item.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.warehouse.fields.status') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.status === 'ACTIVE'">{{ t('components.dropdown.values.statusDDL.active') }}</span>
                                            <span v-if="item.status === 'INACTIVE'">{{ t('components.dropdown.values.statusDDL.inactive') }}</span>
                                            <span v-if="item.status === 'DELETED'">{{ t('components.dropdown.values.statusDDL.deleted') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.warehouse.fields.remarks') }}</div>
                                        <div class="flex-1">{{ item.remarks }}</div>
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
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.warehouse.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.warehouse.actions.edit') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="warehouseForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <div class="p-5">
                    <div class="mb-3">
                        <label class="form-label" for="inputCompany_id">{{ t('views.warehouse.fields.company_id') }}</label>
                        <VeeField as="select" id="company_id" name="company_id" :class="{'form-control form-select':true, 'border-danger': errors['company_id']}" v-model="warehouse.company.hId" :label="t('views.warehouse.fields.company_id')" rules="required" @blur="reValidate(errors)" :disabled="mode === 'edit'">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in companyDDL" :value="c.hId">{{ c.name }}</option>
                        </VeeField>
                        <ErrorMessage name="company_id" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="inputBranch_id">{{ t('views.warehouse.fields.branch_id') }}</label>
                        <VeeField as="select" id="branch_id" name="branch_id" :class="{'form-control form-select':true, 'border-danger': errors['branch_id']}" v-model="warehouse.branch.hId" :label="t('views.warehouse.fields.branch_id')" rules="required" @blur="reValidate(errors)" :disabled="mode === 'edit'">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in branchDDL" :value="c.hId">{{ c.name }}</option>
                        </VeeField>
                        <ErrorMessage name="branch_id" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputCode" class="form-label">{{ t('views.warehouse.fields.code') }}</label>
                        <div class="flex items-center">
                            <VeeField id="inputCode" name="code" type="text" :class="{'form-control':true, 'border-danger': errors['code']}" :placeholder="t('views.warehouse.fields.code')" :label="t('views.warehouse.fields.code')" rules="required" @blur="reValidate(errors)" v-model="warehouse.code" :readonly="warehouse.code === '[AUTO]'" />
                            <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                        </div>
                        <ErrorMessage name="code" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputName" class="form-label">{{ t('views.warehouse.fields.name') }}</label>
                        <VeeField id="inputName" name="name" type="text" :class="{'form-control':true, 'border-danger': errors['name']}" :placeholder="t('views.warehouse.fields.name')" :label="t('views.warehouse.fields.name')" rules="required" @blur="reValidate(errors)" v-model="warehouse.name" />
                        <ErrorMessage name="name" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputAddress" class="form-label">{{ t('views.warehouse.fields.address') }}</label>
                        <textarea id="inputAddress" name="address" type="text" class="form-control" :placeholder="t('views.warehouse.fields.address')" v-model="warehouse.address" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="inputCity" class="form-label">{{ t('views.warehouse.fields.city') }}</label>
                        <input id="inputCity" name="city" type="text" class="form-control" :placeholder="t('views.warehouse.fields.city')" v-model="warehouse.city" />
                    </div>
                    <div class="mb-3">
                        <label for="inputContact" class="form-label">{{ t('views.warehouse.fields.contact') }}</label>
                        <input id="inputContact" name="contact" type="text" class="form-control" :placeholder="t('views.warehouse.fields.contact')" v-model="warehouse.contact" />
                    </div>
                    <div class="mb-3">
                        <label for="inputRemarks" class="form-label">{{ t('views.warehouse.fields.remarks') }}</label>
                        <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.warehouse.fields.remarks')" v-model="warehouse.remarks" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">{{ t('views.warehouse.fields.status') }}</label>
                        <VeeField as="select" id="status" name="status" :class="{'form-control form-select':true, 'border-danger': errors['status']}" v-model="warehouse.status" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </VeeField>
                        <ErrorMessage name="status" class="text-danger" />
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
import { onMounted, onUnmounted, ref, computed, watch, inject } from "vue";
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
const _ = inject('$_');
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
const warehouseList = ref({});
const warehouse = ref({
    company: { 
        hId: '',
        name: '' 
    },
    branch: { 
        hId: '',
        name: '' 
    },
    code: '',
    name: '',
    address: '',
    city: '',
    contact: '',
    remarks: '',
    status: 'ACTIVE',
});
const statusDDL = ref([]);
const companyDDL = ref([]);
const branchDDL = ref([]);
//#endregion

//#region onMounted
onMounted(() => {
    if (selectedUserCompany.value.hId !== '') {
        getAllWarehouse({ page: 1 });
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

const getAllWarehouse = (args) => {
    warehouseList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    let companyId = selectedUserCompany.value.hId;

    axios.get(route('api.get.db.company.warehouse.list', { "companyId": companyId, "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        warehouseList.value = response.data;
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
}

const getDDLSync = () => {
    axios.get(route('api.get.db.company.company.list', {
        userId: userContext.value.hId,
        search: '',
        paginate: false
    })).then(response => {
        companyDDL.value = response.data;
    });

    axios.get(route('api.get.db.company.branch.read.by.company', [selectedUserCompany.value.uuid])).then(response => {
        branchDDL.value = response.data;
    });
}

const onSubmit = (values, actions) => {
    loading.value = true;

    var formData = new FormData(dom('#warehouseForm')[0]); 
    
    if (mode.value === 'create') {
        axios.post(route('api.post.db.company.warehouse.save'), formData).then(response => {
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === 'edit') {
        formData.append('company_id', selectedUserCompany.value.hId);

        var branchId = document.getElementById("branch_id");
        formData.append('branch_id', branchId.value);
        
        axios.post(route('api.post.db.company.warehouse.edit', warehouse.value.uuid), formData).then(response => {
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

const emptyWarehouse = () => {
    return {
        company: {
            hId: '',
            name: ''
        },
        branch: {
            hId: '',
            name: ''
        },
        code: '[AUTO]',
        name: '',
        address: '',
        city: '',
        contact: '',
        remarks: '',
        status: 'ACTIVE',
    }
}

const resetAlertErrors = () => {
    alertErrors.value = [];
}

const createNew = () => {
    mode.value = 'create';
    
    if (sessionStorage.getItem('DCSLAB_LAST_ENTITY') !== null) {
        warehouse.value = JSON.parse(sessionStorage.getItem('DCSLAB_LAST_ENTITY'));
        sessionStorage.removeItem('DCSLAB_LAST_ENTITY');
    } else {
        warehouse.value = emptyWarehouse();

        warehouse.value.company.hId = selectedUserCompany.value.hId;
    }
}

const onDataListChange = ({page, pageSize, search}) => {
    getAllWarehouse({page, pageSize, search});
}

const editSelected = (index) => {
    mode.value = 'edit';
    warehouse.value = warehouseList.value.data[index];
}

const deleteSelected = (index) => {
    deleteId.value = warehouseList.value.data[index].uuid;
    deleteModalShow.value = true;
}

const confirmDelete = () => {
    deleteModalShow.value = false;
    axios.post(route('api.post.db.company.warehouse.delete', deleteId.value)).then(response => {
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
    getAllWarehouse({ page: warehouseList.value.meta.current_page, pageSize: warehouseList.value.meta.per_page });
}

const toggleDetail = (idx) => {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

const generateCode = () => {
    if (warehouse.value.code === '[AUTO]') warehouse.value.code = '';
    else  warehouse.value.code = '[AUTO]'
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value.hId !== '') {
        getAllWarehouse({ page: 1 });
        getDDLSync();
    }
}, { deep: true });

watch(warehouse, (newV) => {
    if (mode.value == 'create') sessionStorage.setItem('DCSLAB_LAST_ENTITY', JSON.stringify(newV));
}, { deep: true });
//#endregion
</script>