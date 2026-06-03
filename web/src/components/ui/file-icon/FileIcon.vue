<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";

const {
  class: className,
  variant,
  type,
  src,
  ...props
} = defineProps<{
  class?: string;
  variant?: "empty-directory" | "directory" | "file" | "image";
  type?: string;
  src?: string;
}>();
</script>

<template>
  <div :class="cn(className)" v-bind="{ ...props, ...$attrs }">
    <div
      :class="
        cn(
          'relative block bg-center bg-no-repeat bg-contain',
          'before:content-[\'\'] before:pt-[100%] before:w-full before:block',
          variant === 'empty-directory' && 'bg-empty-directory',
          variant === 'directory' && 'bg-directory',
          variant === 'file' && 'bg-file',
        )
      "
    >
      <div
        v-if="variant === 'file'"
        class="absolute bottom-0 left-0 right-0 top-0 m-auto flex items-center justify-center text-white"
      >
        {{ type }}
      </div>
      <div v-else-if="variant === 'image'" class="image-fit absolute left-0 top-0 size-full">
        <img class="rounded-lg" :src="src" alt="Midone - Admin Dashboard Template" />
      </div>
    </div>
  </div>
</template>
