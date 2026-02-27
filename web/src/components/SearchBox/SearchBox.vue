<script setup lang="ts">
  import { ref } from 'vue';
  import Lucide from '@/components/Base/Lucide';
  import { FormInput } from '@/components/Base/Form';
  import { TransitionRoot } from '@headlessui/vue';
  import { useI18n } from 'vue-i18n';
  import LoadingIcon from '@/components/Base/LoadingIcon';

  const { t } = useI18n();

  const props = withDefaults(
    defineProps<{
      theme?: 'rubick' | 'icewall' | 'tinker' | 'enigma';
      layout?: 'side-menu' | 'simple-menu' | 'top-menu';
      visible?: boolean;
    }>(),
    {
      theme: 'rubick',
      layout: 'side-menu',
      visible: true,
    }
  );

  const searchDropdown = ref(false);

  const showSearchDropdown = () => {
    searchDropdown.value = true;
  };

  const hideSearchDropdown = () => {
    searchDropdown.value = false;
  };
</script>

<template>
  <div v-if="props.visible" class="relative mr-3 intro-x sm:mr-6">
    <div class="relative hidden sm:block">
      <FormInput
        type="text"
        class="border-transparent w-56 shadow-none rounded-full bg-slate-200 pr-8 transition-[width] duration-300 ease-in-out focus:border-transparent focus:w-72 dark:bg-darkmode-400"
        placeholder="Search..."
        @focus="showSearchDropdown"
        @blur="hideSearchDropdown"
      />
      <Lucide
        icon="Search"
        class="absolute inset-y-0 right-0 w-5 h-5 my-auto mr-3 text-slate-600 dark:text-slate-500"
      />
    </div>
    <a class="relative text-white/70 sm:hidden" href="">
      <Lucide icon="Search" class="w-5 h-5 dark:text-slate-500" />
    </a>
    <TransitionRoot
      as="template"
      :show="searchDropdown"
      enter="transition-all ease-linear duration-150"
      enterFrom="mt-5 invisible opacity-0 translate-y-1"
      enterTo="mt-[3px] visible opacity-100 translate-y-0"
      entered="mt-[3px]"
      leave="transition-all ease-linear duration-150"
      leaveFrom="mt-[3px] visible opacity-100 translate-y-0"
      leaveTo="mt-5 invisible opacity-0 translate-y-1"
    >
      <div class="absolute right-0 z-10 mt-[3px]">
        <div class="w-[450px] p-5 box">
          <div class="mb-2 font-medium">
            {{ t('components.search-box.search_group.user') }}
          </div>
          <div class="mb-5">
            <div class="flex items-center mt-2">
              <div class="ml-3">
                <LoadingIcon icon="puff" />
              </div>
            </div>
          </div>
          <div class="mb-2 font-medium">
            {{ t('components.search-box.search_group.company') }}
          </div>
          <div class="mb-5">
            <div class="flex items-center mt-2">
              <div class="ml-3">
                <LoadingIcon icon="puff" />
              </div>
            </div>
          </div>
          <div class="mb-2 font-medium">
            {{ t('components.search-box.search_group.branch') }}
          </div>
          <div class="mb-5">
            <div class="flex items-center mt-2">
              <div class="ml-3">
                <LoadingIcon icon="puff" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </TransitionRoot>
  </div>
</template>
