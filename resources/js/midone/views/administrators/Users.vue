<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.users.table.title')" :data="userList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
            <template v-slot:table="tableProps">
                <table class="table table-report -mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.users.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.users.table.cols.email') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.users.table.cols.roles') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.users.table.cols.status') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data">
                            <tr class="intro-x">
                                <td><a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse">{{ item.name }}</a></td>
                                <td>{{ item.email }}</td>
                                <td>
                                    <span v-for="(r, rIdx) in item.roles">{{ r.display_name }}</span>
                                </td>
                                <td>
                                    <CheckCircleIcon v-if="item.profile.status === 1" />
                                    <XIcon v-if="item.profile.status === 0" />
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
                                    <br />
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
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.users.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.users.actions.edit') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'show'">{{ t('views.users.actions.show') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="userForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
                <div class="mb-3">
                    <label for="inputName" class="form-label">{{ t('views.users.fields.name') }}</label>
                    <VeeField id="inputName" name="name" as="input" :class="{'form-control':true, 'border-theme-21': errors['name']}" :placeholder="t('views.users.fields.name')" :label="t('views.users.fields.name')" v-model="user.name" v-show="mode === 'create' || mode === 'edit'" />
                    <ErrorMessage name="name" class="text-theme-21" />
                </div>
                <div class="mb-3">
                    <label for="inputEmail" class="form-label">{{ t('views.users.fields.email') }}</label>
                    <VeeField id="inputEmail" name="email" as="input" :class="{'form-control':true, 'border-theme-21': errors['email']}" :placeholder="t('views.users.fields.email')" :label="t('views.users.fields.email')" v-model="user.email" v-show="mode === 'create' || mode === 'edit'" :readonly="mode === 'edit'"/>
                    <ErrorMessage name="email" class="text-theme-21" />
                </div>
                <div class="mb-3">
                    <label for="inputImg" class="form-label">{{ t('views.users.fields.picture') }}</label>
                    <div class="">
                        <div class="my-1">
                            <img id="inputImg" alt="" class="" :src="retrieveImage">
                        </div>
                        <div class="">
                            <input type="file" class="h-full w-full" name="img_path" v-if="mode === 'create' || mode === 'edit'" v-on:change="handleUpload"/>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="inputFirstName" class="form-label">{{ t('views.users.fields.first_name') }}</label>
                    <input id="inputFirstName" name="first_name" type="text" class="form-control" :placeholder="t('views.users.fields.first_name')" v-model="user.profile.first_name" v-show="mode === 'create' || mode === 'edit'"/>
                </div>
                <div class="mb-3">
                    <label for="inputLastName" class="form-label">{{ t('views.users.fields.last_name') }}</label>
                    <input id="inputLastName" name="last_name" type="text" class="form-control" :placeholder="t('views.users.fields.last_name')" v-model="user.profile.last_name" v-show="mode === 'create' || mode === 'edit'"/>
                </div>
                <div class="mb-3">
                    <label for="inputAddress" class="form-label">{{ t('views.users.fields.address') }}</label>
                    <input id="inputAddress" name="address" type="text" class="form-control" :placeholder="t('views.users.fields.address')" v-model="user.profile.address" v-show="mode === 'create' || mode === 'edit'"/>
                </div>
                <div class="mb-3">
                    <label for="inputCity" class="form-label">{{ t('views.users.fields.city') }}</label>
                    <input id="inputCity" name="city" type="text" class="form-control" :placeholder="t('views.users.fields.city')" v-model="user.profile.city" v-show="mode === 'create' || mode === 'edit'"/>
                </div>
                <div class="mb-3">
                    <label for="inputPostalCode" class="form-label">{{ t('views.users.fields.postal_code') }}</label>
                    <input id="inputPostalCode" name="postal_code" type="text" class="form-control" :placeholder="t('views.users.fields.postal_code')" v-model="user.profile.postal_code" v-show="mode === 'create' || mode === 'edit'"/>
                </div>
                <div class="mb-3">
                    <label for="inputCountry" class="form-label">{{ t('views.users.fields.country') }}</label>
                    <select id="inputCountry" name="country" class="form-control form-select" v-model="user.profile.country" :placeholder="t('views.users.fields.country')" v-show="mode === 'create' || mode === 'edit'">
                        <option value="">{{ t('components.dropdown.placeholder') }}</option>
                        <option v-for="c in countriesDDL" :key="c.name" :value="c.name">{{ c.name }}</option>
                    </select>
                    <ErrorMessage name="country" class="text-theme-21" />
                </div>
                <div class="mb-3">
                    <label for="inputTaxId" class="form-label">{{ t('views.users.fields.tax_id') }}</label>
                    <VeeField id="inputTaxId" name="tax_id" type="text" :class="{'form-control':true, 'border-theme-21': errors['tax_id']}" :placeholder="t('views.users.fields.tax_id')" :label="t('views.users.fields.tax_id')" v-model="user.profile.tax_id" v-show="mode === 'create' || mode === 'edit'"/>
                    <ErrorMessage name="tax_id" class="text-theme-21" />
                </div>
                <div class="mb-3">
                    <label for="inputICNum" class="form-label">{{ t('views.users.fields.ic_num') }}</label>
                    <VeeField id="inputICNum" name="ic_num" type="text" :class="{'form-control':true, 'border-theme-21': errors['ic_num']}" :placeholder="t('views.users.fields.ic_num')" :label="t('views.users.fields.ic_num')" v-model="user.profile.ic_num" v-show="mode === 'create' || mode === 'edit'"/>
                    <ErrorMessage name="ic_num" class="text-theme-21" />
                </div>
                <div class="mb-3">
                    <label for="inputRoles" class="form-label">{{ t('views.users.fields.roles') }}</label>
                    <select multiple :class="{'form-control':true, 'border-theme-21':errors['roles']}" id="inputRoles" name="roles[]" size="6" v-model="user.selectedRoles" v-show="mode === 'create' || mode === 'edit'">
                        <option v-for="(value, name) in rolesDDL" :value="name">{{ value }}</option>
                    </select>
                    <div class="" v-if="mode === 'show'">
                        <span v-for="r in user.roles">{{ r.display_name }}&nbsp;</span>
                    </div>
                    <ErrorMessage name="roles" class="text-theme-21" />
                </div>
                <div class="mb-3">
                    <label for="inputStatus" class="form-label">{{ t('views.users.fields.status') }}</label>
                    <select class="form-control form-select" id="inputStatus" name="status" v-model="user.profile.status" v-show="mode === 'create' || mode === 'edit'">
                        <option value="">{{ t('components.dropdown.placeholder') }}</option>
                        <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                    </select>
                    <ErrorMessage name="status" class="text-theme-21" />
                </div>
                <div class="mb-3">
                    <label for="inputRemarks" class="form-label">{{ t('views.users.fields.remarks') }}</label>
                    <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.users.fields.remarks')" v-model="user.profile.remarks" v-show="mode === 'create' || mode === 'edit'" rows="3"></textarea>
                </div>
                <hr class="mb-3"/>
                <div class="mb-3">
                    <label for="inputSettings" class="form-label">{{ t('views.users.fields.settings.settings') }}</label>
                    <div class="mb-3">
                        <label for="selectTheme" class="form-label">{{ t('views.users.fields.settings.theme') }}</label>
                        <select class="form-control form-select" id="selectTheme" name="theme" v-model="user.selectedSettings.theme" v-show="this.mode === 'create' || this.mode === 'edit'">
                            <option value="side-menu-light-full">Menu Light</option>
                            <option value="side-menu-light-mini">Mini Menu Light</option>
                            <option value="side-menu-dark-full">Menu Dark</option>
                            <option value="side-menu-dark-mini">Mini Menu Dark</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="selectDate">{{ t('views.users.fields.settings.dateFormat') }}</label>
                                <select id="selectDate" class="form-control form-select" name="dateFormat" v-model="user.selectedSettings.dateFormat" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="yyyy_MM_dd">{{ helper.formatDate(new Date(), 'YYYY-MM-DD') }}</option>
                                    <option value="dd_MMM_yyyy">{{ helper.formatDate(new Date(), 'DD-MMM-YYYY') }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="selectTime">{{ t('views.users.fields.settings.timeFormat') }}</label>
                                <select id="selectTime" class="form-control form-select" name="timeFormat" v-model="user.selectedSettings.timeFormat" v-show="this.mode === 'create' || this.mode === 'edit'">
                                    <option value="hh_mm_ss">{{ helper.formatDate(new Date(), 'HH:mm:ss') }}</option>
                                    <option value="h_m_A">{{ helper.formatDate(new Date(), 'H:m A') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="apiToken">{{ t('views.users.fields.settings.apiToken') }}</label>
                        <div class="form-check" v-show="this.mode === 'edit'">
                            <input id="apiToken" class="form-check-input" type="checkbox" name="apiToken">
                            <label class="form-check-label" for="apiToken">{{ t('components.buttons.revoke') }}</label>
                        </div>
                        <div v-if="this.mode === 'create'">*************************</div>
                    </div>
                </div>
                <div class="mb-3" v-if="this.mode === 'edit'">
                    <label for="resetPassword" class="form-label">{{ t('views.users.fields.reset_password') }}</label>
                    <div class="form-check pr-5">
                        <input id="resetPassword" name="resetPassword" class="form-check-input" type="checkbox" value="" />
                        <label class="form-check-label" for="resetPassword">{{ t('views.users.fields.reset_password') }}</label>
                    </div>
                    <div class="form-check">
                        <input id="emailResetPassword" name="emailResetPassword" class="form-check-input" type="checkbox" value="" />
                        <label class="form-check-label" for="emailResetPassword">{{ t('views.users.fields.email_reset_password') }}</label>
                    </div>
                </div>
                <div class="mt-5" v-if="this.mode === 'create' || this.mode === 'edit'">
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
/* Stuctures
// Vue Import
// Helper Import
// Core Components Import
// Components Import

// Vee-Validate Schema
// Declarations
// Mixins
// Data - VueX
// Data - UI
// Data - Views

// onMounted
// Methods
// Computed
// Watcher
*/

// Vue Import
import { inject, onMounted, ref, computed } from 'vue'
// Helper Import
import { getLang } from '../../lang';
import mainMixins from '../../mixins';
import { helper } from '../../utils/helper';
// Components Import
import DataList from '../../global-components/data-list/Main'
import AlertPlaceholder from '../../global-components/alert-placeholder/Main'

// Vee-Validate Schema
const schema = {
    name: 'required',
    email: 'required|email',
    tax_id: 'required',
    ic_num: 'required',
};

// Mixins
const { t, route } = mainMixins();

// Data - UI
const mode = ref('list');
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref('');
const expandDetail = ref(null);

// Data - Views
const user = ref({
    roles: [],
    selectedRoles: [],
    profile: {
        country: '',
        status: 1,
        img_path: ''
    },
    selectedSettings: {
        theme: 'side-menu-light-full',
        dateFormat: '',
        timeFormat: '',
    }
});
const userList = ref({ });
const rolesDDL = ref([]);
const statusDDL = ref([]);
const countriesDDL = ref([]);

// onMounted
onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);

    getUser({ page: 1 });
    getDDL();

    loading.value = false;
});

