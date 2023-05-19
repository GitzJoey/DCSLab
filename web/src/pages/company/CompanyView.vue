<script setup lang="ts">
//#region Import
import { ref } from "vue";
import { onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { ViewTitleLayout } from "../../base-components/FormLayout";
import { ViewMode } from "../../types/enums/ViewMode";
import DataList from "../../base-components/DataList/DataList.vue";
import Table from "../../base-components/Table";
import { CheckCircleIcon } from "lucide-vue-next";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
//#endregion

//#region onMounted
onMounted(() => {
  // console.log("a");
});
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <div v-if="mode == ViewMode.LIST"></div>
      <ViewTitleLayout>
        <template #title>
          <div>
              <h1>Company List</h1>
          </div>
        </template>
      </ViewTitleLayout>
      <DataList>
        <template #table="tableProps" >
          <Table class="mt-5" :hover="true" >
              <Table.Thead variant="light" >
                <Table.Tr>
                  <Table.Th class="whitespace-nowrap" >
                    Name
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap" >
                    Email
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap" >
                    Roles
                  </Table.Th>
                  <Table.Th class="whitespace-nowrap" >
                    Status
                  </Table.Th>
                </Table.Tr>
              </Table.Thead>

              <Table.Tbody>
                <Table.Tr>
                  <Table.Td>
                    1234
                  </Table.Td>
                  <Table.Td>
                    <a class="hover:animate-pulse" >
                      test@mail.com
                    </a>
                  </Table.Td>
                  <Table.Td>
                    <CheckCircleIcon/>
                  </Table.Td>
                  <Table.Td>
                    ACTIVE
                  </Table.Td>
                </Table.Tr>
              </Table.Tbody>
          </Table>

        </template>
      </DataList>
    </LoadingOverlay>
    
  </div>
</template>
