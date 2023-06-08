<template>
  <div class="flex items-center mt-8 intro-y">
    <slot name="title"></slot>
  </div>

  <div class="grid grid-cols-12 gap-6 mt-5">
    <!-- Begin Side Menu -->
    <div
      class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse"
    >
      <div class="intro-y box mt-5 lg:mt-0">
        <slot name="side-menu-title"></slot>
        <div class="p-5 border-t border-slate-200/60 dark:border-darkmode-400">
          <template
            v-for="(link, index) in cards"
            :key="index"
          >
            <a class="flex items-center mt-5" :href="`#${index}`">
              <i data-lucide="activity" class="w-4 h-4 mr-2"></i>
              <slot name="side-menu-link" :link="link" />
            </a>
          </template>
        </div>
      </div>
    </div>
    <!-- End Side Menu -->

    <!-- Begin Card Menu -->
    <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
      <div class="grid grid-cols-12 gap-6">
        <div
          class="intro-y box col-span-12 2xl:col-span-12"
          v-for="(card, index) in cards"
        >
          <slot :name="`card-items-${index}`" :card="card" :index="index" />
        </div>
      </div>
    </div>
    <!-- End Card Menu -->
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  cards: { type: Array, default: [] },
});
</script>
