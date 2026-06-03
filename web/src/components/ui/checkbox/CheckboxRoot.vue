<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { checkboxRoot } from "@midoneui/core/styles/checkbox.styles";
import * as checkbox from "@zag-js/checkbox";
import { useMachine, normalizeProps } from "@zag-js/vue";
import type { Props } from "@zag-js/checkbox";
import { CheckboxHiddenInput } from ".";
import { computed, provide } from "vue";

const {
  class: className,
  checked = undefined,
  ...props
} = defineProps<Partial<Props> & { class?: string }>();

const service = useMachine(
  checkbox.machine,
  computed(() => ({
    ...props,
    checked,
    id: crypto.randomUUID(),
  }))
);

const api = computed(() => checkbox.connect(service, normalizeProps));

provide("checkboxApi", api);
</script>

<template>
  <label
    :class="cn(checkboxRoot, className)"
    v-bind="{ ...props, ...$attrs, ...api.getRootProps() }"
  >
    <slot />
    <CheckboxHiddenInput />
  </label>
</template>
