<script setup lang="ts">
import Alert from "../Alert";
import Lucide from "../Lucide";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
</script>

<template>
    <div class="mt-8">
        <Alert variant="secondary" class="flex items-center mb-2">
            {{ t('components.alert-placeholder.email_verification.message') }}
            <Alert.DismissButton type="button" class="text-white" aria-label="Close">
                <Lucide icon="X" class="text-black w-4 h-4" />
            </Alert.DismissButton>
        </Alert>
    </div>
</template>