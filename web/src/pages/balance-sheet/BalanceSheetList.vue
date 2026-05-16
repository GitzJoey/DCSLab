<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { FormInputDateTime, FormLabel } from '@/components/Base/Form';
import Table from '@/components/Base/Table';
import TreeList from '@/components/TreeList/TreeList.vue';
import ChartOfAccountService from '@/services/ChartOfAccountService';
import JournalEntryItemService from '@/services/JournalEntryItemService';
import { ChartOfAccount } from '@/types/models/ChartOfAccount';
import { JournalEntryItem } from '@/types/models/JournalEntry';
import { Resource } from '@/types/resources/Resource';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import { ViewMode } from '@/types/enums/ViewMode';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';

interface TreeListRef {
  expandAll: () => void;
  collapseAll: () => void;
}

interface BalanceSheetNode extends ChartOfAccount {
  children?: Array<BalanceSheetNode>;
  ownDebit: number;
  ownCredit: number;
  ownBalance: number;
  balance: number;
}

type SummaryAccountType = 'asset' | 'liability' | 'equity';

const { t } = useI18n();
const router = useRouter();
const chartOfAccountService = new ChartOfAccountService();
const journalEntryItemService = new JournalEntryItemService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits([
  'mode-state',
  'loading-state',
  'show-alertplaceholder',
]);

const treeListRef = ref<TreeListRef | null>(null);
const filters = ref<{
  end_date: string | null;
}>({
  end_date: null,
});

const chartOfAccountLists = ref<Resource<Array<ChartOfAccount>> | null>({
  data: [],
});

const journalEntryItemLists = ref<Resource<Array<JournalEntryItem>> | null>({
  data: [],
});

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const accountBalances = computed(() => {
  const result = new Map<string, { debit: number; credit: number }>();

  for (const item of journalEntryItemLists.value?.data ?? []) {
    const chartOfAccountId = item.chart_of_account?.id;
    if (!chartOfAccountId) continue;

    const current = result.get(chartOfAccountId) ?? { debit: 0, credit: 0 };
    current.debit += Number(item.debit ?? 0);
    current.credit += Number(item.credit ?? 0);
    result.set(chartOfAccountId, current);
  }

  return result;
});

const balanceSheetItems = computed<Array<BalanceSheetNode>>(() => {
  const rootItems = chartOfAccountLists.value?.data ?? [];

  const normalizeAccount = (account: ChartOfAccount): BalanceSheetNode | null => {
    if (!['asset', 'liability', 'equity'].includes(account.account_type)) {
      return null;
    }

    const current = accountBalances.value.get(account.id) ?? { debit: 0, credit: 0 };
    const children = (account.children ?? [])
      .map((child) => normalizeAccount(child))
      .filter((child): child is BalanceSheetNode => child !== null);

    const ownBalance = account.normal_balance === 'debit'
      ? current.debit - current.credit
      : current.credit - current.debit;

    const balance = ownBalance + children.reduce((total, child) => total + child.balance, 0);

    return {
      ...account,
      children,
      ownDebit: current.debit,
      ownCredit: current.credit,
      ownBalance,
      balance,
    };
  };

  return rootItems
    .map((item) => normalizeAccount(item))
    .filter((item): item is BalanceSheetNode => item !== null);
});

const summaryBalance = computed<Record<SummaryAccountType, number>>(() => {
  const result: Record<SummaryAccountType, number> = {
    asset: 0,
    liability: 0,
    equity: 0,
  };

  for (const item of balanceSheetItems.value) {
    result[item.account_type as SummaryAccountType] += item.balance;
  }

  return result;
});

const totalLiabilityAndEquity = computed(() => summaryBalance.value.liability + summaryBalance.value.equity);

const balanceDifference = computed(() => summaryBalance.value.asset - totalLiabilityAndEquity.value);

const isBalanced = computed(() => balanceDifference.value === 0);

onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  const now = new Date();
  const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
  filters.value.end_date = formatDate(endOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');

  await getBalanceSheet(true);
});

