<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import * as toast from "@zag-js/toast";
import { toasterContainer } from "@midoneui/core/styles/toast.styles";
import type { Store } from "@zag-js/toast";
import { normalizeProps, useMachine } from "@zag-js/vue";
import { computed } from "vue";
import { ToastItem } from ".";

const {
  class: className,
  asChild = false,
  toaster,
  ...props
} = defineProps<{ class?: string; asChild?: boolean; toaster: Store }>();

const serviceGroup = useMachine(toast.group.machine, {
  id: crypto.randomUUID(),
  store: toaster,
});
const apiGroup = computed(() =>
  toast.group.connect(serviceGroup, normalizeProps)
);
</script>

<template>
  <Teleport to="body">
    <div
      :class="cn(toasterContainer, className)"
      v-bind="{ ...apiGroup?.getGroupProps(), ...props, ...$attrs }"
    >
      <ToastItem
        v-for="(toastGroup, index) in apiGroup.getToasts()"
        :key="toastGroup.id"
        :index="index"
        :toastGroup="toastGroup"
        :serviceGroup="serviceGroup"
        v-slot="{ toast }"
      >
        <slot :toast="toast" />
      </ToastItem>
    </div>
  </Teleport>
</template>
