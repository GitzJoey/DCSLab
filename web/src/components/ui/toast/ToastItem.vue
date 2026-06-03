<script lang="ts" setup>
import * as toast from "@zag-js/toast";
import { provide, computed } from "vue";
import { useMachine, normalizeProps } from "@zag-js/vue";

const { toastGroup, serviceGroup, index } = defineProps<{
  class?: string;
  asChild?: boolean;
  toastGroup: toast.Options;
  serviceGroup: toast.GroupService;
  index: number;
}>();

const composedProps = computed(() => ({
  ...toastGroup,
  index,
  parent: serviceGroup,
}));
const service = useMachine(toast.machine, composedProps);
const api = toast.connect(service, normalizeProps);

provide("toastApi", api);
</script>

<template>
  <slot
    :toast="{
      ...api,
      id: toastGroup.id,
    }"
  />
</template>
