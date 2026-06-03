<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { sheetContent } from "@midoneui/core/styles/sheet.styles";
import { Box } from "@/components/ui/box";
import { SheetBackdrop, SheetPositioner } from "@/components/ui/sheet";
import type { Api } from "@zag-js/dialog";
import { inject } from "vue";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  asChild = false,
  side = "right",
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
  side?: "top" | "right" | "bottom" | "left";
}>();

const api = inject<Api>("sheetApi");
</script>

<template>
  <Teleport to="body">
    <SheetBackdrop />
    <SheetPositioner>
      <Slot v-bind="{ ...props, ...$attrs, ...api?.getContentProps() }">
        <slot v-if="asChild" />
        <div v-else>
          <Box
            raised="double"
            :data-side="side"
            :class="cn(sheetContent, className)"
            v-bind="{ ...props }"
          >
            <div><slot /></div>
          </Box>
        </div>
      </Slot>
    </SheetPositioner>
  </Teleport>
</template>
