<script setup lang="ts">
//#region Imports
import { onMounted, onUnmounted, ref, computed, watch } from "vue";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
import DataList from "../../base-components/DataList";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import Table from "../../base-components/Table";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - Pinia
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

});

onUnmounted(() => {

});
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>

<template>
    <div class="mt-8">
        <LoadingOverlay :visible="loading">
            <div v-if="mode == 'list'">
                <div class="flex flex-col items-center intro-y sm:flex-row">
                    <h2 class="mr-auto text-lg font-medium">{{ t('views.user.page_title') }}</h2>
                    <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
                        <Button as="a" href="#" variant="primary" class="shadow-md">
                            <Lucide icon="Plus" class="w-4 h-4"/>&nbsp;{{ t('components.buttons.create_new') }}
                        </Button>
                    </div>
                </div>

                <AlertPlaceholder />
                <DataList>
                    <template v-slot:table="tableProps">
                        <Table class="mt-5" :hover="true">
                            <Table.Thead variant="light">
                                <Table.Tr>
                                    <Table.Th class="whitespace-nowrap">{{ t('views.user.table.cols.name') }}</Table.Th>
                                    <Table.Th class="whitespace-nowrap">{{ t('views.user.table.cols.email') }}</Table.Th>
                                    <Table.Th class="whitespace-nowrap">{{ t('views.user.table.cols.roles') }}</Table.Th>
                                    <Table.Th class="whitespace-nowrap">{{ t('views.user.table.cols.status') }}</Table.Th>
                                    <Table.Th class="whitespace-nowrap"></Table.Th>                                    
                                </Table.Tr>
                            </Table.Thead>
                            <Table.Tbody>
                                <Table.Tr>
                                <Table.Td>1</Table.Td>
                                <Table.Td>Angelina</Table.Td>
                                <Table.Td>Jolie</Table.Td>
                                <Table.Td>@angelinajolie</Table.Td>
                                </Table.Tr>
                                <Table.Tr>
                                <Table.Td>2</Table.Td>
                                <Table.Td>Brad</Table.Td>
                                <Table.Td>Pitt</Table.Td>
                                <Table.Td>@bradpitt</Table.Td>
                                </Table.Tr>
                                <Table.Tr>
                                <Table.Td>3</Table.Td>
                                <Table.Td>Charlie</Table.Td>
                                <Table.Td>Hunnam</Table.Td>
                                <Table.Td>@charliehunnam</Table.Td>
                                </Table.Tr>
                            </Table.Tbody>
                        </Table>
                    </template>
                </DataList>
            </div>
            <div v-else>

            </div>

        </LoadingOverlay>
    </div>

</template>