<script setup lang="ts">
import { computed, ref, toRef } from "vue";
import { FormInput, FormSelect } from "../../base-components/Form";
import Button from "../../base-components/Button";
import { Menu } from "../../base-components/Headless";
import Lucide from "../../base-components/Lucide";
import Pagination from "../../base-components/Pagination";
import { useI18n } from "vue-i18n";

export interface PaginationData {
  current_page: number,
  from: number | null,
  last_page: number,
  path: string,
  per_page: number,
  to: number | null,
  total: number,
}

export interface DataListEmittedData {
  search: {
    text: string,
  },
  pagination: {
    page: number,
    per_page: number,
  }
}

const { t } = useI18n();

const props = defineProps({
  visible: { type: Boolean, default: true },
  canPrint: { type: Boolean, default: false },
  canExport: { type: Boolean, default: false },
  enableSearch: { type: Boolean, default: false },
  pagination: {
    type: Object as () => PaginationData | null, default: () => ({
      current_page: 1,
      from: 1,
      last_page: 0,
      path: '',
      per_page: 10,
      to: 0,
      total: 0,
    })
  },
});

const emits = defineEmits<{
  (e: 'dataListChanged', data: DataListEmittedData): void,
  (e: 'print'): void,
  (e: 'export', exportType: string): void
}>();

const visible = toRef(props, "visible");
const canPrint = toRef(props, "canPrint");
const canExport = toRef(props, "canExport");
const enableSearch = toRef(props, "enableSearch");
const pagination = toRef(props, "pagination");

const search = ref<string>('');
const perPage = computed(() => {
  return pagination.value != null ? pagination.value.per_page : 10;
});

const pages = computed(() => {
  if (pagination.value == null) return [];
  return generatePaginationArray(pagination.value.current_page, pagination.value.total, pagination.value.per_page);
});

const generatePaginationArray = (
  currentPage: number,
  totalRecords: number,
  perPage: number,
  maxVisiblePages = 7
): number[] => {
  const totalPages = Math.ceil(totalRecords / perPage);
  const paginationArray: number[] = [];

  if (totalPages <= maxVisiblePages) {
    for (let i = 1; i <= totalPages; i++) {
      paginationArray.push(i);
    }
  } else {
    if (currentPage <= Math.floor(maxVisiblePages / 2) + 1) {
      for (let i = 1; i <= maxVisiblePages - 2; i++) {
        paginationArray.push(i);
      }
      paginationArray.push(-1);
      paginationArray.push(totalPages);
    } else if (currentPage >= totalPages - Math.floor(maxVisiblePages / 2)) {
      paginationArray.push(1);
      paginationArray.push(-1);
      for (let i = totalPages - (maxVisiblePages - 2); i <= totalPages; i++) {
        paginationArray.push(i);
      }
    } else {
      paginationArray.push(1);
      paginationArray.push(-1);
      const start = currentPage - Math.floor(maxVisiblePages / 2) + 1;
      const end = currentPage + Math.floor(maxVisiblePages / 2) - 1;
      for (let i = start; i <= end; i++) {
        paginationArray.push(i);
      }
      paginationArray.push(-1);
      paginationArray.push(totalPages);
    }
  }

  return paginationArray;
}

const createDataEmittedPayload = (search: string, page: number, per_page: number): DataListEmittedData => {
  let result: DataListEmittedData = {
    search: {
      text: search,
    },
    pagination: {
      page: page,
      per_page: per_page,
    }
  };

  return result;
}

const searchTextboxChanged = () => {
  if (pagination.value != null)
    emits('dataListChanged', createDataEmittedPayload(search.value, 1, pagination.value.per_page));
}

const refreshButtonClicked = () => {
  if (pagination.value != null)
    emits('dataListChanged', createDataEmittedPayload(search.value, pagination.value.current_page, pagination.value.per_page));
}

const printButtonClicked = () => {
  emits('print');
}

const exportButtonClicked = (type: string) => {
  emits('export', type);
}

const paginationFirstButtonClicked = () => {
  if (pagination.value != null)
    emits('dataListChanged', createDataEmittedPayload(search.value, 1, pagination.value.per_page));
}

const paginationPreviousButtonClicked = () => {
  if (pagination.value != null) {
    if (pagination.value.current_page > 1)
      emits('dataListChanged', createDataEmittedPayload(search.value, pagination.value.current_page - 1, pagination.value.per_page));
  }
}

