<script setup lang="ts">
import { inject, HTMLAttributes, computed } from "vue";
import { ProvidePreview } from "./Preview.vue";

interface PanelProps extends /* @vue-ignore */ HTMLAttributes {
  type?: "preview" | "source";
}

const { type } = withDefaults(defineProps<PanelProps>(), {
  type: "preview",
});

const preview = inject<ProvidePreview>(
  "preview",
  computed(() => true)
);
</script>

<template>
  <div v-if="(type == 'preview' && preview) || (type == 'source' && !preview)">
    <slot></slot>
  </div>
</template>
