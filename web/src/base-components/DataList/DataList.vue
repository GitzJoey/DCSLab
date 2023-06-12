<script setup lang="ts">
import { computed, ref, toRef } from "vue";
import { FormInput, FormSelect } from "../../base-components/Form";
import Button from "../../base-components/Button";
import { Menu } from "../../base-components/Headless";
import Lucide from "../../base-components/Lucide";
import Pagination from "../../base-components/Pagination";
import { useI18n } from "vue-i18n";

export interface DataListData {
  search: {
    text: string,
  },
  pagination: {
    current_page: number,
    from: number,
    last_page: number,
    path: string,
    per_page: number,
    to: number,
    total: number,
  }
}

const { t } = useI18n();

const props = defineProps({
  visible: { type: Boolean, default: true },
  canPrint: { type: Boolean, default: false },
  canExport: { type: Boolean, default: false },
  enableSearch: { type: Boolean, default: false },
  data: {
    type: Object as () => DataListData, default: () => ({
      search: {
        text: '',
      },
      pagination: {
        current_page: 1,
        from: 1,
        last_page: 0,
        path: '',
        per_page: 10,
        to: 0,
        total: 0,
      }
    })
  },
});

const emits = defineEmits(["dataListChanged", "print", "export"]);

const visible = toRef(props, "visible");
const canPrint = toRef(props, "canPrint");
const canExport = toRef(props, "canExport");
const enableSearch = toRef(props, "enableSearch");
const data = toRef(props, "data");

const search = ref<string>('');

const pages = computed(() => {
  return generatePaginationArray(data.value.pagination.current_page, data.value.pagination.total);
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

const refreshButtonClicked = () => {
  emits('dataListChanged', data);
}

const printButtonClicked = () => {
  emits('print');
}

const exportButtonClicked = (type: string) => {
  emits('export', type);
}

const paginationFirstButtonClicked = () => {
  emits('dataListChanged', data);
}

const paginationPreviousButtonClicked = () => {
  emits('dataListChanged', data);
}

const paginationNumberButtonClicked = (n: number) => {
  emits('dataListChanged', data);
}

const paginationNextButtonClicked = () => {
  emits('dataListChanged', data);
}

const paginationLastButtonClicked = () => {
  emits('dataListChanged', data);
}

const pageSizeChanged = () => {
  emits('dataListChanged', data);
}
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
        <Menu>
          <Menu.Button v-if="canPrint" :as="Button" class="px-2">
            <span class="flex items-center justify-center w-5 h-5">
              <Lucide icon="Printer" class="w-4 h-4" />
            </span>
          </Menu.Button>
          <Menu.Items v-if="canPrint || canExport" class="w-40" placement="bottom-end">
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
    <div class="overflow-x-auto mb-4">
      <slot name="content"></slot>
    </div>
    <div class="flex flex-wrap intro-y sm:flex-row sm:flex-nowrap">
      <Pagination class="w-full sm:w-auto sm:mr-auto">
        <Pagination.Link @click="paginationFirstButtonClicked">
          <Lucide icon="ChevronsLeft" class="w-4 h-4" />
        </Pagination.Link>
        <Pagination.Link @click="paginationPreviousButtonClicked">
          <Lucide icon="ChevronLeft" class="w-4 h-4" />
        </Pagination.Link>
        <template v-for="n in pages" :key="n">
          <Pagination.Link v-if="n > 0" @click="paginationNumberButtonClicked(n)">
            {{ n }}
          </Pagination.Link>
          <Pagination.Link v-else>
            {{ '...' }}
          </Pagination.Link>
        </template>
        <Pagination.Link @click="paginationNextButtonClicked">
          <Lucide icon="ChevronRight" class="w-4 h-4" />
        </Pagination.Link>
        <Pagination.Link @click="paginationLastButtonClicked">
          <Lucide icon="ChevronsRight" class="w-4 h-4" />
        </Pagination.Link>
      </Pagination>
      <FormSelect v-model="data.pagination.per_page" class="w-20 mt-3 sm:mt-0" @change="pageSizeChanged">
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
      </FormSelect>
    </div>
  </div>
</template>
