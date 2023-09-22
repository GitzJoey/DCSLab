<script setup lang="ts">
import { computed, ref, toRef, watch } from "vue";
import { FormInput, FormSelect } from "../../base-components/Form";
import Button from "../../base-components/Button";
import { Menu } from "../../base-components/Headless";
import Lucide from "../../base-components/Lucide";
import Pagination from "../../base-components/Pagination";
import { useI18n } from "vue-i18n";
import { debounce } from "lodash";

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

export interface DataListProps {
  visible: boolean,
  canPrint: boolean,
  canExport: boolean,
  enableSearch: boolean,
  pagination: PaginationData | null,
}

const { t } = useI18n();

const props = withDefaults(defineProps<DataListProps>(), {
  visible: true,
  canPrint: false,
  canExport: false,
  enableSearch: false,
  pagination: () => ({
    current_page: 0,
    from: null,
    last_page: 0,
    path: '',
    per_page: 10,
    to: null,
    total: 0,
  }),
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

const showExportButtonGroups = ref<boolean>(false);

const search = ref<string>('');
const perPage = ref<number>(10);

const textInfo = computed(() => {
  if (props.pagination == null) return '';
  if (props.pagination.from == null) return '';
  if (props.pagination.to == null) return '';
  if (props.pagination.total == null) return '';

  return t('components.data-list.showing') + ' ' + props.pagination.from.toString() + ' ' +
    t('components.data-list.to') + ' ' + props.pagination.to.toString() + ' ' +
    t('components.data-list.of') + ' ' + props.pagination.total.toString() + ' ' +
    t('components.data-list.entries');
});

const pages = computed(() => {
  if (props.pagination == null) return [];
  return generatePaginationArray(props.pagination.current_page, props.pagination.total, props.pagination.per_page);
});

const generatePaginationArray = (
  currentPage: number,
  totalRecords: number,
  perPage: number,
  maxVisiblePages = 7
): number[] => {
  if (currentPage === 0 || totalRecords === 0 || perPage === 0) {
    return [];
  }

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
  if (props.pagination != null)
    emits('dataListChanged', createDataEmittedPayload(search.value, 1, props.pagination.per_page));
}

const refreshButtonClicked = () => {
  if (props.pagination != null)
    emits('dataListChanged', createDataEmittedPayload(search.value, props.pagination.current_page, props.pagination.per_page));
}

const printButtonClicked = () => {
  emits('print');
}

const exportButtonClicked = (type: string) => {
  if (type.length == 0) {
    showExportButtonGroups.value = !showExportButtonGroups.value;
    return;
  }

  emits('export', type);
}

const paginationFirstButtonClicked = () => {
  if (props.pagination != null)
    emits('dataListChanged', createDataEmittedPayload(search.value, 1, props.pagination.per_page));
}

const paginationPreviousButtonClicked = () => {
  if (props.pagination != null) {
    if (props.pagination.current_page > 1)
      emits('dataListChanged', createDataEmittedPayload(search.value, props.pagination.current_page - 1, props.pagination.per_page));
  }
}

const paginationNumberButtonClicked = (n: number) => {
  if (props.pagination != null)
    emits('dataListChanged', createDataEmittedPayload(search.value, n, props.pagination.per_page));
}

const paginationNextButtonClicked = () => {
  if (props.pagination != null) {
    if (props.pagination.current_page != props.pagination.last_page)
      emits('dataListChanged', createDataEmittedPayload(search.value, props.pagination.current_page + 1, props.pagination.per_page));
  }
}

const paginationLastButtonClicked = () => {
  if (props.pagination != null)
    emits('dataListChanged', createDataEmittedPayload(search.value, props.pagination.last_page, props.pagination.per_page));
}

const pageSizeChanged = () => {
  if (props.pagination != null)
    emits('dataListChanged', createDataEmittedPayload(search.value, 1, perPage.value));
}

watch(
  search,
  debounce((): void => {
    searchTextboxChanged();
  }, 500)
);

watch(perPage,
  (val, oldVal) => {
    if (val != oldVal) pageSizeChanged();
  }
);
</script>

<template>
  <div v-if="visible" class="intro-y box p-5 mt-5">
    <div class="grid justify-items-end">
      <div class="flex flex-row gap-2">
        <div v-if="enableSearch" class="relative w-56 text-slate-500">
          <FormInput v-model="search" type="text" class="w-56 pr-10" placeholder="Search..." />
          <Lucide icon="Search" class="absolute inset-y-0 right-0 w-4 h-4 my-auto mr-3" />
        </div>

        <Button @click="refreshButtonClicked">
          <Lucide icon="RefreshCw" class="w-4 h-4" />
        </Button>

        <Button v-if="canPrint" @click="printButtonClicked">
          <Lucide icon="Printer" class="w-4 h-4" />
        </Button>

        <Button v-if="canExport" @click="exportButtonClicked('')">
          <Lucide icon="FileText" class="w-4 h-4" />
        </Button>
      </div>
    </div>

    <div v-if="showExportButtonGroups" class="mt-2 grid justify-items-end">
      <div class="flex gap-2">
        <Button size="sm" @click="exportButtonClicked('PDF')">
          {{ t('components.data-list.export_to_pdf') }}
        </Button>
        <Button size="sm" @click="exportButtonClicked('XLS')">
          {{ t('components.data-list.export_to_excel') }}
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
              <Pagination.Link :active="pagination ? n == pagination.current_page : false"
                @click="paginationNumberButtonClicked(n)">
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
      <div class="w-1/2 flex items-center justify-start">
        {{ textInfo }}
      </div>
      <div class="w-1/2 flex justify-end">
        <FormSelect v-model="perPage" class="w-20 mt-3 sm:mt-0">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </FormSelect>
      </div>
    </div>
  </div>
</template>