const getBalanceSheet = async (refresh: boolean) => {
  emits('loading-state', true);

  const chartOfAccountResult: ServiceResponse<Resource<Array<ChartOfAccount>> | null> =
    await chartOfAccountService.readAnyGet({
      with_trashed: false,
      company_id: selectedUserLocation.value.company.id,
      search: '',
      has_parent: false,
      refresh,
      limit: 1000,
    });

  const journalEntryItemResult: ServiceResponse<Resource<Array<JournalEntryItem>> | null> =
    await journalEntryItemService.readAnyGet({
      company_id: selectedUserLocation.value.company.id,
      branch_id: selectedUserLocation.value.branch.id,
      end_date: filters.value.end_date || undefined,
      include_system_journals: true,
      refresh,
      limit: 1000,
    });

  if (chartOfAccountResult.success && chartOfAccountResult.data && journalEntryItemResult.success && journalEntryItemResult.data) {
    chartOfAccountLists.value = chartOfAccountResult.data;
    journalEntryItemLists.value = journalEntryItemResult.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder(
      'danger',
      '',
      ((chartOfAccountResult.errors ?? journalEntryItemResult.errors) as Record<string, Array<string>> | undefined) ?? null,
    );
  }

  emits('loading-state', false);
};

const handleFilterChange = async () => {
  await getBalanceSheet(true);
};

const getRowIndentStyle = (depth: number) => ({
  paddingLeft: `${depth * 1.5}rem`,
});

const expandAllTreeRows = () => {
  treeListRef.value?.expandAll();
};

const collapseAllTreeRows = () => {
  treeListRef.value?.collapseAll();
};

const toBalanceSheetNode = (item: unknown): BalanceSheetNode => item as BalanceSheetNode;

const isGroupRow = (item: unknown) => {
  const balanceSheetNode = toBalanceSheetNode(item);
  return balanceSheetNode.children !== undefined && balanceSheetNode.children.length > 0;
};

const getDisplayedDebit = (item: unknown) => {
  const balanceSheetNode = toBalanceSheetNode(item);
  return isGroupRow(balanceSheetNode) ? null : balanceSheetNode.ownDebit;
};

const getDisplayedCredit = (item: unknown) => {
  const balanceSheetNode = toBalanceSheetNode(item);
  return isGroupRow(balanceSheetNode) ? null : balanceSheetNode.ownCredit;
};

const getDisplayedBalance = (item: unknown) => {
  const balanceSheetNode = toBalanceSheetNode(item);
  return isGroupRow(balanceSheetNode) ? balanceSheetNode.balance : balanceSheetNode.ownBalance;
};

const getDisplayedGroupBalance = (item: unknown, isExpanded: boolean) => {
  const balanceSheetNode = toBalanceSheetNode(item);

  if (!isGroupRow(balanceSheetNode)) {
    return formatCurrency(balanceSheetNode.ownBalance);
  }

  return isExpanded ? '' : formatCurrency(balanceSheetNode.balance);
};

