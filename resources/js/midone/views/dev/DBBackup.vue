<template>

</template>

<script setup>
import {inject, onMounted} from "vue";

onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);
});
</script>
