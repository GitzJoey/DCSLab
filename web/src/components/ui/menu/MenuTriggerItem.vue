<script lang="ts" setup>
import { type Api } from "@zag-js/menu";
import { inject } from "vue";
import { cn } from "@midoneui/core/utils/cn";
import { ChevronRight } from "lucide-vue-next";
import { Slot } from "@/components/ui/slot";
import { menuItem } from "@midoneui/core/styles/menu.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("menuApi");
</script>

<template>
  <Slot
    :class="cn(menuItem, className)"
    v-bind="{ ...api?.getTriggerItemProps(api), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <div><slot /></div>
      <ChevronRight data-part="nested-menu-chevron" />
    </div>
  </Slot>
</template>
