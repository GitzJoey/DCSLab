<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import DebtCategoryService from '@/services/DebtCategoryService';
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
const debtCategoryService = new DebtCategoryService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.debt_category.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.debt_category.field_groups.debt_category_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const debtCategoryForm = debtCategoryService.useDebtCategoryEditForm(route.params.ulid.toString());
const companyCode = ref<string>('');
const companyName = ref<string>('');
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

// #region Methods - DebtCategory
const loadData = async () => {
  emits('loading-state', true);
  const result = await debtCategoryService.read(route.params.ulid.toString());
  emits('loading-state', false);

  if (result.success && result.data) {
    companyCode.value = result.data.company?.code ?? '';
    companyName.value = result.data.company?.name ?? '';

    debtCategoryForm.setData({
      company_id: result.data.company?.id ?? '',
      code: result.data.code,
      name: result.data.name,
      sequence: result.data.sequence,
    } as any);
  } else {
    router.push({ name: 'side-menu-debt-category-list' });
  }
};

const setCode = () => {
  debtCategoryForm.forgetError('code');
  if (debtCategoryForm.code == '_AUTO_') {
    debtCategoryForm.setData({ code: '' });
  } else {
    debtCategoryForm.setData({ code: '_AUTO_' });
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
  debtCategoryForm.reset();
  debtCategoryForm.setErrors({});
  await loadData();
};

const onSubmit = async () => {
  if (debtCategoryForm.hasErrors) {
    const firstErrorKey = Object.keys(debtCategoryForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  emits('loading-state', true);
  await debtCategoryForm
    .submit()
    .then(() => {
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-debt-category-list' });
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
  <form id="debtCategoryForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ companyCode }}
            <br />
            {{ companyName }}
          </FormLabel>
          <FormInput type="hidden" v-model="debtCategoryForm.company_id" />
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 sm:col-span-4">
              <FormLabel
                :class="{
                  'text-danger': debtCategoryForm.invalid('code'),
                }"
              >
                {{ t('views.debt_category.fields.code') }}
              </FormLabel>
              <FormInputCode
                id="code"
                v-model="debtCategoryForm.code"
                :class="{
                  'border-danger': debtCategoryForm.invalid('code'),
                }"
                :placeholder="t('views.debt_category.fields.code')"
                @set-auto="setCode"
                @change="debtCategoryForm.validate('code')"
              />
              <FormErrorMessages :messages="debtCategoryForm.errors.code" />
            </div>

            <div class="col-span-12 sm:col-span-5">
              <FormLabel
                :class="{
                  'text-danger': debtCategoryForm.invalid('name'),
                }"
              >
                {{ t('views.debt_category.fields.name') }}
              </FormLabel>
              <FormInput
                id="name"
                v-model="debtCategoryForm.name"
                type="text"
                :class="{
                  'border-danger': debtCategoryForm.invalid('name'),
                }"
                :placeholder="t('views.debt_category.fields.name')"
                @change="debtCategoryForm.validate('name')"
              />
              <FormErrorMessages :messages="debtCategoryForm.errors.name" />
            </div>

            <div class="col-span-12 sm:col-span-3">
              <FormLabel
                :class="{
                  'text-danger': debtCategoryForm.invalid('sequence'),
                }"
              >
                {{ t('views.debt_category.fields.sequence') }}
              </FormLabel>
              <FormInput
                id="sequence"
                v-model.number="debtCategoryForm.sequence"
                type="number"
                min="0"
                :class="{
                  'border-danger': debtCategoryForm.invalid('sequence'),
                }"
                :placeholder="t('views.debt_category.fields.sequence')"
                @change="debtCategoryForm.validate('sequence')"
              />
              <FormErrorMessages :messages="debtCategoryForm.errors.sequence" />
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
            :disabled="debtCategoryForm.validating || debtCategoryForm.hasErrors"
          >
            <Lucide v-if="debtCategoryForm.validating" icon="Loader" class="animate-spin" />
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
