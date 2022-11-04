<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.service.table.title')" :data="serviceList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true" >
            <template v-slot:table="tableProps">
                <table class="table table-report -mt-2" aria-describedby="">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap"> {{ t("views.service.table.cols.code") }} </th>
                            <th class="whitespace-nowrap"> {{ t("views.service.table.cols.name") }} </th>
                            <th class="whitespace-nowrap">{{ t('views.branch.table.cols.remarks') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.branch.table.cols.status') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data" >
                            <tr class="intro-x">
                                <td>{{ item.code }}</td>
                                <td> <a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse" >{{ item.name }}</a > </td>
                                <td>{{ item.remarks }}</td>
                                <td>
                                    <CheckCircleIcon v-if="item.status === 'ACTIVE'" />
                                    <XIcon v-if="item.status === 'INACTIVE'" />
                                    <XIcon v-if="item.status === 'DELETED'" />
                                </td>
                                <td class="table-report__action w-12">
                                    <div class="flex justify-center items-center" >
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content=" t('components.data-list.view') " @click.prevent=" showSelected(itemIdx) " >
                                            <InfoIcon class="w-4 h-4" />
                                        </Tippy>
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content=" t('components.data-list.edit') " @click.prevent=" editSelected(itemIdx) " >
                                            <CheckSquareIcon class="w-4 h-4" />
                                        </Tippy>
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content=" t('components.data-list.delete') " @click.prevent=" deleteSelected(itemIdx) " >
                                            <Trash2Icon class="w-4 h-4 text-danger" />
                                        </Tippy>
                                    </div>
                                </td>
                            </tr>
                            <tr :class="{'intro-x':true, 'hidden transition-all': expandDetail !== itemIdx}">
                                <td colspan="6">
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.service.fields.code') }}</div>
                                        <div class="flex-1">{{ item.code }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.service.fields.product_group_id') }}</div>
                                        <div class="flex-1">{{ item.product_group.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.service.fields.name') }}</div>
                                        <div class="flex-1">{{ item.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.service.fields.unit_id') }}</div>
                                        <div class="flex-1">{{ item.product_units[0].unit.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.service.fields.taxable_supply') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.taxable_supply">{{ t('components.switch.on') }}</span>
                                            <span v-else>{{ t('components.switch.off') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.service.fields.standard_rated_supply') }}</div>
                                        <div class="flex-1">{{ item.standard_rated_supply }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.service.fields.point') }}</div>
                                        <div class="flex-1">{{ item.point }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.service.fields.price_include_vat') }}</div>
                                    <div class="flex-1">
                                        <span v-if="item.price_include_vat">{{ t('components.switch.on') }}</span>
                                        <span v-else>{{ t('components.switch.off') }}</span>
                                    </div>
                                </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.service.fields.point') }}</div>
                                        <div class="flex-1">{{ item.point }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.service.fields.remarks') }}</div>
                                        <div class="flex-1">{{ item.remarks }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.service.fields.status') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.status === 'ACTIVE'">{{ t('components.dropdown.values.statusDDL.active') }}</span>
                                            <span v-if="item.status === 'INACTIVE'">{{ t('components.dropdown.values.statusDDL.inactive') }}</span>
                                            <span v-if="item.status === 'DELETED'">{{ t('components.dropdown.values.statusDDL.deleted') }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <Modal :show="deleteModalShow" @hidden="deleteModalShow = false" >
                    <ModalBody class="p-0">
                        <div class="p-5 text-center">
                            <XCircleIcon class="w-16 h-16 text-danger mx-auto mt-3" />
                            <div class="text-3xl mt-5">
                                {{ t( "components.data-list.delete_confirmation.title" ) }}
                            </div>
                            <div class="text-slate-600 mt-2">
                                {{ t( "components.data-list.delete_confirmation.desc_1" ) }}<br />{{ t( "components.data-list.delete_confirmation.desc_2" ) }}
                            </div>
                        </div>
                        <div class="px-5 pb-8 text-center">
                            <button type="button" class="btn btn-outline-secondary w-24 mr-1" @click="deleteModalShow = false" >
                                {{ t("components.buttons.cancel") }}
                            </button>
                            <button type="button" class="btn btn-danger w-24" @click="confirmDelete" >
                                {{ t("components.buttons.delete") }}
                            </button>
                        </div>
                    </ModalBody>
                </Modal>
            </template>
        </DataList>
    </div>

    <div class="intro-y box" v-if="mode !== 'list'">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5" >
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'"> {{ t("views.service.actions.create") }} </h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'"> {{ t("views.service.actions.edit") }} </h2>
        </div>
        <div class="loader-container">
            <VeeForm id="serviceForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }" >
                <div class="p-5">
                    <!-- #region Code -->
                    <div class="mb-3">
                        <label for="inputCode" class="form-label">{{ t("views.service.fields.code") }}</label>
                        <div class="flex items-center">
                            <VeeField id="inputCode" name="code" type="text" :class="{ 'form-control': true, 'border-danger': errors['code'], }" :placeholder="t('views.service.fields.code')" :label="t('views.service.fields.code')" rules="required" @blur="reValidate(errors)" v-model="service.code" :readonly="service.code === '[AUTO]'"/>
                            <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'" > {{ t("components.buttons.auto") }} </button>
                        </div>
                        <ErrorMessage name="code" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Product Group -->
                    <div class="mb-3">
                        <label class="form-label" for="product_group_id">{{ t('views.service.fields.product_group_id') }}</label>
                        <select id="product_group_id" name="product_group_id" class="form-control form-select" v-model="service.product_group.hId">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option :value="g.hId" v-for="g in productGroupDDL" v-bind:key="g.hId">{{ g.code }} - {{ g.name }}</option>
                        </select>
                        <ErrorMessage name="product_group_id" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Name -->
                    <div class="mb-3">
                        <label for="inputName" class="form-label">{{ t("views.service.fields.name") }}</label>
                        <VeeField id="inputName" name="name" type="text" :class="{ 'form-control': true, 'border-danger': errors['name'], }" :placeholder="t('views.service.fields.name')" :label="t('views.service.fields.name')" rules="required" @blur="reValidate(errors)" v-model="service.name"/>
                        <ErrorMessage name="name" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Product Units -->
                        <!-- #region Code -->
                        <td>
                            <input type="hidden" :name="'product_units_hId[]'" v-model="service.product_units[0].hId" />
                            <input type="hidden" name="product_units_code[]" v-model="service.product_units[0].code"/>
                        </td>
                        <!-- #endregion -->
                    
                        <!-- #region Unit -->
                        <div class="mb-3">
                            <label class="form-label" for="product_units_unit_hId">{{ t('views.service.fields.unit_id') }}</label>
                            <select id="product_units_unit_hId" name="product_units_unit_hId[]" class="form-control form-select" v-model="service.product_units[0].unit.hId">
                                <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                <option :value="u.hId" v-for="u in unitDDL" v-bind:key="u.hId">{{ u.code }} - {{ u.name }}</option>
                            </select>
                            <ErrorMessage name="product_units_unit_hId" class="text-danger" />
                        </div>
                        <!-- #endregion -->

                        <!-- #region Is Base -->
                        <td>
                            <input type="hidden" name="product_units_is_base[]" v-model="service.product_units[0].is_base"/>
                        </td>
                        <!-- #endregion -->

                        <!-- #region Conversion Value -->
                        <td>
                            <input type="hidden" name="product_units_conv_value[]" v-model="service.product_units[0].conversion_value"/>
                        </td>
                        <!-- #endregion -->

                        <!-- #region Is Primary Unit -->
                        <td>
                            <input type="hidden" name="product_units_is_primary_unit[]" v-model="service.product_units[0].is_primary_unit"/>
                        </td>
                        <!-- #endregion -->

                        <!-- #region Remarks -->
                        <td>
                            <input type="hidden" name="product_units_remarks[]" v-model="service.product_units[0].remarks"/>
                        </td>
                        <!-- #endregion -->
                    <!-- #endregion -->
                    
                    <!-- #region Product Type -->
                    <td>
                        <input type="hidden" v-model="service.product_type" name="product_type"/>
                    </td>
                    <!-- #endregion -->

                    <!-- #region Taxable Supply -->
                    <div class="mb-3">
                        <label for="inputTaxableSupply" class="form-label">{{ t('views.service.fields.taxable_supply') }}</label>
                        <div class="form-switch mt-2">
                            <input id="inputTaxableSupply" type="checkbox" class="form-check-input" name="taxable_supply" v-model="service.taxable_supply" :true-value="1" :false-value="0">
                        </div>
                    </div>
                    <!-- #endregion -->

                    <!-- #region Standard Rated Supply -->
                    <div class="mb-3">
                        <label for="inputStandardRatedSupply" class="form-label">{{ t('views.service.fields.standard_rated_supply') }}</label>
                        <input id="inputStandardRatedSupply" name="standard_rated_supply" type="text" class="form-control" :placeholder="t('views.service.fields.standard_rated_supply')" v-model="service.standard_rated_supply" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Price Include VAT -->
                    <div class="mb-3">
                        <label for="inputPriceIncludeVAT" class="form-label">{{ t('views.service.fields.price_include_vat') }}</label>
                        <div class="form-switch mt-2">
                            <input id="inputPriceIncludeVAT" type="checkbox" class="form-check-input" name="price_include_vat" v-model="service.price_include_vat" :true-value="1" :false-value="0">
                        </div>
                    </div>
                    <!-- #endregion -->

                    <!-- #region Point -->
                    <div class="mb-3">
                        <label for="inputPoint" class="form-label">{{ t('views.service.fields.point') }}</label>
                        <input id="inputPoint" name="point" type="number" class="form-control" :placeholder="t('views.service.fields.point')" v-model="service.point" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Remarks -->
                    <div class="mb-3">
                        <label for="inputRemarks" class="form-label">{{ t('views.service.fields.remarks') }}</label>
                        <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.service.fields.remarks')" v-model="service.remarks" rows="3"></textarea>
                    </div>
                    <!-- #endregion -->

                    <!-- #region Status -->
                    <div class="mb-3">
                        <label for="inputStatus" class="form-label">{{ t('views.service.fields.status') }}</label>
                        <VeeField as="select" :class="{'form-control form-select':true, 'border-danger':errors['status']}" id="inputStatus" name="status" :label="t('views.service.fields.status')" rules="required" @blur="reValidate(errors)" v-model="service.status">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </VeeField>
                        <ErrorMessage name="status" class="text-danger" />
                    </div>
                    <!-- #endregion -->
                </div>
                <div class="pl-5" v-if="mode === 'create' || mode === 'edit'">
                    <button type="submit" class="btn btn-primary w-24 mr-3"> {{ t("components.buttons.save") }} </button>
                    <button type="button" class="btn btn-secondary" @click=" handleReset(); resetAlertErrors(); " > {{ t("components.buttons.reset") }} </button>
                </div>
            </VeeForm>
            <div class="loader-overlay" v-if="loading"></div>
        </div>
        <hr />
        <div>
            <button type="button" class="btn btn-secondary w-15 m-3" @click="backToList" > {{ t("components.buttons.back") }} </button>
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
const selectedUserCompany = computed( () => userContextStore.selectedUserCompany );
//#endregion

//#region Data - UI
const mode = ref("list");
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref("");
const deleteModalShow = ref(false);
const expandDetail = ref(null);
//#endregion

//#region Data - Views
const serviceList = ref({});
const service = ref({
    code: '[AUTO]',
    product_group: { 
        hId: '',
        name: '' 
    },
    brand: {
        hId: '',
        name: ''
    },
    name: '',
    product_units: [
        {
            hId: '0',
            code: '[AUTO]',
            is_base: 1,
            conversion_value: 1,
            is_primary_unit: 1,
            remarks: '',
            unit: {
                hId: '',
                name: '',
            }
        }
    ],
    product_type: 'SERVICE',
    taxable_supply: false,
    standard_rated_supply: 0,
    price_include_vat: false,
    point: '',
    remarks: '',
    status: 'ACTIVE',
});
const statusDDL = ref([]);
const productGroupDDL = ref([]);
const unitDDL = ref([]);
//#endregion

//#region onMounted
onMounted(() => {
    if (selectedUserCompany.value.hId !== "") {
        getAllService({ page: 1 });
        getDDLSync();
    } else {
    }

    getDDL();

    setMode();

    loading.value = false;
});

onUnmounted(() => {
    sessionStorage.removeItem("DCSLAB_LAST_ENTITY");
});
//#endregion

//#region Methods
const setMode = () => {
    if (sessionStorage.getItem("DCSLAB_LAST_ENTITY") !== null) createNew();
};

const getAllService = (args) => {
    serviceList.value = {};
    let companyId = selectedUserCompany.value.hId;
    
    if (args.search === undefined) args.search = '';
    if (args.paginate === undefined) args.paginate = 1;
    if (args.page === undefined) args.page = 1;
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.useCache === undefined) args.useCache = true;

    axios.get( route("api.get.db.product.service.list", {
        companyId: companyId,
        productCategory: 'SERVICES',
        search: args.search,
        page: args.page,
        perPage: args.pageSize,
        useCache: args.useCache
    })).then((response) => {
        serviceList.value = response.data;
        loading.value = false;
    });
};

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
    axios.get(route('api.get.db.product.product_group.list', {
        companyId: selectedUserCompany.value.hId,
        search:'',
        category: 'SERVICES',
        paginate: false
    })).then(response => {
        productGroupDDL.value = response.data;
    });

    axios.get(route('api.get.db.product.unit.list', {
        companyId: selectedUserCompany.value.hId,
        search:'',
        category: 'SERVICES',
        paginate: false
    })).then(response => {
        unitDDL.value = response.data;
    });
}

const onSubmit = (values, actions) => {
    loading.value = true;

    var formData = new FormData(dom("#serviceForm")[0]);
    formData.append("company_id", selectedUserCompany.value.hId);

    if (mode.value === "create") {
        axios.post(route("api.post.db.product.service.save"), formData) .then((response) => {
            backToList();
        }).catch((e) => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === "edit") {
        axios.post(route("api.post.db.product.service.edit", service.value.uuid), formData ).then((response) => {
            actions.resetForm();
            backToList();
        }).catch((e) => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else {}
};

const handleError = (e, actions) => {
    //Laravel Validations
    if ( e.response.data.errors !== undefined && Object.keys(e.response.data.errors).length > 0 ) {
        for (var key in e.response.data.errors) {
            for (var i = 0; i < e.response.data.errors[key].length; i++) {
                actions.setFieldError(key, e.response.data.errors[key][i]);
            }
        }
        alertErrors.value = e.response.data.errors;
    } else {
        //Catch From Controller
        alertErrors.value = {
            controller: e.response.status + " " + e.response.statusText + ": " + e.response.data.message,
        };
    }
};

const invalidSubmit = (e) => {
    alertErrors.value = e.errors;
    if (dom(".border-danger").length !== 0) dom(".border-danger")[0].scrollIntoView({ behavior: "smooth" });
}

function reValidate(errors) {
    alertErrors.value = errors;
}

const emptyService = () => {
    return {
        code: '[AUTO]',
        product_group: { 
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
                remarks: '',
                unit: {
                    hId: '',
                    name: '',
                }
            }
        ],
        product_type: 'SERVICE',
        taxable_supply: false,
        standard_rated_supply: 0,
        price_include_vat: false,
        point: 0,
        remarks: '',
        status: 'ACTIVE',
    };
}

const resetAlertErrors = () => {
    alertErrors.value = [];
}

const createNew = () => {
    mode.value = "create";

    if (sessionStorage.getItem("DCSLAB_LAST_ENTITY") !== null) {
        service.value = JSON.parse(sessionStorage.getItem("DCSLAB_LAST_ENTITY"));
        sessionStorage.removeItem("DCSLAB_LAST_ENTITY");
    } else {
        service.value = emptyService();
    }
}

const onDataListChange = ({search, paginate, page, perPage, useCache}) => {
    getAllService({search, paginate, page, perPage, useCache});
}

const editSelected = (index) => {
    mode.value = "edit";
    service.value = serviceList.value.data[index];
}

const deleteSelected = (index) => {
    deleteId.value = serviceList.value.data[index].uuid;
    deleteModalShow.value = true;
}

const confirmDelete = () => {
    deleteModalShow.value = false;

    axios.post(route("api.post.db.product.service.delete", deleteId.value)).then((response) => {
        backToList();
    }).catch((e) => {
        alertErrors.value = e.response.data;
    }).finally(() => {

    });
}

const showSelected = (index) => {
    toggleDetail(index);
}

const backToList = () => {
    resetAlertErrors();
    sessionStorage.removeItem("DCSLAB_LAST_ENTITY");

    mode.value = "list";
    getAllService({ page: serviceList.value.meta.current_page, pageSize: serviceList.value.meta.per_page, });
}

const toggleDetail = (idx) => {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

const generateCode = () => {
    if (service.value.code === "[AUTO]") service.value.code = "";
    else service.value.code = "[AUTO]";
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value.hId !== "") {
        getAllService({ page: 1 });
        getDDLSync();
    }
}, { deep: true });

watch(service, (newV) => {
    if (mode.value == "create") sessionStorage.setItem("DCSLAB_LAST_ENTITY", JSON.stringify(newV));
}, { deep: true } );
//#endregion
</script>