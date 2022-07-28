<template>
    <div>Loading...</div>
</template>

<script setup>
import { onMounted } from "vue";
import router from "@/router";

onMounted(() => {
  router.push({ name: 'login' });
});
</script>