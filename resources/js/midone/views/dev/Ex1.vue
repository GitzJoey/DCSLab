<template>
</template>

<script setup>
import {inject, onMounted, ref} from "vue";

onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);
});
</script>
