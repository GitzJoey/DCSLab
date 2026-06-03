<script lang="ts" setup>
import { ref, h } from "vue";
import {
  type ColumnDef,
  type ColumnFiltersState,
  FlexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  type SortingState,
  useVueTable,
  type VisibilityState,
} from "@tanstack/vue-table";
import { ArrowUpDown, MoreVertical } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { CheckboxRoot, CheckboxControl } from "@/components/ui/checkbox";
import {
  MenuRoot,
  MenuTrigger,
  MenuPositioner,
  MenuContent,
  MenuItem,
  MenuCheckboxItem,
} from "@/components/ui/menu";
import { Input } from "@/components/ui/input";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";

export type Payment = {
  id: string;
  amount: number;
  status: "pending" | "processing" | "success" | "failed";
  badge: "success" | "pending" | "danger";
  email: string;
};

const data: Payment[] = [
  {
    id: "m5gr84i9",
    amount: 316,
    status: "success",
    badge: "success",
    email: "ken99@example.com",
  },
  {
    id: "3u1reuv4",
    amount: 242,
    status: "success",
    badge: "success",
    email: "Abe45@example.com",
  },
  {
    id: "derv1ws0",
    amount: 837,
    status: "processing",
    badge: "pending",
    email: "Monserrat44@example.com",
  },
  {
    id: "5kma53ae",
    amount: 874,
    status: "success",
    badge: "success",
    email: "Silas22@example.com",
  },
  {
    id: "bhqecj4p",
    amount: 721,
    status: "failed",
    badge: "danger",
    email: "carmella@example.com",
  },
];

const columns: ColumnDef<Payment>[] = [
  {
    id: "select",
    header: ({ table }) => {
      return h(
        CheckboxRoot,
        {
          checked:
            table.getIsAllPageRowsSelected() ||
            (table.getIsSomePageRowsSelected() && "indeterminate"),
          onCheckedChange: (value) =>
            table.toggleAllPageRowsSelected(!!value.checked),
        },
        {
          default: () => h(CheckboxControl, { "aria-label": "Select all" }),
        }
      );
    },
    cell: ({ row }) => {
      return h(
        CheckboxRoot,
        {
          checked: row.getIsSelected(),
          onCheckedChange: (value) => row.toggleSelected(!!value.checked),
        },
        {
          default: () => h(CheckboxControl, { "aria-label": "Select all" }),
        }
      );
    },
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: "status",
    header: "Status",
    cell: ({ row }) => {
      return h(
        Badge,
        {
          variant: row.original.badge,
          class: "capitalize",
        },
        {
          default: () => row.getValue("status"),
        }
      );
    },
  },
  {
    accessorKey: "email",
    header: ({ column }) => {
      return h(
        "div",
        {
          onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
          class: "flex gap-2 items-center cursor-pointer",
        },
        ["Email", h(ArrowUpDown, { class: "size-3" })]
      );
    },
    cell: ({ row }) => {
      return h("div", { class: "lowercase" }, row.getValue("email"));
    },
  },
  {
    accessorKey: "amount",
    header: () => h("div", { class: "text-right" }, "Amount"),
    cell: ({ row }) => {
      const amount = parseFloat(row.getValue("amount"));
      const formatted = new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
      }).format(amount);
      return h("div", { class: "text-right font-medium" }, formatted);
    },
  },
  {
    id: "actions",
    enableHiding: false,
    header: () => h("div", { class: "text-center" }, "Actions"),
    cell: ({ row }) => {
      const payment = row.original;
      return h(
        MenuRoot,
        {},
        {
          default: () => [
            h(
              MenuTrigger,
              { asChild: true },
              {
                default: () =>
                  h("div", { class: "cursor-pointer flex justify-center" }, [
                    h("span", { class: "sr-only" }, "Open menu"),
                    h(MoreVertical, { class: "size-4 opacity-70" }),
                  ]),
              }
            ),
            h(
              MenuPositioner,
              {},
              {
                default: () =>
                  h(
                    MenuContent,
                    {},
                    {
                      default: () => [
                        h(
                          MenuItem,
                          {
                            value: "copy",
                            onClick: () =>
                              navigator.clipboard.writeText(payment.id),
                          },
                          {
                            default: () => "Copy payment ID",
                          }
                        ),
                        h(
                          MenuItem,
                          { value: "view-cust" },
                          {
                            default: () => "View Customer",
                          }
                        ),
                        h(
                          MenuItem,
                          { value: "view-pay" },
                          {
                            default: () => "View Payment",
                          }
                        ),
                      ],
                    }
                  ),
              }
            ),
          ],
        }
      );
    },
  },
];

