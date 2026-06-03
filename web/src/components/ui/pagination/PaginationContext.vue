<script setup lang="ts">
import type { Api } from "@zag-js/pagination";
import { inject } from "vue";

const api = inject<Api>("paginationApi");
</script>

<template>
  <slot :pagination="api"></slot>
</template>
