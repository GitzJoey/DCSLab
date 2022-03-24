<template>
</template>

<script setup>
//#region Imports
import { onMounted, ref } from "vue";
import axios from "@/axios";
//#endregion

//#region onMounted
onMounted(() => {
    axios.get('/api/get/dashboard/devtool/test').then(response => {
        console.log(response.data);
    });
});
//#endregion
</script>
