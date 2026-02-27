<script setup lang="ts">
import HomeLogo from "@/components/HomeLogo/HomeLogo.vue";
import UserLocation from "@/components/UserLocation/UserLocation.vue";
import SearchBox from "@/components/SearchBox/SearchBox.vue";
import SidebarPop from "@/components/SidebarPop/SidebarPop.vue";
import LanguageSwitcher from "@/components/LanguageSwitcher/LanguageSwitcher.vue";
import ProfileMenu from "@/components/ProfileMenu/ProfileMenu.vue";

const props = defineProps<{
  layout?: "side-menu" | "simple-menu" | "top-menu";
}>();
</script>

<template>
  <div
    :class="[
      'h-[70px] md:h-[65px] z-[51] border-b border-white/[0.08] mt-12 md:mt-0 -mx-3 sm:-mx-8 md:-mx-0 px-3 md:border-b-0 relative md:fixed md:inset-x-0 md:top-0 sm:px-8 md:px-10 md:pt-10 md:bg-gradient-to-b md:from-slate-100 md:to-transparent dark:md:from-darkmode-700',
      props.layout == 'top-menu' && 'dark:md:from-darkmode-800',
      'before:content-[\'\'] before:absolute before:h-[65px] before:inset-0 before:top-0 before:mx-7 before:bg-primary/30 before:mt-3 before:rounded-xl before:hidden before:md:block before:dark:bg-darkmode-600/30',
      'after:content-[\'\'] after:absolute after:inset-0 after:h-[65px] after:mx-3 after:bg-primary after:mt-5 after:rounded-xl after:shadow-md after:hidden after:md:block after:dark:bg-darkmode-600',
    ]"
  >
    <div class="flex items-center h-full">
      <HomeLogo theme="enigma" :layout="props.layout" />
      <UserLocation theme="enigma" :layout="props.layout" />
      <SidebarPop theme="enigma" :layout="props.layout" />
      <LanguageSwitcher theme="enigma" :layout="props.layout" />
      <ProfileMenu theme="enigma" :layout="props.layout" />
    </div>
  </div>
</template>
