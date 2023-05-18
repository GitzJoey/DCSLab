<script setup lang="ts">
import { computed, ref, toRef } from "vue";
import { FormInput, FormSelect } from "../../base-components/Form";
import Button from "../../base-components/Button";
import { Menu } from "../../base-components/Headless";
import Lucide from "../../base-components/Lucide";
import Pagination from "../../base-components/Pagination";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  visible: { type: Boolean, default: true },
  canPrint: { type: Boolean, default: false },
  canExport: { type: Boolean, default: false },
  enableSearch: { type: Boolean, default: false },
  data: { type: Object, default: null },
});

const emit = defineEmits(["dataListChange", "print", "export"]);

const visible = toRef(props, "visible");
const canPrint = toRef(props, "canPrint");
const canExport = toRef(props, "canExport");
const enableSearch = toRef(props, "enableSearch");
const data = toRef(props, "data");

const search = ref("");
const pageSize = ref(10);

const dataNotFound = computed(() => {
  if (data.value == null) return true;

  return false;
});

const pages = computed(() => {
  if (
    data.value.meta.current_page !== undefined &&
    data.value.meta.last_page !== undefined
  ) {
    return paginate(
      data.value.meta.current_page,
      data.value.meta.total,
      2,
      "..."
    );
  } else {
    return [];
  }
});

const first = computed(() => {
  return 1;
});

let previous = computed(() => {
  if (data.value.meta.current_page === undefined) return 1;
  if (data.value.meta.current_page === 1) return 1;
  return data.value.meta.current_page - 1;
});

const next = computed(() => {
  if (data.value.meta.current_page === undefined) return 1;
  if (data.value.meta.current_page === data.value.meta.last_page)
    return data.value.meta.last_page;
  return data.value.meta.current_page + 1;
});

const last = computed(() => {
  return data.value.meta.last_page;
});

const paginate = (current: number, total: number, delta = 2, gap = "...") => {
  if (total <= 1) return [1];

  const center = [current] as (number | typeof gap)[];

  for (let i = 1; i <= delta; i++) {
    center.unshift(current - i);
    center.push(current + i);
  }

  const filteredCenter = center.filter((page) => +page > 1 && +page < +total);

  const includeLeftGap = current > 3 + delta;
  const includeLeftPages = current === 3 + delta;
  const includeRightGap = current < total - (2 + delta);
  const includeRightPages = current === total - (2 + delta);

  if (includeLeftPages) filteredCenter.unshift(2);
  if (includeRightPages) filteredCenter.push(total - 1);
  if (includeLeftGap) filteredCenter.unshift(gap);
  if (includeRightGap) filteredCenter.push(gap);

  return [1, ...filteredCenter, total];
};
</script>

<template>
  <div class="intro-y box p-5 mt-5">
    <div class="grid justify-items-end">
      <div class="flex flex-row gap-2">
        <div class="relative w-56 text-slate-500">
          <FormInput type="text" class="w-56 pr-10" placeholder="Search..." />
          <Lucide
            icon="Search"
            class="absolute inset-y-0 right-0 w-4 h-4 my-auto mr-3"
          />
        </div>
        <Button>
          <Lucide icon="RefreshCw" class="w-4 h-4" />
        </Button>
        <Menu>
          <Menu.Button :as="Button" class="px-2">
            <span class="flex items-center justify-center w-5 h-5">
              <Lucide icon="Printer" class="w-4 h-4" />
            </span>
          </Menu.Button>
          <Menu.Items class="w-40" placement="bottom-end">
            <Menu.Item>
              <Lucide icon="Printer" class="w-4 h-4 mr-2" />
              {{ t("components.data-list.print") }}
            </Menu.Item>
            <Menu.Item>
              <Lucide icon="FileText" class="w-4 h-4 mr-2" />
              {{ t("components.data-list.export_to_excel") }}
            </Menu.Item>
            <Menu.Item>
              <Lucide icon="FileText" class="w-4 h-4 mr-2" />
              {{ t("components.data-list.export_to_pdf") }}
            </Menu.Item>
          </Menu.Items>
        </Menu>
      </div>
    </div>
    <div class="overflow-x-auto mb-4">
      <slot name="table" :data-list="data"></slot>
    </div>
    <div class="flex flex-wrap intro-y sm:flex-row sm:flex-nowrap">
      <Pagination class="w-full sm:w-auto sm:mr-auto">
        <Pagination.Link>
          <Lucide icon="ChevronsLeft" class="w-4 h-4" />
        </Pagination.Link>
        <Pagination.Link>
          <Lucide icon="ChevronLeft" class="w-4 h-4" />
        </Pagination.Link>
        <Pagination.Link>...</Pagination.Link>
        <Pagination.Link>1</Pagination.Link>
        <Pagination.Link active>2</Pagination.Link>
        <Pagination.Link>3</Pagination.Link>
        <Pagination.Link>...</Pagination.Link>
        <Pagination.Link>
          <Lucide icon="ChevronRight" class="w-4 h-4" />
        </Pagination.Link>
        <Pagination.Link>
          <Lucide icon="ChevronsRight" class="w-4 h-4" />
        </Pagination.Link>
      </Pagination>
      <FormSelect v-model="pageSize" class="w-20 mt-3 sm:mt-0">
        <option>10</option>
        <option>25</option>
        <option>35</option>
        <option>50</option>
      </FormSelect>
    </div>
  </div>
</template>
