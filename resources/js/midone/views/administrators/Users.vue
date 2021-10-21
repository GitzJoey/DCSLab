<template>
    <div class="intro-y" v-if="mode === 'list'">
        <DataList title="User Lists" :data="userList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange">
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
                        <tr class="intro-x" v-if="tableProps.dataList !== undefined" v-for="(u, uIdx) in tableProps.dataList.data">
                            <td>{{ u.name }}</td>
                            <td>{{ u.email }}</td>
                            <td>
                                <span v-for="(r, rIdx) in u.roles">{{ r.display_name }}</span>
                            </td>
                            <td>
                                <CheckCircleIcon v-if="u.profile.status === 1" />
                                <XIcon v-if="u.profile.status === 0" />
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <a class="flex items-center mr-3" href="" @click.prevent="viewSelected(rIdx)">
                                        <InfoIcon class="w-4 h-4 mr-1" />
                                        {{ t('components.data-list.view') }}
                                    </a>
                                    <a class="flex items-center mr-3" href="" @click.prevent="editSelected(rIdx)">
                                        <CheckSquareIcon class="w-4 h-4 mr-1" />
                                        {{ t('components.data-list.edit') }}
                                    </a>
                                    <a class="flex items-center text-theme-21" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal">
                                        <Trash2Icon class="w-4 h-4 mr-1" /> {{ t('components.data-list.delete') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </template>
        </DataList>
    </div>

    <div class="intro-y box" v-if="mode === 'create'">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.users.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.users.actions.edit') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'show'">{{ t('views.users.actions.show') }}</h2>
        </div>
        <vee-form id="userForm" class="p-5" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
            <div class="flex items-start p-3">
                <label for="inputName" class="w-40 px-3 py-2">{{ t('views.users.fields.name') }}</label>
                <div class="flex-1">
                    <vee-field id="inputName" name="name" as="input" :class="{'form-control':true, 'border-theme-21': errors['name']}" :placeholder="t('views.users.fields.name')" :label="t('views.users.fields.name')" v-model="user.name" v-show="mode === 'create' || mode === 'edit'"/>
                    <ErrorMessage name="name" class="text-theme-21 px-1" />
                </div>
            </div>
            <div class="flex items-start p-3">
                <label for="inputEmail" class="w-40 px-3 py-2">{{ t('views.users.fields.email') }}</label>
                <div class="flex-1">
                    <vee-field id="inputEmail" name="email" as="input" :class="{'form-control':true, 'border-theme-21': errors['email']}" :placeholder="t('views.users.fields.email')" :label="t('views.users.fields.email')" v-model="user.email" v-show="mode === 'create' || mode === 'edit'" :readonly="mode === 'edit'"/>
                    <ErrorMessage name="email" class="text-theme-21 px-1" />
                </div>
            </div>
            <div class="flex items-start p-3">
                <label for="inputImg" class="w-40 px-3 py-2"></label>
                <div class="flex-1">
                    <div class="my-1">
                        <img alt="" class="" :src="retrieveImage">
                    </div>
                    <div class="">
                        <input type="file" class="h-full w-full" v-on:change="handleUpload"/>
                    </div>
                </div>
            </div>
            <div class="flex items-start p-3">
                <label for="inputFirstName" class="w-40 px-3 py-2">{{ t('views.users.fields.first_name') }}</label>
                <div class="flex-1">
                    <input id="inputFirstName" name="first_name" type="text" class="form-control" :placeholder="t('views.users.fields.first_name')" v-model="user.profile.first_name" v-show="mode === 'create' || mode === 'edit'"/>
                    <div class="form-control-plaintext" v-if="mode === 'show'">{{ user.profile.first_name }}</div>
                </div>
            </div>
            <div class="flex items-start p-3">
                <label for="inputLastName" class="w-40 px-3 py-2">{{ t('views.users.fields.last_name') }}</label>
                <div class="flex-1">
                    <input id="inputLastName" name="last_name" type="text" class="form-control" :placeholder="t('views.users.fields.last_name')" v-model="user.profile.last_name" v-show="mode === 'create' || mode === 'edit'"/>
                    <div class="form-control-plaintext" v-if="mode === 'show'">{{ user.profile.last_name }}</div>
                </div>
            </div>
            <div class="flex items-start p-3">
                <label for="inputAddress" class="w-40 px-3 py-2">{{ t('views.users.fields.address') }}</label>
                <div class="flex-1">
                    <input id="inputAddress" name="address" type="text" class="form-control" :placeholder="t('views.users.fields.address')" v-model="user.profile.address" v-show="mode === 'create' || mode === 'edit'"/>
                    <div class="form-control-plaintext" v-if="mode === 'show'">{{ user.profile.address }}</div>
                </div>
            </div>
            <div class="flex items-start p-3">
                <label for="inputCity" class="w-40 px-3 py-2">{{ t('views.users.fields.city') }}</label>
                <div class="flex-1">
                    <input id="inputCity" name="city" type="text" class="form-control" :placeholder="t('views.users.fields.city')" v-model="user.profile.city" v-show="mode === 'create' || mode === 'edit'"/>
                    <div class="form-control-plaintext" v-if="mode === 'show'">{{ user.profile.city }}</div>
                </div>
            </div>
            <div class="flex items-start p-3">
                <label for="inputPostalCode" class="w-40 px-3 py-2">{{ t('views.users.fields.postal_code') }}</label>
                <div class="flex-1">
                    <input id="inputPostalCode" name="postal_code" type="text" class="form-control" :placeholder="t('views.users.fields.postal_code')" v-model="user.profile.postal_code" v-show="mode === 'create' || mode === 'edit'"/>
                    <div class="form-control-plaintext" v-if="mode === 'show'">{{ user.profile.postal_code }}</div>
                </div>
            </div>
            <div class="flex items-start p-3">
                <label for="inputCountry" class="w-40 px-3 py-2">{{ t('views.users.fields.country') }}</label>
                <div class="flex-1">
                    <select id="inputCountry" name="country" class="form-select" v-model="user.profile.country" :placeholder="t('views.users.fields.country')" v-show="mode === 'create' || mode === 'edit'">
                        <option value="">{{ t('components.dropdown.placeholder') }}</option>
                        <option v-for="c in countriesDDL" :key="c.name">{{ c.name }}</option>
                    </select>
                    <div class="form-control-plaintext" v-if="mode === 'show'">{{ user.profile.country }}</div>
                </div>
            </div>
            <div class="flex items-start p-3">
                <label for="inputTaxId" class="w-40 px-3 py-2">{{ t('views.users.fields.tax_id') }}</label>
                <div class="flex-1">
                    <vee-field id="inputTaxId" name="tax_id" as="input" :class="{'form-control':true, 'border-theme-21': errors['tax_id']}" :placeholder="t('views.users.fields.tax_id')" :label="t('views.users.fields.tax_id')" v-model="user.profile.tax_id" v-show="mode === 'create' || mode === 'edit'"/>
                    <ErrorMessage name="tax_id" class="text-theme-21 px-1" />
                    <div class="form-control-plaintext" v-if="mode === 'show'">{{ user.profile.tax_id }}</div>
                </div>
            </div>
            <div class="flex items-start p-3">
                <label for="inputICNum" class="w-40 px-3 py-2">{{ t('views.users.fields.ic_num') }}</label>
                <div class="flex-1">
                    <vee-field id="inputICNum" name="ic_num" as="input" :class="{'form-control':true, 'border-theme-21': errors['ic_num']}" :placeholder="t('views.users.fields.ic_num')" :label="t('views.users.fields.ic_num')" v-model="user.profile.ic_num" v-show="mode === 'create' || mode === 'edit'"/>
                    <ErrorMessage name="ic_num" class="text-theme-21 px-1" />
                    <div class="form-control-plaintext" v-if="mode === 'show'">{{ user.profile.ic_num }}</div>
                </div>
            </div>
            <hr/>
            <div class="flex items-start p-3">
                <label for="inputRoles" class="w-40 px-3 py-2">{{ t('views.users.fields.roles') }}</label>
                <div class="flex-1">
                    <select multiple :class="{'form-control':true, 'border-theme-21':errors['roles']}" id="inputRoles" name="roles[]" size="6" v-model="user.selectedRoles" v-show="mode === 'create' || mode === 'edit'">
                        <option v-for="(r, rIdx) in rolesDDL" :value="r.hId">{{ r.display_name }}</option>
                    </select>
                    <ErrorMessage name="roles" class="text-theme-21 px-1" />
                    <div class="form-control-plaintext" v-if="mode === 'show'">
                        <span v-for="r in user.roles">{{ r.display_name }}&nbsp;</span>
                    </div>
                </div>
            </div>
            <div class="flex items-start p-3">
                <label for="inputStatus" class="w-40 px-3 py-2">{{ t('views.users.fields.status') }}</label>
                <div class="flex-1 d-flex align-items-center">
                    <div>
                        <select class="form-select" id="inputStatus" name="status" v-model="user.profile.status" v-show="mode === 'create' || mode === 'edit'">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code">{{ c.name }}</option>
                        </select>
                        <div class="form-control-plaintext" v-if="mode === 'show'">
                            <span v-if="user.profile.status === 1">{{ t('statusDDL.active') }}</span>
                            <span v-if="user.profile.status === 0">{{ t('statusDDL.inactive') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-start p-3">
                <label for="inputRemarks" class="w-40 px-3 py-2">{{ t('views.users.fields.remarks') }}</label>
                <div class="flex-1">
                    <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.users.fields.remarks')" v-model="user.profile.remarks" v-show="mode === 'create' || mode === 'edit'" rows="3"></textarea>
                    <ErrorMessage name="remarks" class="invalid-feedback" />
                    <div class="form-control-plaintext" v-if="mode === 'show'">{{ user.profile.remarks }}</div>
                </div>
            </div>
            <div class="flex items-start p-3">
                <div class="w-40 px-3 py-2"></div>
                <div class="flex-1">
                    <button type="submit" class="btn btn-primary mt-5 mr-3">{{ t('components.buttons.save') }}</button>
                    <button type="button" class="btn btn-secondary" @click="handleReset">{{ t('components.buttons.reset') }}</button>
                </div>
            </div>
        </vee-form>
        <hr/>
        <div>
            <button type="button" class="btn btn-secondary w-15 m-3" @click="backToList">{{ t('components.buttons.back') }}</button>
        </div>
    </div>
</template>

<script>
import { defineComponent, inject, onMounted, ref, computed } from 'vue'
import mainMixins from '../../mixins';

import DataList from '../../global-components/data-list/Main'

export default defineComponent({
    name: "Users",
    components: {
        DataList
    },
    setup() {
        const { t } = mainMixins();

        const schema = {
            name: 'required',
            email: 'required|email',
            tax_id: 'required',
            ic_num: 'required',
        };

        let mode = ref('list');
        let user = ref({
            name: '',
            selectedRoles: '',
            roles: [],
            profile: {
                status: '',
                country: '',
                img_path: ''
            }
        });
        const userList = ref({ });
        let rolesDDL = ref([]);
        let statusDDL = ref([]);
        let countriesDDL = ref([]);

        onMounted(() => {
            const setDashboardLayout = inject('setDashboardLayout');
            setDashboardLayout(false);

            getUser();
            getDDL();
        });

        function getUser() {
            axios.get('/api/get/dashboard/admin/users/read?page=1').then(response => {
                userList.value = response.data;
                mode.value = 'list';
            });
        }

        function getDDL() {
            axios.get('/api/get/dashboard/common/ddl/list/countries').then(response => {
                countriesDDL.value = response.data;
            });

            axios.get('/api/get/dashboard/common/ddl/list/statuses').then(response => {
                statusDDL.value = response.data;
            });

            axios.get('/api/get/dashboard/admin/users/roles/read').then(response => {
                rolesDDL.value = response.data;
            });
        }

        function onSubmit(values) {
            console.log(values);
        }

        function createNew() {
            mode.value = 'create';
        }

        function onDataListChange({page, pageSize}) {
            console.log(page);
            console.log(pageSize);
        }

        function editSelected(index) {
            console.log('editSelected');
        }

        function viewSelected(index) {

        }

        function backToList() {
            mode.value = 'list';
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

        return {
            t,
            schema,
            onSubmit,
            mode,
            user,
            userList,
            createNew,
            onDataListChange,
            editSelected,
            viewSelected,
            backToList,
            retrieveImage,
            handleUpload,
            countriesDDL,
            statusDDL,
            rolesDDL
        }
    }
})
</script>
