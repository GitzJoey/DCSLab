<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { dialogContent } from "@midoneui/core/styles/dialog.styles";
import { Box } from "@/components/ui/box";
import { DialogBackdrop, DialogPositioner } from "@/components/ui/dialog";
import type { Api } from "@zag-js/dialog";
import { inject } from "vue";
import { Slot } from "@/components/ui/slot";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("dialogApi");
</script>

<template>
  <Teleport to="body">
    <DialogBackdrop />
    <DialogPositioner>
      <Slot v-bind="{ ...props, ...$attrs, ...api?.getContentProps() }">
        <slot v-if="asChild" />
        <div v-else>
          <Box
            raised="double"
            :class="cn(dialogContent, className)"
            v-bind="{ ...props }"
          >
            <div><slot /></div>
          </Box>
        </div>
      </Slot>
    </DialogPositioner>
  </Teleport>
</template>
