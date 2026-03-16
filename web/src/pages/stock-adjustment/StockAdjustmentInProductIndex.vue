<script setup lang="ts">
  import { ref, provide } from 'vue';
  import { useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import { ViewMode } from '@/types/enums/ViewMode';
  import { useUserContextStore } from '@/stores/user-context';
  import { TitleLayout } from '@/components/Base/Form/FormLayout';
  import LoadingOverlay from '@/components/LoadingOverlay';
  import Lucide from '@/components/Base/Lucide';
  import Notification from '@/components/Base/Notification/Notification.vue';
  import AlertPlaceholder from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
  import ProfileService from '@/services/ProfileService';
  import { UserProfile } from '@/types/models/UserProfile';
  import { NotificationData } from '@/types/models/NotificationData';
  import { type NotificationElement } from '@/components/Base/Notification/Notification.vue';
  import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
  import StockAdjustmentInProductList from './StockAdjustmentInProductList.vue';

  const { t } = useI18n();
  const router = useRouter();

  const userContextStore = useUserContextStore();
  const profileService = new ProfileService();

  const mode = ref<ViewMode>(ViewMode.LIST);
  const loading = ref<boolean>(false);
  const titleView = ref<string>('views.stock_adjustment_in_product.page_title');

  const alertType = ref<'danger' | 'success' | 'warning' | 'pending' | 'dark' | 'hidden'>('hidden');
  const title = ref<string>('');
  const alertList = ref<Record<string, Array<string>> | null>(null);

  const stockAdjustmentInProductNotification = ref<NotificationElement>();
  const notificationTitle = ref<string>('');
  const notificationContent = ref<string>('');

  provide('bind[stockAdjustmentInProductNotification]', (el: NotificationElement) => {
    stockAdjustmentInProductNotification.value = el;
  });

  const onLoadingStateChanged = (state: boolean) => {
    loading.value = state;
  };

  const onModeStateChanged = (state: ViewMode) => {
    mode.value = state;

    switch (state) {
      case ViewMode.LIST:
      default:
        titleView.value = 'views.stock_adjustment_in_product.page_title';
        break;
    }
  };

  const onUpdateProfileTriggered = async () => {
    const userprofile = await profileService.readProfile();
    if (userprofile.success) {
      userContextStore.setUserContext(userprofile.data as UserProfile);
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

    if (stockAdjustmentInProductNotification.value) {
      stockAdjustmentInProductNotification.value.showToast();
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
        <template #title>
          {{ t(titleView) }}
        </template>
        <template #optional>
          <!-- No optional buttons for now -->
        </template>
      </TitleLayout>

      <AlertPlaceholder
        :alert-type="alertType"
        :title="title"
        :alert-list="alertList"
        @dismiss="resetAlertPlaceholder"
      />

      <StockAdjustmentInProductList
        @loading-state="onLoadingStateChanged"
        @mode-state="onModeStateChanged"
        @update-profile="onUpdateProfileTriggered"
        @show-alertplaceholder="onAlertPlaceholderTriggered"
        @show-notification="onShowNotificationTriggered"
      />
    </LoadingOverlay>

    <Notification ref-key="stockAdjustmentInProductNotification" :options="{ duration: 3000 }" class="flex">
      <Lucide icon="CheckCircle" class="text-success" />
      <div class="ml-4 mr-4">
        <div class="font-medium">{{ notificationTitle }}</div>
        <div class="mt-1 text-slate-500">
          {{ notificationContent }}
        </div>
      </div>
    </Notification>
  </div>
</template>
