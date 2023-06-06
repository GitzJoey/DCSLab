<script setup lang="ts">
import { PropType, computed, ref, toRef, watch } from "vue";
import { FormInput, FormSelect } from "../../base-components/Form";
import Button from "../../base-components/Button";
import { Menu } from "../../base-components/Headless";
import Lucide from "../../base-components/Lucide";
import Pagination from "../../base-components/Pagination";
import { useI18n } from "vue-i18n";
import Table from '../../base-components/Table'
const { t } = useI18n();

const props = defineProps({
  visible: { type: Boolean, default: true },
  canPrint: { type: Boolean, default: false },
  canExport: { type: Boolean, default: false },
  enableSearch: { type: Boolean, default: false },
  data: { type: Object as PropType<any>, default: null },
});

const emit = defineEmits(["data-list-changed", "print", "export"]);

const visible = toRef(props, "visible");
const canPrint = toRef(props, "canPrint");
const canExport = toRef(props, "canExport");
const enableSearch = toRef(props, "enableSearch");
const data = toRef<any, any>(props, "data");

const search = ref("");
const pageSize = ref(10);

const dataNotFound = computed(() => {
  if (data.value == null) return true;

  return false;
});

const pages = computed(() => {
  if (
    data.value?.meta?.current_page !== undefined &&
    data.value?.meta?.last_page !== undefined
  ) {
    return generatePaginationArray(
      data.value?.meta?.current_page,
      data.value?.meta?.total,
    )
  } else {
    return [];
  }
});

const first = computed(() => {
  return 1;
});

let previous = computed(() => {
  if (data.value?.meta.current_page === undefined) return 1;
  if (data.value?.meta.current_page === 1) return 1;
  return data.value?.meta.current_page - 1;
});

const next = computed(() => {
  if (data.value?.meta.current_page === undefined) return 1;
  if (data.value?.meta.current_page === data.value?.meta.last_page)
    return data.value?.meta.last_page;
  return data.value?.meta.current_page + 1;
});

const last = computed(() => {
  return data.value?.meta.last_page;
});

const generatePaginationArray = (currentPage: number, totalPages: number): number[] => {
  const paginationArray: number[] = [];

  if (totalPages <= 10) {
    for (let i = 1; i <= totalPages; i++) {
      paginationArray.push(i);
    }
  } else {
    let startPage = currentPage - 1;
    let endPage = currentPage + 1;

    if (startPage < 1) {
      endPage += Math.abs(startPage) + 1;
      startPage = 1;
    }

    if (endPage > totalPages) {
      startPage -= endPage - totalPages;
      endPage = totalPages;
    }

    if (startPage > 1) {
      paginationArray.push(1);
      if (startPage > 2) {
        paginationArray.push(-1);
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      paginationArray.push(i);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        paginationArray.push(-1);
      }
      paginationArray.push(totalPages);
    }
  }

  return paginationArray;
};


// Watch region
watch(search, (newSearch: string) => {
  if (newSearch.length > 3 || newSearch === '') {
    emit('data-list-changed', { page: 1, per_page: pageSize, search: newSearch })
  }
})
// End Watch Region


</script>

<template>
  <div v-if="visible" class="intro-y box p-5 mt-5">
    <div class="grid justify-items-end">
      <div class="flex flex-row gap-2">
        <div v-if="enableSearch" class="relative w-56 text-slate-500">
          <FormInput v-model="search" type="text" class="w-56 pr-10" placeholder="Search..." />
          <Lucide icon="Search" class="absolute inset-y-0 right-0 w-4 h-4 my-auto mr-3" />
        </div>
        <Button
          @click="$emit('data-list-changed', { page: data?.meta?.current_page ? data?.meta?.current_page : 1, per_page: pageSize, search: search })">
          <Lucide icon="RefreshCw" class="w-4 h-4" />
        </Button>
        <Menu>
          <Menu.Button v-if="canPrint" :as="Button" class="px-2">
            <span class="flex items-center justify-center w-5 h-5">
              <Lucide icon="Printer" class="w-4 h-4" />
            </span>
          </Menu.Button>
          <Menu.Items v-if="canPrint || canExport" class="w-40" placement="bottom-end">
            <Menu.Item v-if="canPrint">
              <Lucide icon="Printer" class="w-4 h-4 mr-2" />
              {{ t("components.data-list.print") }}
            </Menu.Item>
            <Menu.Item v-if="canExport">
              <Lucide icon="FileText" class="w-4 h-4 mr-2" />
              {{ t("components.data-list.export_to_excel") }}
            </Menu.Item>
            <Menu.Item v-if="canExport">
              <Lucide icon="FileText" class="w-4 h-4 mr-2" />
              {{ t("components.data-list.export_to_pdf") }}
            </Menu.Item>
          </Menu.Items>
        </Menu>
      </div>
    </div>
    <div class="overflow-x-auto mb-4">
      <slot v-if="!dataNotFound" name="table" :data-list="data"></slot>
      <Table v-if="dataNotFound" class="mt-5">
        <Table.Tbody>
          <Table.Tr>
            <Table.Td>
              {{ t('components.data-list.data_not_found') }}
            </Table.Td>
          </Table.Tr>
        </Table.Tbody>
      </Table>
    </div>
    <div class="flex flex-wrap intro-y sm:flex-row sm:flex-nowrap">
      <Pagination class="w-full sm:w-auto sm:mr-auto">
        <Pagination.Link @click="$emit('data-list-changed', { page: first, per_page: pageSize, search: search })">
          <Lucide icon="ChevronsLeft" class="w-4 h-4" />
        </Pagination.Link>
        <Pagination.Link @click="$emit('data-list-changed', { page: previous, per_page: pageSize, search: search })">
          <Lucide icon="ChevronLeft" class="w-4 h-4" />
        </Pagination.Link>
        <Pagination.Link v-for="n in pages" :key="n">
          {{ n > 0 ? n : '...' }}
        </Pagination.Link>
        <Pagination.Link @click="$emit('data-list-changed', { page: next, per_page: pageSize, search: search })">
          <Lucide icon="ChevronRight" class="w-4 h-4" />
        </Pagination.Link>
        <Pagination.Link @click="$emit('data-list-changed', { page: last, per_page: pageSize, search: search })">
          <Lucide icon="ChevronsRight" class="w-4 h-4" />
        </Pagination.Link>
      </Pagination>
      <FormSelect v-model="pageSize" class="w-20 mt-3 sm:mt-0"
        @change="$emit('data-list-changed', { page: data?.meta?.current_page ? data?.meta?.current_page : 1, per_page: pageSize, search: search })">
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="35">35</option>
        <option value="50">50</option>
      </FormSelect>
    </div>
  </div>
</template>
