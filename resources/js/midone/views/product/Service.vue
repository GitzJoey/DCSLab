<template>

</template>

<script setup>
// Vue Import
import { inject, onMounted, ref, computed, watch } from "vue";
// Helper Import
import axios from "@/axios";
import { useI18n } from "vue-i18n";
import { route } from "@/ziggy";
// Core Components Import
import { useUserContextStore } from "@/stores/user-context";
// Components Import
import DataList from "@/global-components/data-list/Main";
import AlertPlaceholder from "@/global-components/alert-placeholder/Main";

// Declarations
const { t } = useI18n();

// Data - Pinia
const userContextStore = useUserContextStore();
const selectedUserCompany = computed(() => userContextStore.selectedUserCompany );

// Data - UI
const mode = ref('list');
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref('');
const expandDetail = ref(null);
// Data - Views

// onMounted
onMounted(() => {

});
// Methods
// Computed
// Watcher
</script>