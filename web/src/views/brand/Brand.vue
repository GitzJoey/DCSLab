<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.brand.table.title')" :data="brandList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true" >
            <template v-slot:table="tableProps">
                <table class="table table-report -mt-2" aria-describedby="">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap"> {{ t("views.brand.table.cols.code") }} </th>
                            <th class="whitespace-nowrap"> {{ t("views.brand.table.cols.name") }} </th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data" >
                            <tr class="intro-x">
                                <td>{{ item.code }}</td>
                                <td> <a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse" >{{ item.name }}</a > </td>
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
                            <tr :class="{ 'intro-x': true, 'hidden transition-all': expandDetail !== itemIdx, }" >
                                <td colspan="6">
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">
                                            {{ t("views.brand.fields.code") }}
                                        </div>
                                        <div class="flex-1">
                                            {{ item.code }}
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">
                                            {{ t("views.brand.fields.name") }}
                                        </div>
                                        <div class="flex-1">
                                            {{ item.name }}
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
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'"> {{ t("views.brand.actions.create") }} </h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'"> {{ t("views.brand.actions.edit") }} </h2>
        </div>
        <div class="loader-container">
            <VeeForm id="brandForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }" >
                <div class="p-5">
                    <!-- #region Code -->
                    <div class="mb-3">
                        <label for="inputCode" class="form-label">{{ t("views.brand.fields.code") }}</label>
                        <div class="flex items-center">
                            <VeeField id="inputCode" name="code" type="text" :class="{ 'form-control': true, 'border-danger': errors['code'], }" :placeholder="t('views.brand.fields.code')" :label="t('views.brand.fields.code')" rules="required" @blur="reValidate(errors)" v-model="brand.code" :readonly="brand.code === '[AUTO]'" />
                            <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'" > {{ t("components.buttons.auto") }} </button>
                        </div>
                        <ErrorMessage name="code" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Name -->
                    <div class="mb-3">
                        <label for="inputName" class="form-label">{{ t("views.brand.fields.name") }}</label>
                        <VeeField id="inputName" name="name" type="text" :class="{ 'form-control': true, 'border-danger': errors['name'], }" :placeholder="t('views.brand.fields.name')" :label="t('views.brand.fields.name')" rules="required" @blur="reValidate(errors)" v-model="brand.name" />
                        <ErrorMessage name="name" class="text-danger" />
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
const brandList = ref({});
const brand = ref({
    code: "",
    name: "",
});
//#endregion

//#region onMounted
onMounted(() => {
    if (selectedUserCompany.value.hId !== "") {
        getAllProductGroups({ page: 1 });
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

const getAllProductGroups = (args) => {
    brandList.value = {};
    let companyId = selectedUserCompany.value.hId;
    if (args.search === undefined) args.search = "";
    if (args.paginate === undefined) args.paginate = 1;
    if (args.page === undefined) args.page = 1;
    if (args.pageSize === undefined) args.pageSize = 10;

    axios.get( route("api.get.db.product.brand.list", {
        companyId: companyId,
        page: args.page,
        perPage: args.pageSize,
        search: args.search
    })).then((response) => {
        brandList.value = response.data;
        loading.value = false;
    });
};

const getDDL = () => {

};

const getDDLSync = () => {

};

const onSubmit = (values, actions) => {
    loading.value = true;

    var formData = new FormData(dom("#brandForm")[0]);
    formData.append("company_id", selectedUserCompany.value.hId);

    if (mode.value === "create") {
        axios.post(route("api.post.db.product.brand.save"), formData) .then((response) => {
            backToList();
        }).catch((e) => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === "edit") {
        axios.post(route("api.post.db.product.brand.edit", brand.value.uuid), formData ).then((response) => {
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

const emptyProductGroup = () => {
    return {
        code: "[AUTO]",
        name: "",
    };
}

const resetAlertErrors = () => {
    alertErrors.value = [];
}

const createNew = () => {
    mode.value = "create";

    if (sessionStorage.getItem("DCSLAB_LAST_ENTITY") !== null) {
        brand.value = JSON.parse(sessionStorage.getItem("DCSLAB_LAST_ENTITY"));
        sessionStorage.removeItem("DCSLAB_LAST_ENTITY");
    } else {
        brand.value = emptyProductGroup();
    }
}

const onDataListChange = ({ page, pageSize, search }) => {
    getAllProductGroups({ page, pageSize, search });
}

const editSelected = (index) => {
    mode.value = "edit";
    brand.value = brandList.value.data[index];
}

const deleteSelected = (index) => {
    deleteId.value = brandList.value.data[index].uuid;
    deleteModalShow.value = true;
}

const confirmDelete = () => {
    deleteModalShow.value = false;
    axios.post(route("api.post.db.product.brand.delete", deleteId.value)) .then((response) => {
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
    getAllProductGroups({ page: brandList.value.meta.current_page, pageSize: brandList.value.meta.per_page, });
}

const toggleDetail = (idx) => {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

const generateCode = () => {
    if (brand.value.code === "[AUTO]") brand.value.code = "";
    else brand.value.code = "[AUTO]";
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value.hId !== "") {
        getAllProductGroups({ page: 1 });
        getDDLSync();
    }
}, { deep: true });

watch(brand, (newV) => {
    if (mode.value == "create") sessionStorage.setItem("DCSLAB_LAST_ENTITY", JSON.stringify(newV));
}, { deep: true } );
//#endregion
</script>