<!-- TwoColumnLayout -->
<template>
    <div class="grid grid-cols-12 gap-6 mt-5">
      <!-- Begin Side Menu -->
  
      <div
        v-if="isShowSideTab"
        class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse transition ease-in duration-100"
      >
        <div class="intro-y box mt-5 lg:mt-0">
          <div class="relative flex items-center p-5">
            <div class="ml-4 mr-auto flex items-center justify-between w-full">
              <div class="font-medium text-base">
                <slot name="side-menu-title"></slot>
              </div>
  
              <div
                :class="[
                  'transition ease-in duration-100 ml-auto mr-5 hidden xl:block cursor-pointer',
                ]"
                @click="isShowSideTab = false"
              >
                <Lucide class="w-4 h-4" icon="ChevronsLeft" />
              </div>
            </div>
          </div>
          <div class="p-5 border-t border-slate-200/60 dark:border-darkmode-400">
            <template v-for="(link, index) in cards" :key="index">
              <a class="flex items-center mt-5" :href="`#${index}`">
                <i data-lucide="activity" class="w-4 h-4 mr-2"></i>
                <slot name="side-menu-link" :link="link" />
                <div
                  :class="[
                    'transition ease-in duration-100 ml-auto mr-5 hidden xl:block',
                    { 'transform rotate-180': link.active },
                  ]"
                  @click="$emit('handleExpandCard', index)"
                >
                  <Lucide v-if="link.active" class="w-4 h-4" icon="Minus" />
                  <Lucide v-else class="w-4 h-4" icon="Plus" />
                </div>
              </a>
            </template>
          </div>
        </div>
      </div>
      <div class="" v-else>
        <div
          :class="[
            'transition ease-in duration-100 ml-auto mr-5 hidden xl:block cursor-pointer',
            // { 'transform rotate-180': link.active },
          ]"
          @click="isShowSideTab = true"
        >
          <Lucide class="w-4 h-4" icon="ChevronsRight" />
        </div>
      </div>
      <!-- End Side Menu -->
  
      <!-- Begin Card Menu -->
      <div :class="['col-span-12' ,'lg:col-span-8',,  `${isShowSideTab?  '2xl:col-span-9' : '2xl:col-span-12'}`]">
        <Transition>
          <div class="grid grid-cols-12 gap-6">
            <div
              class="intro-y box col-span-12 2xl:col-span-12"
              v-for="(card, index) in cards"
            >
              <div
                class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400"
                :id="`${index}`"
              >
                <h2 class="font-medium text-base mr-auto">{{ card.title }}</h2>
  
                <div
                  :class="[
                    'transition ease-in duration-100 ml-auto mr-5 hidden xl:block cursor-pointer',
                    { 'transform rotate-180': card.active },
                  ]"
                  @click="$emit('handleExpandCard', index)"
                >
                  <Lucide class="w-4 h-4" icon="ChevronDown" />
                </div>
              </div>
              <div :class="[{ block: card.active }, { hidden: !card.active }]">
                <slot :name="`card-items-${index}`" :card="card" :index="index" />
              </div>
            </div>
          </div>
        </Transition>
      </div>
      <!-- End Card Menu -->
    </div>
  </template>
  
  <script setup lang="ts">
  import { PropType, ref } from "vue";
  import Lucide from "../../base-components/Lucide";
  
  type Cards = {
    title: string;
    active: boolean;
  };
  
  const props = defineProps({
    cards: { type: Array as PropType<Cards[]>, default: [] },
  });
  
  const emit = defineEmits(['handleExpandCard'])
  
  const isShowSideTab = ref(true);
  </script>
  