const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<VisibilityState>({});
const rowSelection = ref({});

const table = useVueTable({
  get data() {
    return data;
  },
  get columns() {
    return columns;
  },
  onSortingChange: (updaterOrValue) => {
    if (typeof updaterOrValue === "function") {
      sorting.value = updaterOrValue(sorting.value);
    } else {
      sorting.value = updaterOrValue;
    }
  },
  onColumnFiltersChange: (updaterOrValue) => {
    if (typeof updaterOrValue === "function") {
      columnFilters.value = updaterOrValue(columnFilters.value);
    } else {
      columnFilters.value = updaterOrValue;
    }
  },
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  onColumnVisibilityChange: (updaterOrValue) => {
    if (typeof updaterOrValue === "function") {
      columnVisibility.value = updaterOrValue(columnVisibility.value);
    } else {
      columnVisibility.value = updaterOrValue;
    }
  },
  onRowSelectionChange: (updaterOrValue) => {
    if (typeof updaterOrValue === "function") {
      rowSelection.value = updaterOrValue(rowSelection.value);
    } else {
      rowSelection.value = updaterOrValue;
    }
  },
  state: {
    get sorting() {
      return sorting.value;
    },
    get columnFilters() {
      return columnFilters.value;
    },
    get columnVisibility() {
      return columnVisibility.value;
    },
    get rowSelection() {
      return rowSelection.value;
    },
  },
});
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <div class="w-full">
          <div class="flex items-center py-4 gap-3">
            <Input
              placeholder="Filter emails..."
              :value="(table.getColumn('email')?.getFilterValue() as string) ?? ''"
              @input="(event: Event) => table.getColumn('email')?.setFilterValue((event.target as HTMLInputElement).value)"
              class="max-w-80"
            />
            <MenuRoot>
              <MenuTrigger class="ms-auto w-auto"> Show Columns </MenuTrigger>
              <MenuPositioner>
                <MenuContent>
                  <MenuCheckboxItem
                    v-for="column in table
                      .getAllColumns()
                      .filter((column) => column.getCanHide())"
                    :key="column.id"
                    class="capitalize"
                    :checked="column.getIsVisible()"
                    :onCheckedChange="
                      (value) => column.toggleVisibility(!!value)
                    "
                    :value="column.id"
                  >
                    {{ column.id }}
                  </MenuCheckboxItem>
                </MenuContent>
              </MenuPositioner>
            </MenuRoot>
          </div>
          <div class="relative -me-2">
            <Table variant="boxed">
              <TableHeader>
                <TableRow
                  v-for="headerGroup in table.getHeaderGroups()"
                  :key="headerGroup.id"
                >
                  <TableHead
                    v-for="header in headerGroup.headers"
                    :key="header.id"
                  >
                    <FlexRender
                      v-if="!header.isPlaceholder"
                      :render="header.column.columnDef.header"
                      :props="header.getContext()"
                    />
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <template v-if="table.getRowModel().rows?.length">
                  <TableRow
                    v-for="row in table.getRowModel().rows"
                    :key="row.id"
                    :data-state="row.getIsSelected() && 'selected'"
                  >
                    <TableCell
                      v-for="cell in row.getVisibleCells()"
                      :key="cell.id"
                    >
                      <FlexRender
                        :render="cell.column.columnDef.cell"
                        :props="cell.getContext()"
                      />
                    </TableCell>
                  </TableRow>
                </template>
                <template v-else>
                  <TableRow>
                    <TableCell
                      :colspan="columns.length"
                      class="h-24 text-center"
                    >
                      No results.
                    </TableCell>
                  </TableRow>
                </template>
              </TableBody>
            </Table>
          </div>
          <div class="flex items-center justify-end space-x-2 py-4">
            <div class="text-muted-foreground flex-1 text-sm">
              {{ table.getFilteredSelectedRowModel().rows.length }} of
              {{ table.getFilteredRowModel().rows.length }} row(s) selected.
            </div>
            <div class="space-x-2">
              <Button
                size="sm"
                @click="table.previousPage()"
                :disabled="!table.getCanPreviousPage()"
              >
                Previous
              </Button>
              <Button
                size="sm"
                @click="table.nextPage()"
                :disabled="!table.getCanNextPage()"
              >
                Next
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
