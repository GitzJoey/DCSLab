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
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const titleView = ref<string>(t('views.company.page_title'));
// #endregion

// #region Computed
// #endregion

// #region Lifecycle Hooks
// #endregion

// #region Methods
const createNew = () => {
    mode.value = ViewMode.FORM_CREATE;
    router.push({ name: 'side-menu-company-company-create' });
}

const backToList = async () => {
    mode.value = ViewMode.LIST;

    router.push({ name: 'side-menu-company-company-list' });
}

const onLoadingStateChanged = (state: boolean) => {
    loading.value = state;
}

const onModeStateChanged = (state: ViewMode) => {
    mode.value = state;

    switch (state) {
        case ViewMode.FORM_CREATE:
            titleView.value = t('views.company.actions.create');
            break;
        case ViewMode.FORM_EDIT:
            titleView.value = t('views.company.actions.edit');
            break;
        case ViewMode.LIST:
        default:
            titleView.value = t('views.company.page_title');
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
                    {{ titleView }}
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

            <router-view @loading-state="onLoadingStateChanged" @mode-state="onModeStateChanged" />
        </LoadingOverlay>
    </div>
</template>