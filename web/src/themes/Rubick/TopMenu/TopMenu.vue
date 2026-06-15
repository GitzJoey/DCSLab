<script setup lang="ts">
import '@/assets/css/themes/rubick/top-menu.css'
import { useSideMenu } from '@/composables/useSideMenu'
import { useQuickSearch } from '@/composables/useQuickSearch'
import fakers from '@/utils/faker'
import { Lucide } from '@/components/ui/lucide'
import { Breadcrumb } from '@/components/ui/breadcrumb'
import { AvatarRoot, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import mainMenu from '@/main/menu'
import { TopMenu } from '@/components/top-menu'
import { AccountDropdown } from '@/components/account-dropdown'
import { NotificationDropdown } from '@/components/notification-dropdown'
import { QuickSearchDialog } from '@/components/quick-search-dialog'
import logo from '@/assets/images/logo.svg'

const { mobileMenuOpen, openMobileMenu, closeMobileMenu } = useSideMenu()

const { quickSearchDialogOpen } = useQuickSearch()
</script>

<template>
  <div
    :class="[
      'rubick min-h-screen dark:bg-background',
      'before:bg-primary dark:before:bg-foreground/1 before:fixed before:inset-0 before:bg-noise',
      'after:bg-accent after:bg-contain after:fixed after:inset-0 after:blur-xl dark:after:opacity-20',
      '[--color-nav-foreground:var(--color-background)] dark:[--color-nav-foreground:var(--color-foreground)]',
    ]"
  >
    <div
      :class="['border-(--color-nav-foreground)/20 relative z-30 flex h-16 items-center border-b']"
    >
      <a
        class="text-(--color-nav-foreground) border-(--color-nav-foreground)/20 hidden h-full items-center border-r px-7 xl:flex"
        href=""
      >
        <img class="size-7" :src="logo" />
        <div class="ml-3.5">
          <span class="text-base font-medium">DCSLab</span>
          <span class="text-base font-light"></span>
        </div>
      </a>
      <div class="flex h-full grow items-center gap-5 px-7">
        <div
          @click="openMobileMenu"
          class="open-mobile-menu border-(--color-nav-foreground)/30 mr-auto flex size-10 cursor-pointer items-center justify-center rounded-xl border xl:hidden"
        >
          <Lucide
            class="rotate-90 [--color:var(--color-nav-foreground)] size-4!"
            icon="ChartNoAxesColumn"
          />
        </div>
        <Breadcrumb
          :items="['Apps', 'Dashboards', 'Overview']"
          class="mr-auto hidden xl:flex [&_ol]:text-(--color-nav-foreground)/70 [&_li]:last:text-(--color-nav-foreground)/90 [&_li]:hover:text-(--color-nav-foreground)/90"
          light
        />
        <div
          class="quick-search-toggle bg-(--color-nav-foreground)/5 border-(--color-nav-foreground)/15 text-(--color-nav-foreground) hover:ring-foreground/5 flex h-9 cursor-pointer items-center rounded-full border px-4 ring-1 ring-transparent ring-offset-2 ring-offset-transparent outline-none"
          @click="quickSearchDialogOpen = true"
        >
          <div class="flex items-center gap-3 opacity-70">
            <Lucide icon="Search" class="size-4!" />
            ⌘K
          </div>
        </div>
        <div class="group/notifications relative flex h-9 items-center">
          <Lucide icon="Bell" class="[--color:var(--color-nav-foreground)] size-4!" />
          <NotificationDropdown
            class="absolute right-0 top-full mt-2 origin-top-right"
            boxClass="absolute right-0 top-0 -mr-0.5 -mt-0.5"
          />
        </div>
        <div class="group/profile relative size-9 flex-none">
          <AvatarRoot
            class="rounded-full ring-(--color)/40 size-full [--color:var(--color-nav-foreground)]"
          >
            <AvatarFallback>PA</AvatarFallback>
            <AvatarImage :src="fakers[0]!['photos'][2]" alt="avatar" />
          </AvatarRoot>
          <AccountDropdown
            class="absolute right-0 top-full mt-2 origin-top-right"
            boxClass="absolute right-0 top-0 -mr-0.5 -mt-0.5"
          />
        </div>
      </div>
    </div>
    <div class="mt-5 p-4">
      <div
        @click="closeMobileMenu"
        :class="[
          'close-mobile-menu fixed ml-68.75 top-0 xl:hidden z-60 cursor-pointer text-background dark:text-foreground hidden [&.close-mobile-menu--mobile-menu-open]:block',
          { 'close-mobile-menu--mobile-menu-open': mobileMenuOpen },
        ]"
      >
        <div class="ml-5 mt-5 flex size-10 items-center justify-center">
          <Lucide class="size-7 stroke-1" icon="X" />
        </div>
      </div>
      <TopMenu
        :menu="mainMenu"
        :class="[
          'top-menu transition-[margin] duration-200 w-68.75 xl:w-auto h-screen xl:h-auto z-50 xl:z-0 top-0 left-0 fixed xl:relative overflow-y-auto xl:overflow-y-visible -ml-68.75 xl:ml-0 [&.top-menu--mobile-menu-open]:ml-0',
          'before:content-[\'\'] before:fixed before:hidden before:inset-0 before:bg-black/80 dark:before:bg-foreground/5 before:backdrop-blur before:xl:hidden [&.top-menu--mobile-menu-open]:before:block',
          'after:content-[\'\'] after:transition-[margin] after:duration-200 after:-ml-68.75 after:fixed after:w-68.75 after:inset-0 after:bg-primary after:xl:hidden dark:after:bg-background after:bg-noise [&.top-menu--mobile-menu-open]:after:ml-0',
          { 'top-menu--mobile-menu-open': mobileMenuOpen },
        ]"
      />
      <div
        class="pt-8 pb-12 px-7 z-10 relative before:absolute before:inset-0 before:opacity-[.07] before:bg-foreground before:rounded-4xl after:absolute after:inset-0 after:bg-[color-mix(in_oklch,var(--color-background),var(--color-foreground)_2%)] after:rounded-4xl after:border after:border-foreground/15 dark:after:opacity-[.59]"
      >
        <div class="relative z-20">
          <RouterView />
        </div>
      </div>
    </div>
    <QuickSearchDialog v-model:open="quickSearchDialogOpen" />
  </div>
</template>
