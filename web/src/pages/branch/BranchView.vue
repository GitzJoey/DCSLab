<script setup lang="ts">
//#region Imports
import { onMounted, ref, watch, computed } from "vue";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
import DataList from "../../base-components/DataList";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import Table from "../../base-components/Table";
import { TitleLayout, TwoColumnsLayout } from "../../base-components/Form/FormLayout";
import { FormInput, FormLabel, FormTextarea, FormSelect, FormSwitch } from "../../base-components/Form";
import { ViewMode } from "../../types/enums/ViewMode";
import DataList from "../../base-components/DataList/DataList.vue";
import Table from "../../base-components/Table";
import Lucide from "../../base-components/Lucide";
//#endregion

//#region Declarations
const { t } = useI18n();
const cacheServices = new CacheService();
const dashboardServices = new DashboardService();
const selectedUserStore = useSelectedUserLocationStore();
const userLocation = computed(() => selectedUserStore.selectedUserLocation);
const branchServices = new BranchService();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const datalistErrors = ref<LaravelError | VeeValidateError | null>(null);
const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'Branch Information', state: CardState.Expanded, },
  { title: '', state: CardState.Hidden, id: 'button' }
]);
const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
const branchForm = ref<BranchFormRequest>({
  data: {
    id: '',
    ulid: '',
    company: {
      id: '',
      ulid: '',
      code: '',
      name: '',
      address: '',
      default: false,
      status: ''
    },
    code: '',
    name: '',
    address: '',
    city: '',
    contact: '',
    is_main: false,
    remarks: '',
    status: 'ACTIVE',
  }
});

const branchLists = ref<Collection<Branch[]> | null>({
  data: [],
  meta: {
    current_page: 0,
    from: null,
    last_page: 0,
    path: '',
    per_page: 0,
    to: null,
    total: 0,
  },
  links: {
    first: '',
    last: '',
    prev: null,
    next: null,
  }
});
const statusDDL = ref<Array<DropDownOption> | null>(null);
//#endregion

//#region onMounted
onMounted(() => {

});
//#endregion

//#region Watcher
watch(
  branchForm,
  debounce((newValue): void => {
    if (mode.value != ViewMode.FORM_CREATE) return;
    cacheServices.setLastEntity('Branch', newValue)
  }, 500),
  { deep: true }
);
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <TitleLayout>
        <template #title>
          {{ titleView }}
        </template>
        <template #optional>
          <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
            <Button v-if="mode == ViewMode.LIST" as="a" href="#" variant="primary" class="shadow-md" @click="createNew">
              <Lucide icon="Plus" class="w-4 h-4" />&nbsp;{{
                t("components.buttons.create_new")
              }}
            </Button>
          </div>
        </template>
      </TitleLayout>
    </LoadingOverlay>
  </div>
</template>
