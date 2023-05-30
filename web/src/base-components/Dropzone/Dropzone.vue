<script setup lang="ts">
import { HTMLAttributes, ref, inject, onMounted } from "vue";
import DropzoneJs, { DropzoneOptions } from "dropzone";
import { init } from "./dropzone";

export type ProvideDropzone = (el: DropzoneElement) => void;

export interface DropzoneElement extends HTMLDivElement {
  dropzone: DropzoneJs;
}

interface DropzoneProps extends /* @vue-ignore */ HTMLAttributes {
  options: DropzoneOptions;
  refKey?: string;
}

const props = defineProps<DropzoneProps>();

const fileUploadRef = ref<DropzoneElement>();

const bindInstance = (el: DropzoneElement) => {
  if (props.refKey) {
    const bind = inject<ProvideDropzone>(`bind[${props.refKey}]`);
    if (bind) {
      bind(el);
    }
  }
};

const vFileUploadDirective = {
  mounted(el: DropzoneElement) {
    init(el, props);
  },
};

onMounted(() => {
  if (fileUploadRef.value) {
    bindInstance(fileUploadRef.value);
  }
});
</script>

<template>
  <div
    ref="fileUploadRef"
    v-file-upload-directive
    class="[&.dropzone]:border-2 [&.dropzone]:border-dashed dropzone [&.dropzone]:border-darkmode-200/60 [&.dropzone]:dark:bg-darkmode-600 [&.dropzone]:dark:border-white/5"
  >
    <div class="dz-message">
      <slot></slot>
    </div>
  </div>
</template>
