<template>
</template>

<script setup>
import {inject, onMounted, ref} from "vue";
import axios from '../../axios';

onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);

    axios.get('/api/get/dashboard/devtool/test').then(response => {
        console.log(response.data);
    });
});
</script>
