<script setup lang="ts">
import { toRef } from "vue";
import Lucide from "../../base-components/Lucide";

const props = defineProps({
  visible: { type: Boolean, default: true },
});

const visible = toRef(props, 'visible');

const goTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<template>
  <div v-if="visible"
    class="fixed bottom-0 right-0 z-50 flex items-center justify-center w-auto h-auto mb-5 mr-5 p-2 border rounded-full shadow-md cursor-pointer box"
    @click="goTop">
    <Lucide icon="ArrowUp" class="w-4 h-4" />
  </div>
</template>
