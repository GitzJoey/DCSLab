<script setup lang="ts">
import { onMounted, ref, computed } from "vue";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import { TitleLayout } from "../../base-components/Form/FormLayout";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import { ViewMode } from "../../types/enums/ViewMode";

const { t } = useI18n();
const router = useRouter();

const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const titleView = computed((): string => { return t('views.user.page_title'); });

//#region onMounted
onMounted(() => {
    router.push({ name: 'side-menu-administrator-user-list' });
});
//#endregion

const createNew = () => {
    router.push({ name: 'side-menu-administrator-user-create' });
}

const backToList = async () => {
    //cacheServices.removeLastEntity('User');

    //router.push({ name: 'side-menu-administrator-user' });
}

const onLoadingStateChanged = (state: boolean) => {

}

const onTitleViewChanged = (title: boolean) => {

}
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
                        <Button v-if="mode == ViewMode.FORM_CREATE || mode == ViewMode.FORM_EDIT" as="a" href="#"
                            variant="primary" class="shadow-md" @click="createNew">
                            <Lucide icon="Plus" class="w-4 h-4" />&nbsp;{{ t("components.buttons.create_new") }}
                        </Button>
                        <Button v-else as="a" href="#" variant="primary" class="shadow-md" @click="backToList">
                            <Lucide icon="ArrowLeft" class="w-4 h-4" />&nbsp;{{ t("components.buttons.back") }}
                        </Button>
                    </div>
                </template>
            </TitleLayout>
        </LoadingOverlay>

        <router-view @loading-state="onLoadingStateChanged" @title-view="onTitleViewChanged" />
    </div>
</template>