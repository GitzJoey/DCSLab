<script setup lang="ts">
import { ref } from "vue";
import Pagination from "../../base-components/Pagination";

const is_active_1 = ref<boolean>(false);
const is_active_2 = ref<boolean>(false);
</script>

<template>
    <Pagination>
        <Pagination.Link :active="is_active_1" @click="is_active_1 = !is_active_1">test 1</Pagination.Link>
        <Pagination.Link :active="is_active_2" @click="is_active_2 = !is_active_2">test 2</Pagination.Link>
    </Pagination>

    <button @click="is_active_1 = true">asdf</button>
</template>