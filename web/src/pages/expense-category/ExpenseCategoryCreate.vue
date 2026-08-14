<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import ExpenseCategoryService from '@/services/ExpenseCategoryService';
import CacheService from '@/services/CacheService';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import {
  FormInput,
  FormLabel,
  FormErrorMessages,
  FormInputCode,
  FormSelect,
  FormSelectSearch,
} from '@/components/Base/Form';
import { CardState } from '@/types/enums/CardState';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { DropDownOption } from '@/types/models/DropDownOption';
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();
const expenseCategoryService = new ExpenseCategoryService();
const cacheService = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.expense_category.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.expense_category.field_groups.expense_category_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const expenseCategoryForm = expenseCategoryService.useExpenseCategoryCreateForm();
const parentDDL = ref<Array<DropDownOption> | null>(null);
const parentSearch = ref<string>('');
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
const parentOptions = computed(() =>
  (parentDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);
const categoryTypeOptions = computed(() => [
  {
    value: 'expense',
    label: t('views.expense_category.options.category_type.expense'),
  },
  {
    value: 'other_expense',
    label: t('views.expense_category.options.category_type.other_expense'),
  },
]);
// #endregion

// #region Vue Core
onMounted(async () => {
  emits('mode-state', ViewMode.FORM_CREATE);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  loadFromCache();
  setCompanyIdData();
  await loadParentDDL();
});

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

watch(
  expenseCategoryForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('EXPENSE_CATEGORY_CREATE', newValue.data());
  }, 500),
  { deep: true },
);
// #endregion

// #region Methods - ExpenseCategory
const setCompanyIdData = () => {
  expenseCategoryForm.setData({
    company_id: selectedUserLocation.value.company.id,
  });
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('EXPENSE_CATEGORY_CREATE') as Record<string, unknown> | null;
  if (!data) return;

  if (!data.code) {
    data.code = '_AUTO_';
  }

  if (data.sequence === undefined || data.sequence === null) {
    data.sequence = 0;
  }

  if (!('parent_id' in data)) {
    data.parent_id = null;
  }

  if (!('category_type' in data) || !data.category_type) {
    data.category_type = 'expense';
  }

  expenseCategoryForm.setData(data);
};

const loadParentDDL = async (search = ''): Promise<void> => {
  if (!selectedUserLocation.value) return;

  const result = await expenseCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    parent_id: undefined,
    has_children: undefined,
    include_id: expenseCategoryForm.parent_id ?? undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    parentDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: `${item.display_code} - ${item.name}`,
    }));
  }
};

const setCode = () => {
  expenseCategoryForm.forgetError('code');
  if (expenseCategoryForm.code == '_AUTO_') {
    expenseCategoryForm.setData({ code: '' });
  } else {
    expenseCategoryForm.setData({ code: '_AUTO_' });
  }
};

const handleParentChange = () => {
  if (expenseCategoryForm.parent_id) {
    expenseCategoryForm.setData({
      category_type: null,
    });
    expenseCategoryForm.forgetError('category_type');
  } else if (!expenseCategoryForm.category_type) {
    expenseCategoryForm.setData({
      category_type: 'expense',
    });
  }

  expenseCategoryForm.validate('parent_id');
};
// #endregion

// #region Actions
const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const resetForm = async () => {
  expenseCategoryForm.reset();
  expenseCategoryForm.setErrors({});
  expenseCategoryForm.setData({
    parent_id: null,
    category_type: 'expense',
    code: '_AUTO_',
    name: '',
    sequence: 0,
  });
  parentSearch.value = '';
  setCompanyIdData();
  await loadParentDDL();
};

