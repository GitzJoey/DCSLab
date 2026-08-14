<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { ViewMode } from '@/types/enums/ViewMode';
import { TitleLayout } from '@/components/Base/Form/FormLayout';
import LoadingOverlay from '@/components/LoadingOverlay';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import AlertPlaceholder from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';

const { t } = useI18n();
const router = useRouter();

const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const titleView = ref<string>('views.journal_entry.report_entry_detail_title');

const alertType = ref<'danger' | 'success' | 'warning' | 'pending' | 'dark' | 'hidden'>('hidden');
const title = ref<string>('');
const alertList = ref<Record<string, Array<string>> | null>(null);

const backToList = async () => {
  resetAlertPlaceholder();
  mode.value = ViewMode.LIST;
  router.push({ name: 'side-menu-journal-entry-list' });
};

const onLoadingStateChanged = (state: boolean) => {
  loading.value = state;
};

const onModeStateChanged = (state: ViewMode) => {
  mode.value = state;
};

const onAlertPlaceholderTriggered = (apProps: AlertPlaceholderProps) => {
  alertType.value = apProps.alertType;
  title.value = apProps.title;
  alertList.value = apProps.alertList;
};

const resetAlertPlaceholder = () => {
  title.value = '';
  alertList.value = null;
  alertType.value = 'hidden';
};
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <TitleLayout>
        <template #title>
          {{ t(titleView) }}
        </template>
        <template #optional>
          <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
            <Button as="a" href="#" variant="primary" class="shadow-md" @click="backToList">
              <Lucide icon="ArrowLeft" class="w-4 h-4" />
              &nbsp;{{ t('components.buttons.back') }}
            </Button>
          </div>
        </template>
      </TitleLayout>

      <AlertPlaceholder
        :alert-type="alertType"
        :title="title"
        :alert-list="alertList"
        @dismiss="resetAlertPlaceholder"
      />
      <RouterView
        @loading-state="onLoadingStateChanged"
        @mode-state="onModeStateChanged"
        @show-alertplaceholder="onAlertPlaceholderTriggered"
      />
    </LoadingOverlay>
  </div>
</template>
