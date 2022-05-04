<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.user.table.title')" :data="userList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
            <template v-slot:table="tableProps">
                <table class="table table-report -mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.user.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.user.table.cols.email') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.user.table.cols.roles') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.user.table.cols.status') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data">
                            <tr class="intro-x">
                                <td>{{ item.name }}</td>
                                <td><a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse">{{ item.email }}</a></td>
                                <td>
                                    <span v-for="(r, rIdx) in item.roles">{{ r.display_name }}</span>
                                </td>
                                <td>
                                    <CheckCircleIcon v-if="item.profile.status === 'ACTIVE'" />
                                    <XIcon v-if="item.profile.status === 'INACTIVE'" />
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
                                <td colspan="5">
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.name') }}</div>
                                        <div class="flex-1">{{ item.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.email') }}</div>
                                        <div class="flex-1">{{ item.email }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.roles') }}</div>
                                        <div class="flex-1">{{ item.roles_description }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.user.fields.status') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.profile.status === 'ACTIVE'">{{ t('components.dropdown.values.statusDDL.active') }}</span>
                                            <span v-if="item.profile.status === 'INACTIVE'">{{ t('components.dropdown.values.statusDDL.inactive') }}</span>
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
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.user.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.user.actions.edit') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="userForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <div class="p-5">
                    <div class="mb-3">
                        <label for="inputName" class="form-label">{{ t('views.user.fields.name') }}</label>
                        <VeeField id="inputName" name="name" type="text" :class="{'form-control':true, 'border-danger': errors['name']}" rules="required|alpha_num" :placeholder="t('views.user.fields.name')" :label="t('views.user.fields.name')" @blur="reValidate(errors)" v-model="user.name" />
                        <ErrorMessage name="name" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputEmail" class="form-label">{{ t('views.user.fields.email') }}</label>
                        <VeeField id="inputEmail" name="email" type="text" :class="{'form-control':true, 'border-danger': errors['email']}" rules="required|email" :placeholder="t('views.user.fields.email')" :label="t('views.user.fields.email')" @blur="reValidate(errors)" v-model="user.email" :readonly="mode === 'edit'" />
                        <ErrorMessage name="email" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputImg" class="form-label">{{ t('views.user.fields.picture') }}</label>
                        <div class="">
                            <div class="my-1">
                                <img id="inputImg" alt="" class="" :src="retrieveImage">
                            </div>
                            <div class="">
                                <input type="file" class="h-full w-full" name="img_path" v-if="mode === 'create' || mode === 'edit'" v-on:change="handleUpload" />
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="inputFirstName" class="form-label">{{ t('views.user.fields.first_name') }}</label>
                        <input id="inputFirstName" name="first_name" type="text" class="form-control" :placeholder="t('views.user.fields.first_name')" v-model="user.profile.first_name" />
                    </div>
                    <div class="mb-3">
                        <label for="inputLastName" class="form-label">{{ t('views.user.fields.last_name') }}</label>
                        <input id="inputLastName" name="last_name" type="text" class="form-control" :placeholder="t('views.user.fields.last_name')" v-model="user.profile.last_name" />
                    </div>
                    <div class="mb-3">
                        <label for="inputAddress" class="form-label">{{ t('views.user.fields.address') }}</label>
                        <input id="inputAddress" name="address" type="text" class="form-control" :placeholder="t('views.user.fields.address')" v-model="user.profile.address" />
                    </div>
                    <div class="mb-3">
                        <label for="inputCity" class="form-label">{{ t('views.user.fields.city') }}</label>
                        <input id="inputCity" name="city" type="text" class="form-control" :placeholder="t('views.user.fields.city')" v-model="user.profile.city" />
                    </div>
                    <div class="mb-3">
                        <label for="inputPostalCode" class="form-label">{{ t('views.user.fields.postal_code') }}</label>
                        <input id="inputPostalCode" name="postal_code" type="text" class="form-control" :placeholder="t('views.user.fields.postal_code')" v-model="user.profile.postal_code" />
                    </div>
                    <div class="mb-3">
                        <label for="inputCountry" class="form-label">{{ t('views.user.fields.country') }}</label>
                        <VeeField as="select" id="inputCountry" name="country" :class="{'form-control form-select':true, 'border-danger': errors['country']}" v-model="user.profile.country" rules="required" :placeholder="t('views.user.fields.country')" :label="t('views.user.fields.country')" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in countriesDDL" :key="c.name" :value="c.name">{{ c.name }}</option>
                        </VeeField>
                        <ErrorMessage name="country" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputTaxId" class="form-label">{{ t('views.user.fields.tax_id') }}</label>
                        <VeeField id="inputTaxId" name="tax_id" type="text" :class="{'form-control':true, 'border-danger': errors['tax_id']}" rules="required" :placeholder="t('views.user.fields.tax_id')" :label="t('views.user.fields.tax_id')" @blur="reValidate(errors)" v-model="user.profile.tax_id" />
                        <ErrorMessage name="tax_id" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputICNum" class="form-label">{{ t('views.user.fields.ic_num') }}</label>
                        <VeeField id="inputICNum" name="ic_num" type="text" :class="{'form-control':true, 'border-danger': errors['ic_num']}" rules="required" :placeholder="t('views.user.fields.ic_num')" :label="t('views.user.fields.ic_num')" @blur="reValidate(errors)" v-model="user.profile.ic_num" />
                        <ErrorMessage name="ic_num" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputRoles" class="form-label">{{ t('views.user.fields.roles') }}</label>
                        <VeeField as="select" multiple v-slot="{ value }" :class="{'form-control':true, 'border-danger':errors['roles[]']}" id="inputRoles" name="roles[]" size="6" v-model="user.selected_roles" rules="required" :label="t('views.user.fields.roles')" @blur="reValidate(errors)">
                            <option v-for="r in rolesDDL" :key="r.hId" :value="r.hId" :selected="value.includes(r.hId)">{{ r.display_name }}</option>
                        </VeeField>
                        <ErrorMessage name="roles[]" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputStatus" class="form-label">{{ t('views.user.fields.status') }}</label>
                        <VeeField as="select" class="form-control form-select" id="inputStatus" name="status" v-model="user.profile.status" rules="required" :label="t('views.user.fields.status')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </VeeField>
                        <ErrorMessage name="status" class="text-danger" />
                    </div>
                    <div class="mb-3">
                        <label for="inputRemarks" class="form-label">{{ t('views.user.fields.remarks') }}</label>
                        <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.user.fields.remarks')" v-model="user.profile.remarks" rows="3"></textarea>
                    </div>
                    <hr class="mb-3" />
                    <div class="mb-3">
                        <label for="inputSettings" class="form-label">{{ t('views.user.fields.settings.settings') }}</label>
                        <div class="mb-3">
                            <label for="selectTheme" class="form-label">{{ t('views.user.fields.settings.theme') }}</label>
                            <select class="form-control form-select" id="selectTheme" name="theme" v-model="user.selected_settings.theme" v-show="mode === 'create' || mode === 'edit'">
                                <option value="side-menu-light-full">Menu Light</option>
                                <option value="side-menu-light-mini">Mini Menu Light</option>
                                <option value="side-menu-dark-full">Menu Dark</option>
                                <option value="side-menu-dark-mini">Mini Menu Dark</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="selectDate">{{ t('views.user.fields.settings.dateFormat') }}</label>
                                    <select id="selectDate" class="form-control form-select" name="dateFormat" v-model="user.selected_settings.dateFormat" v-show="mode === 'create' || mode === 'edit'">
                                        <option value="yyyy_MM_dd">{{ helper.formatDate(new Date(), 'YYYY-MM-DD') }}</option>
                                        <option value="dd_MMM_yyyy">{{ helper.formatDate(new Date(), 'DD-MMM-YYYY') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="selectTime">{{ t('views.user.fields.settings.timeFormat') }}</label>
                                    <select id="selectTime" class="form-control form-select" name="timeFormat" v-model="user.selected_settings.timeFormat" v-show="mode === 'create' || mode === 'edit'">
                                        <option value="hh_mm_ss">{{ helper.formatDate(new Date(), 'HH:mm:ss') }}</option>
                                        <option value="h_m_A">{{ helper.formatDate(new Date(), 'H:m A') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="apiToken">{{ t('views.user.fields.settings.apiToken') }}</label>
                            <div class="form-check" v-show="mode === 'edit'">
                                <input id="apiToken" class="form-check-input" type="checkbox" name="apiToken">
                                <label class="form-check-label" for="apiToken">{{ t('components.buttons.revoke') }}</label>
                            </div>
                            <div v-if="mode === 'create'">*************************</div>
                        </div>
                    </div>
                    <div class="mb-3" v-if="mode === 'edit'">
                        <label for="resetPassword" class="form-label">{{ t('views.user.fields.reset_password') }}</label>
                        <div class="form-check pr-5">
                            <input id="resetPassword" name="resetPassword" class="form-check-input" type="checkbox" value="" />
                            <label class="form-check-label" for="resetPassword">{{ t('views.user.fields.reset_password') }}</label>
                        </div>
                        <div class="form-check">
                            <input id="emailResetPassword" name="emailResetPassword" class="form-check-input" type="checkbox" value="" />
                            <label class="form-check-label" for="emailResetPassword">{{ t('views.user.fields.email_reset_password') }}</label>
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
//#region Imports
import { onMounted, onUnmounted, ref, computed, watch } from "vue";
import axios from "@/axios";
import { helper } from "@/utils/helper";
import { useI18n } from "vue-i18n";
import { route } from "@/ziggy";
import dom from "@left4code/tw-starter/dist/js/dom";
import DataList from "@/global-components/data-list/Main"
import AlertPlaceholder from "@/global-components/alert-placeholder/Main"
import { getCachedDDL, setCachedDDL } from "@/mixins";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region  Data - UI
const mode = ref('list');
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref('');
const deleteModalShow = ref(false);
const expandDetail = ref(null);
//#endregion

//#region Data - Views
const user = ref({
    roles: [],
    selected_roles: [],
    profile: {
        country: '',
        status: 'ACTIVE',
        img_path: ''
    },
    selected_settings: {
        theme: 'side-menu-light-full',
        dateFormat: '',
        timeFormat: '',
    }
});
const userList = ref({ });
const rolesDDL = ref([]);
const statusDDL = ref([]);
const countriesDDL = ref([]);
//#endregion

//#region onMounted
onMounted(() => {
    getUser({ page: 1 });
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

const getUser = (args) => {
    userList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    axios.get(route('api.get.db.admin.users.read', { "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        userList.value = response.data;
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

    if (getCachedDDL('rolesDDL') == null) {
        axios.get(route('api.get.db.admin.users.roles.read')).then(response => {
            rolesDDL.value = response.data;
        });
    } else {
        rolesDDL.value = getCachedDDL('rolesDDL');
    }
}

const onSubmit = (values, actions) => {
    loading.value = true;

    var formData = new FormData(dom('#userForm')[0]);

    if (mode.value === 'create') {
        axios.post(route('api.post.db.admin.users.save'), formData, {
            headers: {
                'content-type': 'multipart/form-data'
            }
        }).then(response => {
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === 'edit') {
        axios.post(route('api.post.db.admin.users.edit', user.value.hId), formData, {
            headers: {
                'content-type': 'multipart/form-data'
            }
        }).then(response => {
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

const emptyUser = () => {
    return {
        roles: [],
        selected_roles: [],
        profile: {
            img_path: '',
            country: '',
            status: 'ACTIVE',
        },
        selected_settings: {
            theme: 'side-menu-light-full',
            dateFormat: 'yyyy_MM_dd',
            timeFormat: 'hh_mm_ss',
        }
    }
}

const resetAlertErrors = () => {
    alertErrors.value = [];
}

const createNew = () => {
    mode.value = 'create';

    if (sessionStorage.getItem('DCSLAB_LAST_ENTITY') !== null) {
        user.value = JSON.parse(sessionStorage.getItem('DCSLAB_LAST_ENTITY'));
        sessionStorage.removeItem('DCSLAB_LAST_ENTITY');
    } else {
        user.value = emptyUser();
    }
}

const onDataListChange = ({page, pageSize, search}) => {
    getUser({page, pageSize, search});
}

const editSelected = (index) => {
    mode.value = 'edit';
    user.value = userList.value.data[index];
}

const deleteSelected = (index) => {
    deleteId.value = userList.value.data[index].hId;
    deleteModalShow.value = true;
}

const confirmDelete = () => {
    deleteModalShow.value = false;
    if (deleteId.value) console.log('Data ' + deleteId.value + ' deleted.');
}

const showSelected = (index) => {
    toggleDetail(index);
}

const backToList = () => {
    resetAlertErrors();
    sessionStorage.removeItem('DCSLAB_LAST_ENTITY');

    mode.value = 'list';
    getUser({ page: userList.value.current_page, pageSize: userList.value.per_page });
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
        user.value.profile.img_path = fileReader.result
    })
    fileReader.readAsDataURL(files[0])
}
//#endregion

//#region Computed
const retrieveImage = computed(() => {
    if (user.value.profile.img_path && user.value.profile.img_path !== '') {
        if (user.value.profile.img_path.includes('data:image')) {
            return user.value.profile.img_path;
        } else {
            return '/storage/' + user.value.profile.img_path;
        }
    } else {
        return '/images/def-user.png';
    }
});
//#endregion

//#region Watcher
watch(user, (newV) => {
    if (mode.value == 'create') sessionStorage.setItem('DCSLAB_LAST_ENTITY', JSON.stringify(newV));
}, { deep: true });
//#endregion
</script>
