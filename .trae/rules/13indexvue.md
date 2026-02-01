---
alwaysApply: false
description: Standarisasi penulisan halaman Index/Wrapper (EntityIndex.vue) di Frontend
---
# Standarisasi Halaman Index (EntityIndex.vue)

Dokumen ini menjelaskan standar penulisan halaman `[Entity]Index.vue` di frontend (`web/src/pages/[entity]/[Entity]Index.vue`). Halaman Index berfungsi sebagai **Wrapper** atau **Parent Layout** yang membungkus navigasi antara List, Create, dan Edit menggunakan `RouterView`.

Halaman ini menangani:
1.  Global Alert Placeholder
2.  Notification Toast
3.  Loading Overlay
4.  Cache Clearing Strategy (saat navigasi)

## 1. Imports

Gunakan komponen layout standar dan store yang diperlukan.

```typescript
import { ref, provide } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { ViewMode } from "@/types/enums/ViewMode";
import CacheService from "@/services/CacheService";
import { useUserContextStore } from "@/stores/user-context";
import { TitleLayout } from "@/components/Base/Form/FormLayout";
import LoadingOverlay from "@/components/LoadingOverlay";
import Button from "@/components/Base/Button";
import Lucide from "@/components/Base/Lucide";
import Notification from "@/components/Base/Notification/Notification.vue";
import AlertPlaceholder from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
// ... Imports Type & Service Lainnya
```

## 2. State Management (Refs)

Definisikan state untuk mengontrol UI global seperti loading, alert, dan notifikasi.

```typescript
const mode = ref<ViewMode>(ViewMode.INDEX);
const loading = ref<boolean>(false);
const titleView = ref<string>('views.entity.page_title'); // Default title key

// Alert State
const alertType = ref<'danger'|'success'|'warning'|'pending'|'dark'|'hidden'>('hidden');
const title = ref<string>('');
const alertList = ref<Record<string, Array<string>> | null>(null);

// Notification State
const entityNotification = ref<NotificationElement>();
const notificationTitle = ref<string>('');
const notificationContent = ref<string>('');
```

## 3. Navigation Methods (Cache & Alert Clearing)

Saat berpindah halaman (terutama "Back to List"), sangat PENTING untuk:
1.  **Reset Alert Placeholder**: Agar pesan error dari form tidak terbawa ke List.
2.  **Clear Cache**: Menghapus draft form (Create/Edit) agar saat user kembali nanti, form dalam keadaan bersih (kecuali memang fitur draft diinginkan persisten).

```typescript
const createNew = () => {
    resetAlertPlaceholder();
    mode.value = ViewMode.FORM_CREATE;
    router.push({ name: 'entity-create-route' });
};

const backToList = async () => {
    resetAlertPlaceholder(); // 1. Bersihkan Alert
    clearCache(mode.value);  // 2. Bersihkan Cache Form
    mode.value = ViewMode.LIST;
    router.push({ name: 'entity-list-route' });
};

const clearCache = (mode: ViewMode) => {
    switch (mode) {
        case ViewMode.FORM_CREATE:
            cacheService.removeLastEntity('ENTITY_CREATE');
            break;
        case ViewMode.FORM_EDIT:
            cacheService.removeLastEntity('ENTITY_EDIT');
            break;
        default:
            break;
    }
};

const resetAlertPlaceholder = () => {
    title.value = '';
    alertList.value = null;
    alertType.value = 'hidden';
};
```

## 4. Event Handlers

Handler untuk event yang di-emit oleh child components (List/Create/Edit).

```typescript
const onLoadingStateChanged = (state: boolean) => {
    loading.value = state;
};

const onModeStateChanged = (state: ViewMode) => {
    mode.value = state;
    // Update Title based on Mode
    switch (state) {
        case ViewMode.FORM_CREATE:
            titleView.value = 'views.entity.actions.create';
            break;
        case ViewMode.FORM_EDIT:
            titleView.value = 'views.entity.actions.edit';
            break;
        default:
            titleView.value = 'views.entity.page_title';
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

    if (entityNotification.value)
        entityNotification.value.showToast();
};
```

## 5. Template Structure

Gunakan `LoadingOverlay` sebagai root wrapper, diikuti `TitleLayout` untuk header, dan `RouterView` untuk konten dinamis.

```html
<template>
    <div class="mt-8">
        <LoadingOverlay :visible="loading">
            <TitleLayout>
                <template #title>
                    {{ t(titleView) }}
                </template>
                <template #optional>
                    <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
                        <!-- Create Button (List Mode) -->
                        <Button v-if="mode == ViewMode.LIST" as="a" href="#" variant="primary" class="shadow-md"
                            @click="createNew">
                            <Lucide icon="Plus" class="w-4 h-4" />&nbsp;{{ t("components.buttons.create_new") }}
                        </Button>
                        
                        <!-- Back Button (Form Mode) -->
                        <Button v-else as="a" href="#" variant="primary" class="shadow-md" @click="backToList">
                            <Lucide icon="ArrowLeft" class="w-4 h-4" />&nbsp;{{ t("components.buttons.back") }}
                        </Button>
                    </div>
                </template>
            </TitleLayout>

            <!-- Global Alert Placeholder -->
            <AlertPlaceholder :alert-type="alertType" :title="title" :alert-list="alertList" @dismiss="resetAlertPlaceholder" />
            
            <!-- Dynamic Content (List/Create/Edit) -->
            <RouterView 
                @loading-state="onLoadingStateChanged" 
                @mode-state="onModeStateChanged" 
                @update-profile="onUpdateProfileTriggered" 
                @show-alertplaceholder="onAlertPlaceholderTriggered" 
                @show-notification="onShowNotificationTriggered" 
            />
        </LoadingOverlay>

        <!-- Global Notification Toast -->
        <Notification ref-key="entityNotification" :options="{ duration: 3000, }" class="flex">
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
```
