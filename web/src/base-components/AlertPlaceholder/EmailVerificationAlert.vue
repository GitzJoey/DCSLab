<script setup lang="ts">
import { computed } from "vue";
import Alert from "../Alert";
import Lucide from "../Lucide";
import { useI18n } from "vue-i18n";
import { useUserContextStore } from "../../stores/user-context";

const { t } = useI18n();
const userContextStore = useUserContextStore();

const userContext = computed(() => userContextStore.getUserContext);
const userContextIsAuth = computed(() => userContextStore.getIsAuthenticated);
const userContextIsLoaded = computed(() => userContextStore.getIsLoaded);
</script>

<template>
    <div v-if="userContextIsLoaded && userContextIsAuth && userContext.email_verified == false" class="mt-8">
        <Alert variant="secondary" class="flex items-center mb-2" v-slot="{ dismiss }">
            {{ t('components.alert-placeholder.email_verification.message') }}
            <Alert.DismissButton type="button" class="text-white" aria-label="Close" @click="dismiss">
                <Lucide icon="X" class="text-black w-4 h-4" />
            </Alert.DismissButton>
        </Alert>
    </div>
</template>