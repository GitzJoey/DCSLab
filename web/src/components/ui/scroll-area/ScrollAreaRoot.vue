<script lang="ts" setup>
import * as scrollArea from "@zag-js/scroll-area";
import type { Props } from "@zag-js/scroll-area";
import { useMachine, normalizeProps } from "@zag-js/vue";
import { cn } from "@midoneui/core/utils/cn";
import { computed, provide } from "vue";
import { scrollAreaRoot } from "@midoneui/core/styles/scroll-area.styles";

const { class: className, ...props } = defineProps<
  Partial<Props> & {
    class?: string;
  }
>();

const service = useMachine(scrollArea.machine, {
  ...props,
  id: crypto.randomUUID(),
});

const api = computed(() => scrollArea.connect(service, normalizeProps));

provide("scrollAreaApi", api);
</script>

<template>
  <div
    v-bind="{ ...api.getRootProps() }"
    :class="cn(scrollAreaRoot, className)"
  >
    <slot />
  </div>
</template>