const paginationNumberButtonClicked = (n: number) => {
  if (pagination.value != null)
    emits('dataListChanged', createDataEmittedPayload(search.value, n, pagination.value.per_page));
}

const paginationNextButtonClicked = () => {
  if (pagination.value != null) {
    if (pagination.value.current_page != pagination.value.last_page)
      emits('dataListChanged', createDataEmittedPayload(search.value, pagination.value.current_page + 1, pagination.value.per_page));
  }
}

const paginationLastButtonClicked = () => {
  if (pagination.value != null)
    emits('dataListChanged', createDataEmittedPayload(search.value, pagination.value.last_page, pagination.value.per_page));
}

const pageSizeChanged = () => {
  if (pagination.value != null)
    emits('dataListChanged', createDataEmittedPayload(search.value, pagination.value.current_page, pagination.value.per_page));
}
</script>

<template>
  <div v-if="visible" class="intro-y box p-5 mt-5">
    <div class="grid justify-items-end">
      <div class="flex flex-row gap-2">
        <div v-if="enableSearch" class="relative w-56 text-slate-500">
          <FormInput v-model="search" type="text" class="w-56 pr-10" placeholder="Search..."
            @change="searchTextboxChanged" />
          <Lucide icon="Search" class="absolute inset-y-0 right-0 w-4 h-4 my-auto mr-3" />
        </div>

        <Button @click="refreshButtonClicked">
          <Lucide icon="RefreshCw" class="w-4 h-4" />
        </Button>
      </div>
    </div>
    <div class="overflow-x-auto mb-4">
      <slot name="content"></slot>
    </div>
    <div class="flex flex-wrap justify-center intro-y sm:flex-row sm:flex-nowrap">
      <div v-if="pages.length > 0" class="pb-1 border-b">
        <Pagination class="w-full sm:w-auto sm:mr-auto">
          <Pagination.Link @click="paginationFirstButtonClicked">
            <Lucide icon="ChevronsLeft" class="w-4 h-4" />
          </Pagination.Link>
          <Pagination.Link @click="paginationPreviousButtonClicked">
            <Lucide icon="ChevronLeft" class="w-4 h-4" />
          </Pagination.Link>
          <template v-for="n in pages" :key="n">
            <template v-if="n > 0">
              <Pagination.Link :active="n == pagination?.current_page" @click="paginationNumberButtonClicked(n)">
                {{ n }}
              </Pagination.Link>
            </template>
            <template v-else>
              <Pagination.Text>
                {{ '...' }}
              </Pagination.Text>
            </template>
          </template>
          <Pagination.Link @click="paginationNextButtonClicked">
            <Lucide icon="ChevronRight" class="w-4 h-4" />
          </Pagination.Link>
          <Pagination.Link @click="paginationLastButtonClicked">
            <Lucide icon="ChevronsRight" class="w-4 h-4" />
          </Pagination.Link>
        </Pagination>
      </div>
    </div>
    <div v-if="pages.length > 0" class="flex">
      <div class="w-1/2 flex justify-start">
        <div>
          <Menu>
            <Menu.Button v-if="canPrint" :as="Button" class="px-2">
              <span class="flex items-center justify-center w-5 h-5">
                <Lucide icon="Printer" class="w-4 h-4" />
              </span>
            </Menu.Button>
            <Menu.Items v-if="canPrint || canExport" class="w-40" placement="right-start">
              <Menu.Item v-if="canPrint">
                <Lucide icon="Printer" class="w-4 h-4 mr-2" @click="printButtonClicked" />
                {{ t("components.data-list.print") }}
              </Menu.Item>
              <Menu.Item v-if="canExport">
                <Lucide icon="FileText" class="w-4 h-4 mr-2" @click="exportButtonClicked('XLS')" />
                {{ t("components.data-list.export_to_excel") }}
              </Menu.Item>
              <Menu.Item v-if="canExport">
                <Lucide icon="FileText" class="w-4 h-4 mr-2" @click="exportButtonClicked('PDF')" />
                {{ t("components.data-list.export_to_pdf") }}
              </Menu.Item>
            </Menu.Items>
          </Menu>
        </div>
      </div>
      <div class="w-1/2 flex justify-end">
        <FormSelect v-model="perPage" class="w-20 mt-3 sm:mt-0" @change="pageSizeChanged">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </FormSelect>
      </div>
    </div>
  </div>
</template>
