<template>
</template>

<script setup>
import { inject, onMounted, ref } from "vue";
import axios from '../../axios';

onMounted(() => {
    axios.get('/api/get/dashboard/devtool/test').then(response => {
        console.log(response.data);
    });
});
</script>
