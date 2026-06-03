<script lang="ts" setup>
import { type Api } from "@zag-js/menu";
import { cn } from "@midoneui/core/utils/cn";
import { inject } from "vue";
import { menuPositioner } from "@midoneui/core/styles/menu.styles";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("menuApi");
</script>

<template>
  <Teleport to="body">
    <Slot
      :class="cn(menuPositioner, className)"
      v-bind="{ ...props, ...$attrs, ...api?.getPositionerProps() }"
    >
      <slot v-if="asChild" />
      <div v-else>
        <slot />
      </div>
    </Slot>
  </Teleport>
</template>
