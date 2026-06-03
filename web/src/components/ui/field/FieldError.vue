<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { fieldError } from "@midoneui/core/styles/field.styles";
import { computed } from "vue";

const {
  class: className,
  errors,
  ...props
} = defineProps<{
  class?: string;
  errors?: Array<{ message?: string } | undefined>;
}>();

const uniqueErrors = computed(() => {
  const err = [
    ...new Map(errors?.map((error) => [error?.message, error])).values(),
  ];
  if (err?.length == 1) {
    return err[0]?.message;
  }
});
</script>

<template>
  <div
    role="alert"
    data-part="field-error"
    :class="cn(fieldError, className)"
    v-bind="{ ...props }"
  >
    <slot v-if="$slots.default" />
    <ul v-else>
      <li v-for="(error, index) in uniqueErrors" :key="index">{{ error }}</li>
    </ul>
  </div>
</template>
