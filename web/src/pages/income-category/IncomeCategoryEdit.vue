<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import IncomeCategoryService from '@/services/IncomeCategoryService';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import { FormInput, FormLabel, FormErrorMessages, FormInputCode } from '@/components/Base/Form';
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
const route = useRoute();
const selectedUserLocationStore = useSelectedUserLocationStore();
const incomeCategoryService = new IncomeCategoryService();

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

const incomeCategoryForm = incomeCategoryService.useIncomeCategoryEditForm(route.params.ulid.toString());
const companyCode = ref<string>('');
const companyName = ref<string>('');
const parentLabel = ref<string>('-');
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
// #endregion

// #region Vue Core
onMounted(async () => {
  emits('mode-state', ViewMode.FORM_EDIT);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await loadData();
});

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};
// #endregion

// #region Methods - IncomeCategory
const loadData = async () => {
  emits('loading-state', true);
  const result = await incomeCategoryService.read(route.params.ulid.toString());
  emits('loading-state', false);

  if (result.success && result.data) {
    companyCode.value = result.data.company?.code ?? '';
    companyName.value = result.data.company?.name ?? '';
    parentLabel.value = result.data.parent
      ? `${result.data.parent.display_code} - ${result.data.parent.name}`
      : '-';

    incomeCategoryForm.setData({
      company_id: result.data.company?.id ?? '',
      code: result.data.code,
      name: result.data.name,
      sequence: result.data.sequence,
    } as any);
  } else {
    router.push({ name: 'side-menu-income-category-list' });
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
  await loadData();
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
            {{ companyCode }}
            <br />
            {{ companyName }}
          </FormLabel>
          <FormInput type="hidden" v-model="incomeCategoryForm.company_id" />
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12">
              <FormLabel>{{ t('views.income_category.fields.parent') }}</FormLabel>
              <FormInput :model-value="parentLabel" type="text" readonly />
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
