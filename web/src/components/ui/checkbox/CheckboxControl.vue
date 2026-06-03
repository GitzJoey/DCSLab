<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { CheckIcon } from "lucide-vue-next";
import { cn } from "@midoneui/core/utils/cn";
import { checkboxControl } from "@midoneui/core/styles/checkbox.styles";
import { CheckboxIndicator } from ".";
import type { Api } from "@zag-js/checkbox";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  asChild?: boolean;
  class?: string;
}>();

const api = inject<Api>("checkboxApi");
</script>

<template>
  <Slot
    :class="cn(checkboxControl, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getControlProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <CheckboxIndicator>
        <CheckIcon />
      </CheckboxIndicator>
    </div>
  </Slot>
</template>
