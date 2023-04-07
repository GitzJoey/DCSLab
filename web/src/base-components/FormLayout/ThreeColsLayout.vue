<script setup lang="ts">
import { ref } from "vue";

const show = ref(true);
</script>

<template>
  <div>
    <div class="grid grid-cols-12">
      <div class="hidden block md:block lg:block md:col-span-3 lg:col-span-3">
        <div class="flex flex-1 flex-col justify-between">
          <span class="text-lg cursor-pointer" @click="show = !show">
            <slot name="title"></slot>
          </span>
        </div>
      </div>
      <div
        class="col-span-12 md:col-span-9 lg:col-span-9"
        :class="{ hidden: show }"
      >
        <slot name="content"></slot>
      </div>
    </div>
    <hr class="py-2" />
  </div>
</template>
