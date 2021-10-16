<template>
    <div class="intro-y">
        <DataList title="User Lists" :data="userList">
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
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <a class="flex items-center mr-3" href="javascript:;">
                                        <CheckSquareIcon class="w-4 h-4 mr-1" />
                                        Edit
                                    </a>
                                    <a class="flex items-center text-theme-21" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal">
                                        <Trash2Icon class="w-4 h-4 mr-1" /> Delete
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

            axios.get('/api/get/dashboard/admin/users/read?page=1').then(response => {
                userList.value = response.data;
            });
        });

        return {
            t,
            userList
        }
    }
})
</script>
