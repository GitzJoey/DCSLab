<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import DebtCreditorService from '@/services/DebtCreditorService';
import CacheService from '@/services/CacheService';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import { FormErrorMessages, FormInput, FormInputCode, FormLabel, FormTextarea } from '@/components/Base/Form';
import { CardState } from '@/types/enums/CardState';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();
const debtCreditorService = new DebtCreditorService();
const cacheService = new CacheService();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.debt_creditor.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.debt_creditor.field_groups.debt_creditor_data', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const debtCreditorForm = debtCreditorService.useDebtCreditorCreateForm();

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

onMounted(() => {
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

const setCompanyIdData = () => {
  debtCreditorForm.setData({
    company_id: selectedUserLocation.value.company.id,
  });
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('DEBT_CREDITOR_CREATE') as Record<string, unknown> | null;
  if (!data) return;

  debtCreditorForm.setData(data);
};

const handleExpandCard = (index: number) => {
  cards.value[index].state =
    cards.value[index].state === CardState.Expanded ? CardState.Collapsed : CardState.Expanded;
};

const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;

  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const onSubmit = async () => {
  if (debtCreditorForm.hasErrors) {
    const firstErrorKey = Object.keys(debtCreditorForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  emits('loading-state', true);
  await debtCreditorForm
    .submit()
    .then(() => {
      resetForm();
      emits('update-profile');
      showAlertPlaceholder('hidden', '', null);
      router.push({ name: 'side-menu-debt-creditor-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = () => {
  debtCreditorForm.reset();
  debtCreditorForm.setErrors({});
  setCompanyIdData();
};

const setCode = () => {
  debtCreditorForm.forgetError('code');
  debtCreditorForm.setData({
    code: debtCreditorForm.code === '_AUTO_' ? '' : '_AUTO_',
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

watch(
  debtCreditorForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('DEBT_CREDITOR_CREATE', newValue.data());
  }, 500),
  { deep: true },
);
</script>

<template>
  <form id="debtCreditorForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="debtCreditorForm.company_id" />
        </div>
      </template>
      <template #card-items-1>
        <div class="p-5">
          <div class="pb-4">
            <FormLabel :class="{ 'text-danger': debtCreditorForm.invalid('code') }">
              {{ t('views.debt_creditor.fields.code') }}
            </FormLabel>
            <FormInputCode
              id="code"
              v-model="debtCreditorForm.code"
              type="text"
              :class="{ 'border-danger': debtCreditorForm.invalid('code') }"
              :placeholder="t('views.debt_creditor.fields.code')"
              @set-auto="setCode"
              @change="debtCreditorForm.validate('code')"
            />
            <FormErrorMessages :messages="debtCreditorForm.errors.code" />
          </div>
          <div class="pb-4">
            <FormLabel :class="{ 'text-danger': debtCreditorForm.invalid('name') }">
              {{ t('views.debt_creditor.fields.name') }}
            </FormLabel>
            <FormInput
              id="name"
              v-model="debtCreditorForm.name"
              type="text"
              :class="{ 'border-danger': debtCreditorForm.invalid('name') }"
              :placeholder="t('views.debt_creditor.fields.name')"
              @change="debtCreditorForm.validate('name')"
            />
            <FormErrorMessages :messages="debtCreditorForm.errors.name" />
          </div>
          <div class="pb-4">
            <FormLabel :class="{ 'text-danger': debtCreditorForm.invalid('remarks') }">
              {{ t('views.debt_creditor.fields.remarks') }}
            </FormLabel>
            <FormTextarea
              id="remarks"
              v-model="debtCreditorForm.remarks"
              type="text"
              :class="{ 'border-danger': debtCreditorForm.invalid('remarks') }"
              :placeholder="t('views.debt_creditor.fields.remarks')"
              rows="3"
              @change="debtCreditorForm.validate('remarks')"
            />
            <FormErrorMessages :messages="debtCreditorForm.errors.remarks" />
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
            :disabled="debtCreditorForm.validating || debtCreditorForm.hasErrors"
          >
            <Lucide v-if="debtCreditorForm.validating" icon="Loader" class="animate-spin" />
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
