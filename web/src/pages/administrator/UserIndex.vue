<script setup lang="ts">
// #region Imports
import { ref } from "vue";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import { TitleLayout } from "../../base-components/Form/FormLayout";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import { ViewMode } from "../../types/enums/ViewMode";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
// #endregion

// #region Props, Emits
// #endregion

// #region Refs
const mode = ref<ViewMode>(ViewMode.INDEX);
const loading = ref<boolean>(false);
const titleView = ref<string>('views.user.page_title');
// #endregion

// #region Computed
// #endregion

// #region Lifecycle Hooks
// #endregion

// #region Methods
const createNew = () => {
    mode.value = ViewMode.FORM_CREATE;
    router.push({ name: 'side-menu-administrator-user-create' });
}

const backToList = async () => {
    mode.value = ViewMode.LIST;

    router.push({ name: 'side-menu-administrator-user-list' });
}

const onLoadingStateChanged = (state: boolean) => {
    loading.value = state;
}

const onModeStateChanged = (state: ViewMode) => {
    mode.value = state;

    switch (state) {
        case ViewMode.FORM_CREATE:
            titleView.value = 'views.user.actions.create';
            break;
        case ViewMode.FORM_EDIT:
            titleView.value = 'views.user.actions.edit';
            break;
        case ViewMode.INDEX:
        case ViewMode.LIST:
        default:
            titleView.value = 'views.user.page_title';
            break;
    }
}
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
                        <Button v-if="mode == ViewMode.LIST" as="a" href="#" variant="primary" class="shadow-md"
                            @click="createNew">
                            <Lucide icon="Plus" class="w-4 h-4" />&nbsp;{{ t("components.buttons.create_new") }}
                        </Button>
                        <Button v-else as="a" href="#" variant="primary" class="shadow-md" @click="backToList">
                            <Lucide icon="ArrowLeft" class="w-4 h-4" />&nbsp;{{ t("components.buttons.back") }}
                        </Button>
                    </div>
                </template>
            </TitleLayout>

            <RouterView @loading-state="onLoadingStateChanged" @mode-state="onModeStateChanged" />
        </LoadingOverlay>
    </div>
</template>