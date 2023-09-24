<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { TitleLayout } from '../../base-components/Form/FormLayout';

const route = useRoute();
const errorCode = ref<number>(0);

onMounted(() => {
    errorCode.value = parseInt(route.params.code as string);
})
</script>

<template>
    <div class="mt-8">
        <TitleLayout>
            <template #title>
                Error
            </template>
        </TitleLayout>

        <div v-if="errorCode == 10000">
            User Location not found
        </div>
    </div>
</template>