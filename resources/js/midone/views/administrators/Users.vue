<template>
    <div class="intro-y">
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
</template>

<script>
import { defineComponent, inject, onMounted, ref } from 'vue'
import mainMixins from '../../mixins/index'

import DataList from '../../global-components/data-list/Main'

export default defineComponent({
    name: "Users",
    components: {
        DataList
    },
    setup() {
        const { t } = mainMixins();

        const userList = ref({ });

        onMounted(() => {
            const setDashboardLayout = inject('setDashboardLayout')
            setDashboardLayout(false);

            getUser();
        });

        function getUser() {
            axios.get('/api/get/dashboard/admin/users/read?page=1').then(response => {
                userList.value = response.data;
            });
        }

        function createNew() {
            console.log('createNew');
        }

        function refreshData() {
            console.log('refreshData');
        }

        function editSelected(index) {
            console.log('editSelected');
        }

        function viewSelected(index) {

        }

        return {
            t,
            userList,
            createNew,
            refreshData,
            editSelected,
            viewSelected,
        }
    }
})
</script>
