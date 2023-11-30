<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { TitleLayout } from '../../base-components/Form/FormLayout';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const route = useRoute();
const errorCode = ref<number>(0);

onMounted(() => {
    errorCode.value = parseInt(route.params.code as string);
})
</script>

<template>
    <div class="mt-8">
        <TitleLayout>
            <template #title>
                {{ t('views.error.title') }}
            </template>
        </TitleLayout>

        <div v-if="errorCode == 401">
            {{ t('views.error.code.401') }}
        </div>
        <div v-else-if="errorCode == 403">
            {{ t('views.error.code.403') }}
        </div>
        <div v-else-if="errorCode == 500">
            {{ t('views.error.code.500') }}
        </div>
        <div v-else-if="errorCode == 500">
            {{ t('views.error.code.500') }}
        </div>
        <div v-else-if="errorCode == 10000">
            {{ t('views.error.code.10000') }}
        </div>
        <div v-else>

        </div>
    </div>
</template>