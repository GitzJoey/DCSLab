<script setup lang="ts">
import { onMounted, ref, toRef, computed } from "vue";
import Lucide from "../../Lucide";
import { useI18n } from "vue-i18n";
import { ViewState } from '../../../types/enums/ViewMode'

export interface TwoColumnsLayoutCards {
  id ?: string | number,
  title: string;
  state: ViewState ;
};


export interface TwoColumnsLayoutProps {
  cards: Array<TwoColumnsLayoutCards>,
  showSideTab?: boolean,
  usingSideTab?  : boolean
}

const { t } = useI18n();

const props = withDefaults(defineProps<TwoColumnsLayoutProps>(), {
  cards: (): Array<TwoColumnsLayoutCards> => [],
  showSideTab: false,
  usingSideTab : false
});

const showSideTab = toRef(props, 'showSideTab');
const usingSideTab = toRef(props, 'usingSideTab')
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
    <div v-if="isShowSideTab && usingSideTab"
      class="col-span-12 lg:col-span-4 2xl:col-span-4 flex lg:block flex-col-reverse transition ease-in duration-100">
      <div class="intro-y box mt-5 lg:mt-0">
        <div class="relative flex items-center p-5">
          <div class="ml-4 mr-auto flex items-center justify-between w-full">
            <div class="font-medium text-base">
              <slot name="side-menu-title"></slot>
            </div>
            <div class="transition ease-in duration-100 ml-auto mr-5 xl:block lg:block cursor-pointer"
              @click="toggleSideTab(false)">
              <Lucide class="w-4 h-4" icon="ChevronsLeft" />
            </div>
          </div>
        </div>
        <div class="p-5 border-t border-slate-200/60 dark:border-darkmode-400">
          <template v-for="(link, index) in cards" :key="index">
            <a class="flex items-center mt-5" :href="`#${index}`">
              <Lucide icon="CircleDot" class="w-4 h-4 mr-2" />
              {{ link.title }}
              <div
                :class="{ 'transition ease-in duration-100 ml-auto mr-5 hidden xl:block': true, 'transform rotate-180': link.state }"
                @click="onLinkClicked(index)">
                <Lucide v-if="link.state" class="w-4 h-4" icon="Minus" />
                <Lucide v-else class="w-4 h-4" icon="Plus" />
              </div>
            </a>
          </template>
        </div>
      </div>
    </div>
    <div v-else-if="usingSideTab">
      <div class="transition ease-in duration-100 ml-auto mr-5 xl:block lg:block cursor-pointer"
        @click="toggleSideTab(true)">
        <Lucide class="w-4 h-4" icon="ChevronsRight" />
      </div>
    </div>

    <div :class="[
      `${isShowSideTab && usingSideTab && 'col-span-12 lg:col-span-8 xl:col-span-8'}`,
      `${!usingSideTab && 'col-span-12'}`,
      `${usingSideTab && !isShowSideTab && 'col-span-11'}`
    ]">
      <Transition>
        <div class="grid grid-cols-12 gap-6">
          <div v-for="(card, index) in cards" :key="index" :class="['intro-y', {'box' : card.state !== ViewState.hide} , 'col-span-12' , '2xl:col-span-12']">
            <div v-if="card.title" :id="`${index}`" class="cursor-pointer flex px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400" @click="onCardTitleClicked(index)">
              <div class="w-1/2 flex justify-start">
                <h2 class="font-medium text-base mr-auto">{{ t(card.title) }}</h2>
              </div>
              <div v-if="card.state !== ViewState.hide" class="w-1/2 flex justify-end">
                <div
                  :class="{ 'transition ease-in duration-100 ml-auto hidden xl:block cursor-pointer': true, 'transform rotate-180': card.state === ViewState.expand }"
                  @click="onCardTitleClicked(index)">
                  <Lucide class="w-6 h-6" icon="ChevronDown" />
                </div>
              </div>
            </div>
            <div :class="[{ 'block': card.state === ViewState.expand }, { 'hidden': card.state === ViewState.collapse }]">
              <slot :name="`card-items-${card.id ? card.id : index}`" :card="card" :index="index"></slot>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>