// Methods
function getUser(args) {
    userList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    axios.get(route('api.get.db.admin.users.read', { "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        userList.value = response.data;
    });
}

function getDDL() {
    axios.get(route('api.get.db.common.ddl.list.countries')).then(response => {
        countriesDDL.value = response.data;
    });

    axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
        statusDDL.value = response.data;
    });

    axios.get(route('api.get.db.admin.users.roles.read')).then(response => {
        rolesDDL.value = response.data;
    });
}

function onSubmit(values, actions) {
    loading.value = true;
    if (mode.value === 'create') {
        axios.post(route('api.post.db.admin.users.save'), new FormData(cash('#userForm')[0]), {
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
        axios.post(route('api.post.db.admin.users.edit', user.value.hId), new FormData(cash('#userForm')[0]), {
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

function emptyUser() {
    return {
        roles: [],
        selectedRoles: [],
        profile: {
            img_path: '',
            country: '',
            status: 1,
        },
        selectedSettings: {
            theme: 'side-menu-light-full',
            dateFormat: 'yyyy_MM_dd',
            timeFormat: 'hh_mm_ss',
        }
    }
}

function resetAlertErrors() {
    alertErrors.value = [];
}

function createNew() {
    mode.value = 'create';
    user.value = emptyUser();
}

function onDataListChange({page, pageSize, search}) {
    getUser({page, pageSize, search});
}

function editSelected(index) {
    mode.value = 'edit';
    user.value = userList.value.data[index];
}

function deleteSelected(index) {
    deleteId.value = userList.value.data[index].hId;
}

function confirmDelete() {
    if (deleteId.value) console.log('Data ' + deleteId.value + ' deleted.');
}

function showSelected(index) {
    toggleDetail(index);
}

function backToList() {
    resetAlertErrors();
    mode.value = 'list';
    getUser({ page: userList.value.current_page, pageSize: userList.value.per_page });
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
        user.value.profile.img_path = fileReader.result
    })
    fileReader.readAsDataURL(files[0])
}

// Computed
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

// Watcher
</script>
