<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.unit.table.title')" :data="unitList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
           <template v-slot:table="tableProps">
                <table class="table table-report -mt-2" aria-describedby="">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.unit.table.cols.code') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.unit.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.unit.table.cols.description') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.unit.table.cols.category') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data">
                            <tr class="intro-x">
                                <td>{{ item.code }}</td>
                                <td><a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse">{{ item.name }}</a></td>
                                <td>{{ item.description }}</td>
                                <td>
                                    <span v-if="item.category == 'PRODUCTS'">{{ t('components.dropdown.values.unitCategoryDDL.product') }}</span>
                                    <span v-if="item.category == 'SERVICES'">{{ t('components.dropdown.values.unitCategoryDDL.service') }}</span>
                                    <span v-if="item.category == 'PRODUCTS_AND_SERVICES'">{{ t('components.dropdown.values.unitCategoryDDL.product_and_service') }}</span>
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
                                <td colspan="6">
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.unit.fields.code') }}</div>
                                        <div class="flex-1">{{ item.code }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.unit.fields.name') }}</div>
                                        <div class="flex-1">{{ item.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.unit.fields.description') }}</div>
                                        <div class="flex-1">{{ item.description }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.unit.fields.category') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.category == 'PRODUCTS'">{{ t('components.dropdown.values.unitCategoryDDL.product') }}</span>
                                            <span v-if="item.category == 'SERVICES'">{{ t('components.dropdown.values.unitCategoryDDL.service') }}</span>
                                            <span v-if="item.category == 'PRODUCTS_AND_SERVICES'">{{ t('components.dropdown.values.unitCategoryDDL.product_and_service') }}</span>
                                        </div>
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
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.unit.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.unit.actions.edit') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="unitForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <div class="p-5">
                    <!-- #region Code -->
                    <div class="mb-3">
                        <label for="inputCode" class="form-label">{{ t('views.unit.fields.code') }}</label>
                        <div class="flex items-center">
                            <VeeField id="inputCode" name="code" type="text" :class="{'form-control':true, 'border-danger': errors['code']}" :placeholder="t('views.unit.fields.code')" :label="t('views.unit.fields.code')" rules="required" @blur="reValidate(errors)" v-model="unit.code" :readonly="unit.code === '[AUTO]'" />
                            <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                        </div>
                        <ErrorMessage name="code" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Name -->
                    <div class="mb-3">
                        <label for="inputName" class="form-label">{{ t('views.unit.fields.name') }}</label>
                        <VeeField id="inputName" name="name" type="text" :class="{'form-control':true, 'border-danger': errors['name']}" :placeholder="t('views.unit.fields.name')" :label="t('views.unit.fields.name')" rules="required" @blur="reValidate(errors)" v-model="unit.name" />
                        <ErrorMessage name="name" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Description -->
                    <div class="mb-3">
                        <label for="inputDescription" class="form-label">{{ t('views.unit.fields.description') }}</label>
                        <textarea id="inputDescription" name="description" type="text" class="form-control" :placeholder="t('views.unit.fields.description')" v-model="unit.description" rows="3"></textarea>
                    </div>
                    <!-- #endregion -->
                    
                    <!-- #region Unit Category -->
                    <div class="mb-3">
                        <label for="category" class="form-label">{{ t('views.unit.fields.category') }}</label>
                        <VeeField as="select" id="category" name="category" :class="{'form-control form-select':true, 'border-danger': errors['category']}" v-model="unit.category" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in unitCategoryDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </VeeField>
                        <ErrorMessage name="category" class="text-danger" />
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
const unitList = ref({});
const unit = ref({
    code: '',
    name: '',
    description: '',
    category: '',
});
const unitCategoryDDL = ref([]);
//#endregion

//#region onMounted
onMounted(() => {
    if (selectedUserCompany.value.hId !== '') {
        getAllUnits({ page: 1 });
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

const getAllUnits = (args) => {
    unitList.value = {};
    let companyId = selectedUserCompany.value.hId;
    if (args.search === undefined) args.search = '';
    if (args.paginate === undefined) args.paginate = 1;
    if (args.page === undefined) args.page = 1;
    if (args.pageSize === undefined) args.pageSize = 10;

    axios.get(route('api.get.db.product.unit.list', {
        "companyId": companyId,
        "category": "PRODUCTS_AND_SERVICES",
        "page": args.page,
        "perPage": args.pageSize,
        "search": args.search
    })).then(response => {
        unitList.value = response.data;
        loading.value = false;
    });
}

const getDDL = () => {
    if (getCachedDDL('unitCategoryDDL') == null) {
        axios.get(route('api.get.db.common.ddl.list.productgroupcategories')).then(response => {
            unitCategoryDDL.value = response.data;
            setCachedDDL('unitCategoryDDL', response.data);
        });
    } else {
        unitCategoryDDL.value = getCachedDDL('unitCategoryDDL');
    }
}

const getDDLSync = () => {

}

const onSubmit = (values, actions) => {
    loading.value = true;

    var formData = new FormData(dom('#unitForm')[0]);
    formData.append('company_id', selectedUserCompany.value.hId);
    
    if (mode.value === 'create') {
        axios.post(route('api.post.db.product.unit.save'), formData).then(response => {
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === 'edit') {
        axios.post(route('api.post.db.product.unit.edit', unit.value.uuid), formData).then(response => {
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

const emptyUnit = () => {
    return {
        code: '[AUTO]',
        name: '',
        description: '',
        category: '',
    }
}

const resetAlertErrors = () => {
    alertErrors.value = [];
}

const createNew = () => {
    mode.value = 'create';
    
    if (sessionStorage.getItem('DCSLAB_LAST_ENTITY') !== null) {
        unit.value = JSON.parse(sessionStorage.getItem('DCSLAB_LAST_ENTITY'));
        sessionStorage.removeItem('DCSLAB_LAST_ENTITY');
    } else {
        unit.value = emptyUnit();
    }
}

const onDataListChange = ({page, pageSize, search}) => {
    getAllUnits({page, pageSize, search});
}

const editSelected = (index) => {
    mode.value = 'edit';
    unit.value = unitList.value.data[index];
}

const deleteSelected = (index) => {
    deleteId.value = unitList.value.data[index].uuid;
    deleteModalShow.value = true;
}

const confirmDelete = () => {
    deleteModalShow.value = false;
    axios.post(route('api.post.db.product.unit.delete', deleteId.value)).then(response => {
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
    getAllUnits({ page: unitList.value.meta.current_page, pageSize: unitList.value.meta.per_page });
}

const toggleDetail = (idx) => {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

const generateCode = () => {
    if (unit.value.code === '[AUTO]') unit.value.code = '';
    else  unit.value.code = '[AUTO]'
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value.hId !== '') {
        getAllUnits({ page: 1 });
        getDDLSync();
    }
}, { deep: true });

watch(unit, (newV) => {
    if (mode.value == 'create') sessionStorage.setItem('DCSLAB_LAST_ENTITY', JSON.stringify(newV));
}, { deep: true });
//#endregion
</script>