const onSubmit = async () => {
  if (expenseCategoryForm.hasErrors) {
    const firstErrorKey = Object.keys(expenseCategoryForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  emits('loading-state', true);
  await expenseCategoryForm
    .submit()
    .then(() => {
      resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-expense-category-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
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
// #endregion
</script>

<template>
  <form id="expenseCategoryForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="expenseCategoryForm.company_id" />
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12">
              <FormLabel
                :class="{
                  'text-danger': expenseCategoryForm.invalid('parent_id'),
                }"
              >
                {{ t('views.expense_category.fields.parent') }}
              </FormLabel>
              <FormSelectSearch
                id="parent_id"
                v-model="expenseCategoryForm.parent_id"
                v-model:search="parentSearch"
                :options="parentOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{
                  'border-danger': expenseCategoryForm.invalid('parent_id'),
                }"
                @change="handleParentChange"
                @search="loadParentDDL"
                @clear="handleParentChange"
              />
              <FormErrorMessages :messages="expenseCategoryForm.errors.parent_id" />
            </div>

            <div v-if="!expenseCategoryForm.parent_id" class="col-span-12 sm:col-span-4">
              <FormLabel
                :class="{
                  'text-danger': expenseCategoryForm.invalid('category_type'),
                }"
              >
                {{ t('views.expense_category.fields.category_type') }}
              </FormLabel>
              <FormSelect
                id="category_type"
                v-model="expenseCategoryForm.category_type"
                :class="{
                  'border-danger': expenseCategoryForm.invalid('category_type'),
                }"
                @change="expenseCategoryForm.validate('category_type')"
              >
                <option v-for="item in categoryTypeOptions" :key="item.value" :value="item.value">
                  {{ item.label }}
                </option>
              </FormSelect>
              <div class="mt-1 text-xs text-slate-500">
                {{ t('views.expense_category.descriptions.category_type') }}
              </div>
              <FormErrorMessages :messages="expenseCategoryForm.errors.category_type" />
            </div>

            <div class="col-span-12 sm:col-span-4">
              <FormLabel
                :class="{
                  'text-danger': expenseCategoryForm.invalid('code'),
                }"
              >
                {{ t('views.expense_category.fields.code') }}
              </FormLabel>
              <FormInputCode
                id="code"
                v-model="expenseCategoryForm.code"
                :class="{
                  'border-danger': expenseCategoryForm.invalid('code'),
                }"
                :placeholder="t('views.expense_category.fields.code')"
                @set-auto="setCode"
                @change="expenseCategoryForm.validate('code')"
              />
              <FormErrorMessages :messages="expenseCategoryForm.errors.code" />
            </div>

            <div class="col-span-12 sm:col-span-5">
              <FormLabel
                :class="{
                  'text-danger': expenseCategoryForm.invalid('name'),
                }"
              >
                {{ t('views.expense_category.fields.name') }}
              </FormLabel>
              <FormInput
                id="name"
                v-model="expenseCategoryForm.name"
                type="text"
                :class="{
                  'border-danger': expenseCategoryForm.invalid('name'),
                }"
                :placeholder="t('views.expense_category.fields.name')"
                @change="expenseCategoryForm.validate('name')"
              />
              <FormErrorMessages :messages="expenseCategoryForm.errors.name" />
            </div>

            <div class="col-span-12 sm:col-span-3">
              <FormLabel
                :class="{
                  'text-danger': expenseCategoryForm.invalid('sequence'),
                }"
              >
                {{ t('views.expense_category.fields.sequence') }}
              </FormLabel>
              <FormInput
                id="sequence"
                v-model.number="expenseCategoryForm.sequence"
                type="number"
                min="0"
                :class="{
                  'border-danger': expenseCategoryForm.invalid('sequence'),
                }"
                :placeholder="t('views.expense_category.fields.sequence')"
                @change="expenseCategoryForm.validate('sequence')"
              />
              <FormErrorMessages :messages="expenseCategoryForm.errors.sequence" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-button>
        <div class="flex gap-4">
          <Button
            type="submit"
            href="#"
            variant="primary"
            class="w-28 shadow-md"
            :disabled="expenseCategoryForm.validating || expenseCategoryForm.hasErrors"
          >
            <Lucide v-if="expenseCategoryForm.validating" icon="Loader" class="animate-spin" />
            <template v-else>
              {{ t('components.buttons.submit') }}
            </template>
          </Button>
          <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md" @click="resetForm">
            {{ t('components.buttons.reset') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>
</template>
