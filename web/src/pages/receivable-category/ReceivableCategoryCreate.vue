<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import ReceivableCategoryService from '@/services/ReceivableCategoryService';
import CacheService from '@/services/CacheService';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import {
  FormInput,
  FormLabel,
  FormErrorMessages,
  FormInputCode,
} from '@/components/Base/Form';
import { CardState } from '@/types/enums/CardState';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();
const receivableCategoryService = new ReceivableCategoryService();
const cacheService = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.receivable_category.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.receivable_category.field_groups.receivable_category_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const receivableCategoryForm = receivableCategoryService.useReceivableCategoryCreateForm();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
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
});

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

watch(
  receivableCategoryForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('RECEIVABLE_CATEGORY_CREATE', newValue.data());
  }, 500),
  { deep: true },
);
// #endregion

// #region Methods - ReceivableCategory
const setCompanyIdData = () => {
  receivableCategoryForm.setData({
    company_id: selectedUserLocation.value.company.id,
  });
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('RECEIVABLE_CATEGORY_CREATE') as Record<string, unknown> | null;
  if (!data) return;

  if (!data.code) {
    data.code = '_AUTO_';
  }

  if (data.sequence === undefined || data.sequence === null) {
    data.sequence = 0;
  }

  receivableCategoryForm.setData(data);
};

const setCode = () => {
  receivableCategoryForm.forgetError('code');
  if (receivableCategoryForm.code == '_AUTO_') {
    receivableCategoryForm.setData({ code: '' });
  } else {
    receivableCategoryForm.setData({ code: '_AUTO_' });
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
  receivableCategoryForm.reset();
  receivableCategoryForm.setErrors({});
  receivableCategoryForm.setData({
    code: '_AUTO_',
    name: '',
    sequence: 0,
  });
  setCompanyIdData();
};

const onSubmit = async () => {
  if (receivableCategoryForm.hasErrors) {
    const firstErrorKey = Object.keys(receivableCategoryForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  emits('loading-state', true);
  await receivableCategoryForm
    .submit()
    .then(() => {
      resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-receivable-category-list' });
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
  <form id="receivableCategoryForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="receivableCategoryForm.company_id" />
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 sm:col-span-4">
              <FormLabel
                :class="{
                  'text-danger': receivableCategoryForm.invalid('code'),
                }"
              >
                {{ t('views.receivable_category.fields.code') }}
              </FormLabel>
              <FormInputCode
                id="code"
                v-model="receivableCategoryForm.code"
                :class="{
                  'border-danger': receivableCategoryForm.invalid('code'),
                }"
                :placeholder="t('views.receivable_category.fields.code')"
                @set-auto="setCode"
                @change="receivableCategoryForm.validate('code')"
              />
              <FormErrorMessages :messages="receivableCategoryForm.errors.code" />
            </div>

            <div class="col-span-12 sm:col-span-5">
              <FormLabel
                :class="{
                  'text-danger': receivableCategoryForm.invalid('name'),
                }"
              >
                {{ t('views.receivable_category.fields.name') }}
              </FormLabel>
              <FormInput
                id="name"
                v-model="receivableCategoryForm.name"
                type="text"
                :class="{
                  'border-danger': receivableCategoryForm.invalid('name'),
                }"
                :placeholder="t('views.receivable_category.fields.name')"
                @change="receivableCategoryForm.validate('name')"
              />
              <FormErrorMessages :messages="receivableCategoryForm.errors.name" />
            </div>

            <div class="col-span-12 sm:col-span-3">
              <FormLabel
                :class="{
                  'text-danger': receivableCategoryForm.invalid('sequence'),
                }"
              >
                {{ t('views.receivable_category.fields.sequence') }}
              </FormLabel>
              <FormInput
                id="sequence"
                v-model.number="receivableCategoryForm.sequence"
                type="number"
                min="0"
                :class="{
                  'border-danger': receivableCategoryForm.invalid('sequence'),
                }"
                :placeholder="t('views.receivable_category.fields.sequence')"
                @change="receivableCategoryForm.validate('sequence')"
              />
              <FormErrorMessages :messages="receivableCategoryForm.errors.sequence" />
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
            :disabled="receivableCategoryForm.validating || receivableCategoryForm.hasErrors"
          >
            <Lucide v-if="receivableCategoryForm.validating" icon="Loader" class="animate-spin" />
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
