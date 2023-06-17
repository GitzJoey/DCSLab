<script setup lang="ts">
import { onMounted, ref, toRef } from "vue";
import Lucide from "../../Lucide";

export interface TwoColumnsLayoutCards {
  title: string;
  active: boolean;
};

export interface TwoColumnsLayoutProps {
  cards: Array<TwoColumnsLayoutCards>,
  showSideTab?: boolean,
}

const props = withDefaults(defineProps<TwoColumnsLayoutProps>(), {
  cards: (): Array<TwoColumnsLayoutCards> => [],
  showSideTab: false,
});

const showSideTab = toRef(props, 'showSideTab');
const isShowSideTab = ref<boolean>(false);

const emits = defineEmits<{
  (e: 'handleExpandCard', index: number): void,
}>();

onMounted(() => {
  isShowSideTab.value = showSideTab.value;
});

const onLinkClicked = (index: number): void => {
  emits('handleExpandCard', index);
}

const onCardTitleClicked = (index: number): void => {
  emits('handleExpandCard', index);
}

const toggleSideTab = (show: boolean | undefined) => {
  if (show != undefined) {
    isShowSideTab.value = show;
  } else {
    isShowSideTab.value = !isShowSideTab.value;
  }
}
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div v-if="isShowSideTab"
      class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse transition ease-in duration-100">
      <div class="intro-y box mt-5 lg:mt-0">
        <div class="relative flex items-center p-5">
          <div class="ml-4 mr-auto flex items-center justify-between w-full">
            <div class="font-medium text-base">
              <slot name="side-menu-title"></slot>
            </div>
            <div class="transition ease-in duration-100 ml-auto mr-5 hidden xl:block cursor-pointer"
              @click="toggleSideTab(false)">
              <Lucide class="w-4 h-4" icon="ChevronsLeft" />
            </div>
          </div>
        </div>
        <div class="p-5 border-t border-slate-200/60 dark:border-darkmode-400">
          <template v-for="(link, index) in cards" :key="index">
            <a class="flex items-center mt-5" :href="`#${index}`">
              <Lucide icon="CircleDot" class="w-4 h-4 mr-2" />
              <slot name="side-menu-link" :link="link"></slot>
              <div
                :class="{ 'transition ease-in duration-100 ml-auto mr-5 hidden xl:block': true, 'transform rotate-180': link.active }"
                @click="onLinkClicked(index)">
                <Lucide v-if="link.active" class="w-4 h-4" icon="Minus" />
                <Lucide v-else class="w-4 h-4" icon="Plus" />
              </div>
            </a>
          </template>
        </div>
      </div>
    </div>
    <div v-else>
      <div class="transition ease-in duration-100 ml-auto mr-5 hidden xl:block cursor-pointer"
        @click="toggleSideTab(true)">
        <Lucide class="w-4 h-4" icon="ChevronsRight" />
      </div>
    </div>

    <div :class="['col-span-12', 'lg:col-span-8', `${isShowSideTab ? '2xl:col-span-9' : '2xl:col-span-12'}`]">
      <Transition>
        <div class="grid grid-cols-12 gap-6">
          <div v-for="(card, index) in cards" :key="index" class="intro-y box col-span-12 2xl:col-span-12">
            <div :id="`${index}`"
              class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400">
              <h2 class="font-medium text-base mr-auto">{{ card.title }}</h2>
              <div
                :class="{ 'transition ease-in duration-100 ml-auto mr-5 hidden xl:block cursor-pointer': true, 'transform rotate-180': card.active }"
                @click="onCardTitleClicked(index)">
                <Lucide class="w-4 h-4" icon="ChevronDown" />
              </div>
            </div>
            <div :class="[{ 'block': card.active }, { 'hidden': !card.active }]">
              <slot :name="`card-items-${index}`" :card="card" :index="index"></slot>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>