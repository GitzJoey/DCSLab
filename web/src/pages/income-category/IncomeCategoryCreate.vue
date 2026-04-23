<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import IncomeCategoryService from '@/services/IncomeCategoryService';
import CacheService from '@/services/CacheService';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import {
  FormInput,
  FormLabel,
  FormErrorMessages,
  FormInputCode,
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
const incomeCategoryService = new IncomeCategoryService();
const cacheService = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.income_category.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.income_category.field_groups.income_category_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const incomeCategoryForm = incomeCategoryService.useIncomeCategoryCreateForm();
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
  incomeCategoryForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('INCOME_CATEGORY_CREATE', newValue.data());
  }, 500),
  { deep: true },
);
// #endregion

// #region Methods - IncomeCategory
const setCompanyIdData = () => {
  incomeCategoryForm.setData({
    company_id: selectedUserLocation.value.company.id,
  });
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('INCOME_CATEGORY_CREATE') as Record<string, unknown> | null;
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

  incomeCategoryForm.setData(data);
};

const loadParentDDL = async (search = ''): Promise<void> => {
  if (!selectedUserLocation.value) return;

  const result = await incomeCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    parent_id: undefined,
    has_children: undefined,
    include_id: incomeCategoryForm.parent_id ?? undefined,
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
  incomeCategoryForm.forgetError('code');
  if (incomeCategoryForm.code == '_AUTO_') {
    incomeCategoryForm.setData({ code: '' });
  } else {
    incomeCategoryForm.setData({ code: '_AUTO_' });
  }
};
// #endregion

// #region Actions
const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const resetForm = async () => {
  incomeCategoryForm.reset();
  incomeCategoryForm.setErrors({});
  incomeCategoryForm.setData({
    parent_id: null,
    code: '_AUTO_',
    name: '',
    sequence: 0,
  });
  parentSearch.value = '';
  setCompanyIdData();
  await loadParentDDL();
};

const onSubmit = async () => {
  if (incomeCategoryForm.hasErrors) {
    const firstErrorKey = Object.keys(incomeCategoryForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  emits('loading-state', true);
  await incomeCategoryForm
    .submit()
    .then(() => {
      resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-income-category-list' });
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
  <form id="incomeCategoryForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="incomeCategoryForm.company_id" />
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12">
              <FormLabel
                :class="{
                  'text-danger': incomeCategoryForm.invalid('parent_id'),
                }"
              >
                {{ t('views.income_category.fields.parent') }}
              </FormLabel>
              <FormSelectSearch
                id="parent_id"
                v-model="incomeCategoryForm.parent_id"
                v-model:search="parentSearch"
                :options="parentOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{
                  'border-danger': incomeCategoryForm.invalid('parent_id'),
                }"
                @change="incomeCategoryForm.validate('parent_id')"
                @search="loadParentDDL"
                @clear="incomeCategoryForm.validate('parent_id')"
              />
              <FormErrorMessages :messages="incomeCategoryForm.errors.parent_id" />
            </div>

            <div class="col-span-12 sm:col-span-4">
              <FormLabel
                :class="{
                  'text-danger': incomeCategoryForm.invalid('code'),
                }"
              >
                {{ t('views.income_category.fields.code') }}
              </FormLabel>
              <FormInputCode
                id="code"
                v-model="incomeCategoryForm.code"
                :class="{
                  'border-danger': incomeCategoryForm.invalid('code'),
                }"
                :placeholder="t('views.income_category.fields.code')"
                @set-auto="setCode"
                @change="incomeCategoryForm.validate('code')"
              />
              <FormErrorMessages :messages="incomeCategoryForm.errors.code" />
            </div>

            <div class="col-span-12 sm:col-span-5">
              <FormLabel
                :class="{
                  'text-danger': incomeCategoryForm.invalid('name'),
                }"
              >
                {{ t('views.income_category.fields.name') }}
              </FormLabel>
              <FormInput
                id="name"
                v-model="incomeCategoryForm.name"
                type="text"
                :class="{
                  'border-danger': incomeCategoryForm.invalid('name'),
                }"
                :placeholder="t('views.income_category.fields.name')"
                @change="incomeCategoryForm.validate('name')"
              />
              <FormErrorMessages :messages="incomeCategoryForm.errors.name" />
            </div>

            <div class="col-span-12 sm:col-span-3">
              <FormLabel
                :class="{
                  'text-danger': incomeCategoryForm.invalid('sequence'),
                }"
              >
                {{ t('views.income_category.fields.sequence') }}
              </FormLabel>
              <FormInput
                id="sequence"
                v-model.number="incomeCategoryForm.sequence"
                type="number"
                min="0"
                :class="{
                  'border-danger': incomeCategoryForm.invalid('sequence'),
                }"
                :placeholder="t('views.income_category.fields.sequence')"
                @change="incomeCategoryForm.validate('sequence')"
              />
              <FormErrorMessages :messages="incomeCategoryForm.errors.sequence" />
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
            :disabled="incomeCategoryForm.validating || incomeCategoryForm.hasErrors"
          >
            <Lucide v-if="incomeCategoryForm.validating" icon="Loader" class="animate-spin" />
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
