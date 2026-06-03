<script setup lang="ts">
import type { Api } from "@zag-js/date-picker";
import { inject } from "vue";

const api = inject<Api>("datepickerApi");
</script>

<template>
  <slot :datePicker="api"></slot>
</template>
