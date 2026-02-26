<script setup lang="ts">
// #region Imports
import { ref, provide } from "vue";
import LoadingOverlay from "@/components/LoadingOverlay";
import { TitleLayout } from "@/components/Base/Form/FormLayout";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import Button from "@/components/Base/Button";
import Lucide from "@/components/Base/Lucide";
import { ViewMode } from "@/types/enums/ViewMode";
import CacheService from "@/services/CacheService";
import AlertPlaceholder from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
import Notification from "@/components/Base/Notification/Notification.vue";
import { NotificationData } from "@/types/models/NotificationData";
import { type NotificationElement } from "@/components/Base/Notification/Notification.vue";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();

const cacheService = new CacheService();
// #endregion

// #region Props, Emits
// #endregion

// #region Refs
const mode = ref<ViewMode>(ViewMode.INDEX);
const loading = ref<boolean>(false);
const titleView = ref<string>("views.user.page_title");

const alertType = ref<
  "danger" | "success" | "warning" | "pending" | "dark" | "hidden"
>("hidden");
const title = ref<string>("");
const alertList = ref<Record<string, Array<string>> | null>(null);

const userNotification = ref<NotificationElement>();

const notificationTitle = ref<string>("");
const notificationContent = ref<string>("");
// #endregion

// #region Provide/Inject
provide("bind[userNotification]", (el: NotificationElement) => {
  userNotification.value = el;
});
// #endregion

// #region Computed
// #endregion

// #region Lifecycle Hooks
// #endregion

// #region Methods
const createNew = () => {
  resetAlertPlaceholder();
  mode.value = ViewMode.FORM_CREATE;
  router.push({ name: "side-menu-administrator-user-create" });
};

const backToList = async () => {
  resetAlertPlaceholder();
  clearCache(mode.value);
  mode.value = ViewMode.LIST;
  router.push({ name: "side-menu-administrator-user-list" });
};

const onLoadingStateChanged = (state: boolean) => {
  loading.value = state;
};

const onModeStateChanged = (state: ViewMode) => {
  mode.value = state;

  switch (state) {
    case ViewMode.FORM_CREATE:
      titleView.value = "views.user.actions.create";
      break;
    case ViewMode.FORM_EDIT:
      titleView.value = "views.user.actions.edit";
      break;
    case ViewMode.INDEX:
    case ViewMode.LIST:
    default:
      titleView.value = "views.user.page_title";
      break;
  }
};

const clearCache = (mode: ViewMode) => {
  switch (mode) {
    case ViewMode.FORM_CREATE:
      cacheService.removeLastEntity("USER_CREATE");
      break;
    case ViewMode.FORM_EDIT:
      cacheService.removeLastEntity("USER_EDIT");
      break;
    default:
      break;
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

  if (userNotification.value) userNotification.value.showToast();
};

const resetAlertPlaceholder = () => {
  title.value = "";
  alertList.value = null;
  alertType.value = "hidden";
};
// #endregion

// #region Watchers
// #endregion
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
            <Button
              v-if="mode == ViewMode.LIST"
              as="a"
              href="#"
              variant="primary"
              class="shadow-md"
              @click="createNew"
            >
              <Lucide icon="Plus" class="w-4 h-4" />&nbsp;{{
                t("components.buttons.create_new")
              }}
            </Button>
            <Button
              v-else
              as="a"
              href="#"
              variant="primary"
              class="shadow-md"
              @click="backToList"
            >
              <Lucide icon="ArrowLeft" class="w-4 h-4" />&nbsp;{{
                t("components.buttons.back")
              }}
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
        @show-notification="onShowNotificationTriggered"
      />
    </LoadingOverlay>
    <Notification
      ref-key="userNotification"
      :options="{ duration: 3000 }"
      class="flex"
    >
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
