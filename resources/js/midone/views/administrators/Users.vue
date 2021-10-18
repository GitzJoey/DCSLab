<template>
    <div class="intro-y" v-if="mode === 'list'">
        <DataList title="User Lists" :data="userList" v-on:createNew="createNew" v-on:refreshData="refreshData">
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

    <div class="intro-y box mt-5" v-if="mode === 'create'">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.users.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.users.actions.edit') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'show'">{{ t('views.users.actions.show') }}</h2>
        </div>
        <vee-form id="userForm" class="p-5" @submit="onSubmit" :validation-schema="schema" v-slot="{ handleReset, errors }">
            <div class="form-inline">
                <label for="inputName" class="form-label w-1/3">{{ t('views.users.fields.name') }}</label>
                <vee-field id="inputName" name="name" as="input" :class="{'form-control w-2/3':true, 'border-theme-21': errors['name']}" :placeholder="t('views.users.fields.name')" :label="t('views.users.fields.name')" v-model="user.name" v-show="mode === 'create' || mode === 'edit'"/>
                <ErrorMessage name="name" class="text-theme-21 mt-2 ml-1" />
                <div class="form-label" v-if="mode === 'show'">{{ user.name }}</div>
            </div>
            <div class="sm:ml-20 sm:pl-5 mt-5">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </vee-form>
    </div>
</template>

<script>
import { defineComponent, inject, onMounted, ref } from 'vue'
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
            name: ''
        });
        const userList = ref({ });

        onMounted(() => {
            const setDashboardLayout = inject('setDashboardLayout')
            setDashboardLayout(false);

            getUser();
        });

        function getUser() {
            axios.get('/api/get/dashboard/admin/users/read?page=1').then(response => {
                userList.value = response.data;
                mode.value = 'list';
            });
        }

        function onSubmit(values) {
            console.log(values);
        }

        function createNew() {
            mode.value = 'create';
        }

        function refreshData() {
            console.log('refreshData');
        }

        function editSelected(index) {
            console.log('editSelected');
        }

        function viewSelected(index) {

        }

        function backToList() {
            this.mode = 'list';
        }

        return {
            t,
            schema,
            onSubmit,
            mode,
            user,
            userList,
            createNew,
            refreshData,
            editSelected,
            viewSelected,
            backToList,
        }
    }
})
</script>