const showAlertPlaceholder = (
  pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark',
  pTitle: string,
  pAlertList: Record<string, Array<string>> | null,
) => {
  const ap: AlertPlaceholderProps = {
    alertType: pAlertType,
    title: pTitle,
    alertList: pAlertList,
  };

  emits('show-alertplaceholder', ap);
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y lg:col-span-12">
      <div class="grid grid-cols-12 gap-4 gap-y-3 mb-3">
        <div class="col-span-12 lg:col-span-4 md:col-span-6">
          <FormLabel>
            {{ t('views.balance_sheet.fields.end_date') }}
          </FormLabel>
          <FormInputDateTime
            v-model="filters.end_date"
            :placeholder="t('views.balance_sheet.fields.end_date')"
            @change="handleFilterChange"
          />
        </div>
        <div class="col-span-12 lg:col-span-8 md:col-span-6 flex items-end justify-end gap-2">
          <Button variant="outline-secondary" @click="collapseAllTreeRows">
            <Lucide icon="ChevronsUpDown" class="mr-1 h-4 w-4" />
            {{ t('views.balance_sheet.actions.collapse_all') }}
          </Button>
          <Button variant="outline-secondary" @click="expandAllTreeRows">
            <Lucide icon="ListTree" class="mr-1 h-4 w-4" />
            {{ t('views.balance_sheet.actions.expand_all') }}
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 mb-4 lg:grid-cols-3">
        <div class="rounded border border-slate-200 bg-white p-4 dark:border-darkmode-400 dark:bg-darkmode-600">
          <div class="text-slate-500 text-xs uppercase">{{ t('views.balance_sheet.summary.asset') }}</div>
          <div class="mt-1 text-lg font-medium">{{ formatCurrency(summaryBalance.asset) }}</div>
        </div>
        <div class="rounded border border-slate-200 bg-white p-4 dark:border-darkmode-400 dark:bg-darkmode-600">
          <div class="text-slate-500 text-xs uppercase">{{ t('views.balance_sheet.summary.liability_and_equity') }}</div>
          <div class="mt-1 text-lg font-medium">{{ formatCurrency(totalLiabilityAndEquity) }}</div>
          <div class="mt-1 text-xs text-slate-500">
            {{ t('views.balance_sheet.summary.liability') }}: {{ formatCurrency(summaryBalance.liability) }}
          </div>
          <div class="text-xs text-slate-500">
            {{ t('views.balance_sheet.summary.equity') }}: {{ formatCurrency(summaryBalance.equity) }}
          </div>
        </div>
        <div
          class="rounded border p-4"
          :class="isBalanced
            ? 'border-success/30 bg-success/5 dark:border-success/20 dark:bg-success/10'
            : 'border-warning/30 bg-warning/5 dark:border-warning/20 dark:bg-warning/10'"
        >
          <div class="text-slate-500 text-xs uppercase">{{ t('views.balance_sheet.summary.difference') }}</div>
          <div class="mt-1 text-lg font-medium">{{ formatCurrency(Math.abs(balanceDifference)) }}</div>
          <div class="mt-1 text-xs" :class="isBalanced ? 'text-success' : 'text-warning'">
            {{
              isBalanced
                ? t('views.balance_sheet.summary.status_balanced')
                : t('views.balance_sheet.summary.status_unbalanced')
            }}
          </div>
        </div>
      </div>

      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.balance_sheet.fields.code') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.balance_sheet.fields.name') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap text-right">
              {{ t('views.balance_sheet.fields.debit') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap text-right">
              {{ t('views.balance_sheet.fields.credit') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap text-right">
              {{ t('views.balance_sheet.fields.balance') }}
            </Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody>
          <template v-if="balanceSheetItems.length === 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="5">
                <div class="flex justify-center italic">
                  {{ t('components.data-list.data_not_found') }}
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
          <TreeList v-else ref="treeListRef" :items="balanceSheetItems">
            <template #row="{ item, depth, hasChildren, isExpanded, toggle }">
              <Table.Tr class="intro-x">
                <Table.Td class="font-medium">
                  {{ item.code }}
                </Table.Td>
                <Table.Td>
                  <div class="flex items-center gap-2" :style="getRowIndentStyle(depth)">
                    <button
                      v-if="hasChildren"
                      type="button"
                      class="flex h-5 w-5 items-center justify-center rounded border border-slate-200 text-slate-500 transition hover:bg-slate-100"
                      :aria-label="isExpanded ? 'Collapse row' : 'Expand row'"
                      @click="toggle()"
                    >
                      <Lucide :icon="isExpanded ? 'ChevronDown' : 'ChevronRight'" class="h-3 w-3" />
                    </button>
                    <Lucide v-else-if="depth > 0" icon="CornerDownRight" class="h-4 w-4 text-slate-400" />
                    <div v-else class="w-5"></div>
                    <div class="flex flex-col">
                      <span :class="{ 'font-medium': item.is_group }">{{ item.name }}</span>
                      <span class="text-xs text-slate-400">
                        {{
                          isGroupRow(item)
                            ? t('views.balance_sheet.labels.group_total')
                            : t('views.balance_sheet.labels.direct_mutation')
                        }}
                      </span>
                    </div>
                  </div>
                </Table.Td>
                <Table.Td class="text-right">
                  {{
                    getDisplayedDebit(item) === null
                      ? '-'
                      : formatCurrency(getDisplayedDebit(item) as number)
                  }}
                </Table.Td>
                <Table.Td class="text-right">
                  {{
                    getDisplayedCredit(item) === null
                      ? '-'
                      : formatCurrency(getDisplayedCredit(item) as number)
                  }}
                </Table.Td>
                <Table.Td class="text-right">
                  {{
                    isGroupRow(item)
                      ? getDisplayedGroupBalance(item, isExpanded)
                      : formatCurrency(getDisplayedBalance(item))
                  }}
                </Table.Td>
              </Table.Tr>
            </template>
          </TreeList>
        </Table.Tbody>
      </Table>
    </div>
  </div>
</template>
