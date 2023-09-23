<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const errorCode = ref<number>(0);

onMounted(() => {
    errorCode.value = route.params.code;
})
</script>

<template>
    <div class="mt-8">
        <div>test</div>
    </div>
</template>