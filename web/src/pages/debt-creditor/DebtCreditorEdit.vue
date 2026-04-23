<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import DebtCreditorService from '@/services/DebtCreditorService';
import CacheService from '@/services/CacheService';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { FormErrorMessages, FormInput, FormInputCode, FormLabel, FormTextarea } from '@/components/Base/Form';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import { CardState } from '@/types/enums/CardState';
import { ViewMode } from '@/types/enums/ViewMode';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { DebtCreditor } from '@/types/models/DebtCreditor';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const selectedUserLocationStore = useSelectedUserLocationStore();
const debtCreditorService = new DebtCreditorService();
const cacheService = new CacheService();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.debt_creditor.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.debt_creditor.field_groups.debt_creditor_data', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const debtCreditorForm = debtCreditorService.useDebtCreditorEditForm(route.params.ulid as string);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_EDIT);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await loadData(route.params.ulid as string);
});

const loadData = async (ulid: string) => {
  emits('loading-state', true);
  const result: ServiceResponse<DebtCreditor | null> = await debtCreditorService.read(ulid);

  if (result.success && result.data) {
    debtCreditorForm.setData({
      company_id: result.data.company.id,
      code: result.data.code,
      name: result.data.name,
      remarks: result.data.remarks ?? '',
    });
  } else {
    router.push({ name: 'side-menu-debt-creditor-list' });
  }

  emits('loading-state', false);
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
      emits('update-profile');
      router.push({ name: 'side-menu-debt-creditor-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = async () => {
  debtCreditorForm.reset();
  debtCreditorForm.setErrors({});
  await loadData(route.params.ulid as string);
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
    cacheService.setLastEntity('DEBT_CREDITOR_EDIT', newValue.data());
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
