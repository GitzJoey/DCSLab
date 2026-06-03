<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { datePickerPresetTrigger } from "@midoneui/core/styles/datepicker.styles";
import { Button } from "@/components/ui/button";
import type { Api, PresetTriggerProps } from "@zag-js/date-picker";
import { inject } from "vue";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<
  PresetTriggerProps & {
    class?: string;
    asChild?: boolean;
  }
>();

const api = inject<Api>("datepickerApi");
</script>

<template>
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getPresetTriggerProps(props) }">
    <Button
      variant="ghost"
      v-if="!asChild"
      :class="cn(datePickerPresetTrigger, className)"
    >
      <slot />
    </Button>
    <slot v-else />
  </Slot>
</template>
