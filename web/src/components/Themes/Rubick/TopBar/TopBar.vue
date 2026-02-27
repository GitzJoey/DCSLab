<script setup lang="ts">
import UserLocation from "@/components/UserLocation/UserLocation.vue";
import SearchBox from "@/components/SearchBox/SearchBox.vue";
import SidebarPop from "@/components/SidebarPop/SidebarPop.vue";
import LanguageSwitcher from "@/components/LanguageSwitcher/LanguageSwitcher.vue";
import ProfileMenu from "@/components/ProfileMenu/ProfileMenu.vue";
</script>

<template>
  <div class="relative z-[51] flex h-[67px] items-center border-b border-slate-200">
    <UserLocation theme="rubick" />
    <SearchBox theme="rubick" />
    <SidebarPop theme="rubick" />
    <LanguageSwitcher theme="rubick" />
    <ProfileMenu theme="rubick" />
  </div>
</template>
