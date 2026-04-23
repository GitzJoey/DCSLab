<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import LiabilityCreditorService from '@/services/LiabilityCreditorService';
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
import type { LiabilityCreditor } from '@/types/models/LiabilityCreditor';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const selectedUserLocationStore = useSelectedUserLocationStore();
const liabilityCreditorService = new LiabilityCreditorService();
const cacheService = new CacheService();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.liability_creditor.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.liability_creditor.field_groups.liability_creditor_data', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const liabilityCreditorForm = liabilityCreditorService.useLiabilityCreditorEditForm(route.params.ulid as string);

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
  const result: ServiceResponse<LiabilityCreditor | null> = await liabilityCreditorService.read(ulid);

  if (result.success && result.data) {
    liabilityCreditorForm.setData({
      company_id: result.data.company.id,
      code: result.data.code,
      name: result.data.name,
      remarks: result.data.remarks ?? '',
    });
  } else {
    router.push({ name: 'side-menu-liability-creditor-list' });
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
  if (liabilityCreditorForm.hasErrors) {
    const firstErrorKey = Object.keys(liabilityCreditorForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  emits('loading-state', true);
  await liabilityCreditorForm
    .submit()
    .then(() => {
      emits('update-profile');
      router.push({ name: 'side-menu-liability-creditor-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = async () => {
  liabilityCreditorForm.reset();
  liabilityCreditorForm.setErrors({});
  await loadData(route.params.ulid as string);
};

const setCode = () => {
  liabilityCreditorForm.forgetError('code');
  liabilityCreditorForm.setData({
    code: liabilityCreditorForm.code === '_AUTO_' ? '' : '_AUTO_',
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
  liabilityCreditorForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('LIABILITY_CREDITOR_EDIT', newValue.data());
  }, 500),
  { deep: true },
);
</script>

<template>
  <form id="liabilityCreditorForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="liabilityCreditorForm.company_id" />
        </div>
      </template>
      <template #card-items-1>
        <div class="p-5">
          <div class="pb-4">
            <FormLabel :class="{ 'text-danger': liabilityCreditorForm.invalid('code') }">
              {{ t('views.liability_creditor.fields.code') }}
            </FormLabel>
            <FormInputCode
              id="code"
              v-model="liabilityCreditorForm.code"
              type="text"
              :class="{ 'border-danger': liabilityCreditorForm.invalid('code') }"
              :placeholder="t('views.liability_creditor.fields.code')"
              @set-auto="setCode"
              @change="liabilityCreditorForm.validate('code')"
            />
            <FormErrorMessages :messages="liabilityCreditorForm.errors.code" />
          </div>
          <div class="pb-4">
            <FormLabel :class="{ 'text-danger': liabilityCreditorForm.invalid('name') }">
              {{ t('views.liability_creditor.fields.name') }}
            </FormLabel>
            <FormInput
              id="name"
              v-model="liabilityCreditorForm.name"
              type="text"
              :class="{ 'border-danger': liabilityCreditorForm.invalid('name') }"
              :placeholder="t('views.liability_creditor.fields.name')"
              @change="liabilityCreditorForm.validate('name')"
            />
            <FormErrorMessages :messages="liabilityCreditorForm.errors.name" />
          </div>
          <div class="pb-4">
            <FormLabel :class="{ 'text-danger': liabilityCreditorForm.invalid('remarks') }">
              {{ t('views.liability_creditor.fields.remarks') }}
            </FormLabel>
            <FormTextarea
              id="remarks"
              v-model="liabilityCreditorForm.remarks"
              type="text"
              :class="{ 'border-danger': liabilityCreditorForm.invalid('remarks') }"
              :placeholder="t('views.liability_creditor.fields.remarks')"
              rows="3"
              @change="liabilityCreditorForm.validate('remarks')"
            />
            <FormErrorMessages :messages="liabilityCreditorForm.errors.remarks" />
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
            :disabled="liabilityCreditorForm.validating || liabilityCreditorForm.hasErrors"
          >
            <Lucide v-if="liabilityCreditorForm.validating" icon="Loader" class="animate-spin" />
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
