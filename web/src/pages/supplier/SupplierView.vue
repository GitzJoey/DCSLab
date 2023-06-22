<script setup lang="ts">
//#region Import
import { ref } from "vue";
import { onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { TitleLayout } from "../../base-components/Form/FormLayout";
import { ViewMode } from "../../types/enums/ViewMode";
import DataList from "../../base-components/DataList/DataList.vue";
import Table from "../../base-components/Table";
import Lucide from "../../base-components/Lucide";
import {
  FormInput,
  FormLabel,
  FormTextarea,
  FormSelect,
} from "../../base-components/Form";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
//#endregion

//#region onMounted
onMounted(() => {});
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <div v-if="mode == ViewMode.LIST"></div>
      <TitleLayout>
        <template #title>{{ t("views.supplier.page_title") }}</template>
      </TitleLayout>

      <div class="grid grid-cols-12">
        <select
          class="form-select col-span-11 py-3 px-4 w-full mt-3 lg:mt-0 ml-auto border-none focus:border-none rounded "
        >
          <option>Supplier 1</option>
          <option>Supplier 2</option>
          <option>Supplier 3</option>
        </select>

        <div
          class="box flex col-span-1 ml-5 items-center justify-center bg-[#164e63] text-white cursor-pointer"
        >
          <Lucide icon="Plus" />
        </div>
      </div>

      <div class="box mt-3">
        <div class="p-4">
          <FormLabel html-for="name"> Code </FormLabel>
          <FormInput id="name" name="name" type="text" class="w-full" />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> Name </FormLabel>
          <FormInput id="name" name="name" type="text" class="w-full" />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> Address </FormLabel>
          <FormInput id="name" name="name" type="text" class="w-full" />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> City </FormLabel>
          <FormInput id="name" name="name" type="text" class="w-full" />
        </div>

        <div class="p-4">
          <FormLabel html-for="name"> Taxable Enterprise </FormLabel>
          <FormInput id="name" name="name" type="text" class="w-full" />
        </div>

        <div class="p-4">
          <button class="w-full bg-[#164e63] h-[50px] rounded text-white" >Submit</button>
        </div>
      </div>
    </LoadingOverlay>
  </div>
</template>
