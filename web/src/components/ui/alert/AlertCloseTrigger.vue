<script lang="ts" setup>
import { X } from "lucide-vue-next";
import { cn } from "@midoneui/core/utils/cn";
import { Slot } from "@/components/ui/slot";
import { alertCloseTrigger } from "@midoneui/core/styles/alert.styles";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const context = inject<{
  present: boolean;
  setPresent: (value: boolean) => void;
} | null>("alertPresent", null);
</script>

<template>
  <Slot
    :class="cn([className, alertCloseTrigger])"
    v-bind="{ ...props, ...$attrs }"
    @click="context?.setPresent(false)"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot v-if="$slots.default" />
      <X v-else />
    </div>
  </Slot>
</template>
