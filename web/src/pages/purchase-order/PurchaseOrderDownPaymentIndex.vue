<script setup lang="ts">
import { provide, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { ViewMode } from '@/types/enums/ViewMode';
import { TitleLayout } from '@/components/Base/Form/FormLayout';
import LoadingOverlay from '@/components/LoadingOverlay';
import Lucide from '@/components/Base/Lucide';
import Notification from '@/components/Base/Notification/Notification.vue';
import AlertPlaceholder from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { NotificationData } from '@/types/models/NotificationData';
import type { NotificationElement } from '@/components/Base/Notification/Notification.vue';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import PurchaseOrderDownPaymentList from './PurchaseOrderDownPaymentList.vue';

const { t } = useI18n();

const loading = ref(false);
const titleView = ref<string>('views.purchase_order_down_payment.page_title');
const alertType = ref<'danger' | 'success' | 'warning' | 'pending' | 'dark' | 'hidden'>('hidden');
const title = ref('');
const alertList = ref<Record<string, Array<string>> | null>(null);
const notificationTitle = ref('');
const notificationContent = ref('');
const purchaseOrderDownPaymentNotification = ref<NotificationElement>();

provide('bind[purchaseOrderDownPaymentNotification]', (el: NotificationElement) => {
  purchaseOrderDownPaymentNotification.value = el;
});

const onLoadingStateChanged = (state: boolean) => {
  loading.value = state;
};

const onModeStateChanged = (state: ViewMode) => {
  if (state === ViewMode.LIST) {
    titleView.value = 'views.purchase_order_down_payment.page_title';
  }
};

const onAlertPlaceholderTriggered = (apProps: AlertPlaceholderProps) => {
  alertType.value = apProps.alertType;
  title.value = apProps.title;
  alertList.value = apProps.alertList;
};

const onShowNotificationTriggered = (notification: NotificationData) => {
  notificationTitle.value = notification.title;
  notificationContent.value = notification.content;

  if (purchaseOrderDownPaymentNotification.value) {
    purchaseOrderDownPaymentNotification.value.showToast();
  }
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
        <template #title>{{ t(titleView) }}</template>
      </TitleLayout>

      <AlertPlaceholder :alert-type="alertType" :title="title" :alert-list="alertList" @dismiss="resetAlertPlaceholder" />

      <PurchaseOrderDownPaymentList
        @loading-state="onLoadingStateChanged"
        @mode-state="onModeStateChanged"
        @show-alertplaceholder="onAlertPlaceholderTriggered"
        @show-notification="onShowNotificationTriggered"
      />
    </LoadingOverlay>

    <Notification ref-key="purchaseOrderDownPaymentNotification" :options="{ duration: 3000 }" class="flex">
      <Lucide icon="CheckCircle" class="text-success" />
      <div class="ml-4 mr-4">
        <div class="font-medium">{{ notificationTitle }}</div>
        <div class="mt-1 text-slate-500">{{ notificationContent }}</div>
      </div>
    </Notification>
  </div>
</template>
