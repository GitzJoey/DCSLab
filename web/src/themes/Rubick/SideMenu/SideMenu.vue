<script setup lang="ts">
import '@/assets/css/themes/rubick/side-menu.css'
import logo from '@/assets/images/logo.svg'
import { useSideMenu } from '@/composables/useSideMenu'
import { useQuickSearch } from '@/composables/useQuickSearch'
import fakers from '@/utils/faker'
import { Lucide } from '@/components/ui/lucide'
import { Breadcrumb } from '@/components/ui/breadcrumb'
import { AvatarRoot, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import mainMenu from '@/main/side-menu'
import { SideMenu } from '@/components/side-menu'
import { AccountDropdown, AccountTrigger } from '@/components/account-dropdown'
import { NotificationDropdown } from '@/components/notification-dropdown'
import { QuickSearchDialog } from '@/components/quick-search-dialog'
import {
  ScrollAreaRoot,
  ScrollAreaViewport,
  ScrollAreaContent,
  ScrollAreaScrollbar,
  ScrollAreaThumb,
  ScrollAreaCorner,
} from '@/components/ui/scroll-area'

const {
  compactMenu,
  compactMenuOnHover,
  mobileMenuOpen,
  scrolled,
  toggleCompactMenu,
  onMouseEnterSideMenu,
  onMouseLeaveSideMenu,
  openMobileMenu,
  closeMobileMenu,
  onScrollContent,
} = useSideMenu()

const { quickSearchDialogOpen } = useQuickSearch()
</script>

<template>
  <div
    :class="[
      'rubick',
      'min-h-screen dark:bg-background',
      'before:bg-primary dark:before:bg-foreground/1 before:fixed before:inset-0 before:bg-noise',
      'after:bg-accent after:bg-contain after:fixed after:inset-0 after:blur-xl dark:after:opacity-20',
    ]"
  >
    <div
      @mouseenter="onMouseEnterSideMenu"
      @mouseleave="onMouseLeaveSideMenu"
      :class="[
        'side-menu text-background dark:text-foreground xl:ml-0 transition-[margin] duration-100 fixed top-0 left-0 z-50 group',
        'before:content-[\'\'] before:fixed before:inset-0 before:bg-black/80 dark:before:bg-foreground/5 before:backdrop-blur before:xl:hidden',
        'after:content-[\'\'] after:absolute after:inset-0 after:bg-primary after:xl:hidden dark:after:bg-background after:bg-noise',
        '[&.side-menu--mobile-menu-open]:ml-0 [&.side-menu--mobile-menu-open]:before:block',
        '-ml-68.75 before:hidden',
        { 'side-menu--mobile-menu-open': mobileMenuOpen },
        { 'side-menu--collapsed': compactMenu },
        { 'side-menu--on-hover': compactMenuOnHover },
      ]"
    >
      <div
        @click="closeMobileMenu"
        :class="[
          'close-mobile-menu fixed ml-68.75 xl:hidden z-50 cursor-pointer hidden',
          '[&.close-mobile-menu--mobile-menu-open]:block',
          { 'close-mobile-menu--mobile-menu-open': mobileMenuOpen },
        ]"
      >
        <div class="ml-5 mt-5 flex size-10 items-center justify-center">
          <Lucide class="size-7 stroke-1" icon="X" />
        </div>
      </div>
      <div
        :class="[
          'side-menu__content',
          'z-20 pt-5 pb-30 relative w-68.75 duration-100 transition-[width] group-[.side-menu--collapsed]:xl:w-27.5 group-[.side-menu--collapsed.side-menu--on-hover]:xl:w-68.75 h-screen flex flex-col',
        ]"
      >
        <div
          class="relative z-10 hidden h-16.25 w-68.75 flex-none items-center overflow-hidden px-6 duration-100 xl:flex group-[.side-menu--collapsed.side-menu--on-hover]:xl:w-68.75 group-[.side-menu--collapsed]:xl:w-27.5"
        >
          <a
            class="flex items-center transition-[margin] duration-100 xl:ml-2 group-[.side-menu--collapsed.side-menu--on-hover]:xl:ml-2 group-[.side-menu--collapsed]:xl:ml-6"
            href=""
          >
            <img class="size-7" :src="logo" />
            <div
              class="ml-3.5 text-nowrap transition-opacity group-[.side-menu--collapsed.side-menu--on-hover]:xl:opacity-100 group-[.side-menu--collapsed]:xl:opacity-0"
            >
              <span class="text-base font-medium">DCSLab</span>
              <span class="text-base font-light"></span>
            </div>
          </a>
          <a
            @click="toggleCompactMenu"
            class="toggle-compact-menu border-background/20 bg-background/10 dark:bg-foreground/2 dark:border-foreground/9 ml-auto hidden items-center justify-center rounded-md border py-0.5 pl-0.5 pr-1 opacity-70 transition-[opacity,transform] hover:opacity-100 group-[.side-menu--collapsed]:xl:rotate-180 group-[.side-menu--collapsed.side-menu--on-hover]:xl:opacity-100 group-[.side-menu--collapsed]:xl:opacity-0 2xl:flex"
            href=""
          >
            <Lucide icon="ChevronLeft" />
          </a>
        </div>
        <ScrollAreaRoot class="flex-1 min-h-0">
          <ScrollAreaViewport
            :class="
              [
                'px-4 pb-3',
                '[-webkit-mask-image:linear-gradient(to_top,rgba(0,0,0,0),black_30px),linear-gradient(to_bottom,rgba(0,0,0,0),black_30px)]',
                '[-webkit-mask-composite:destination-in]',
              ].join(' ')
            "
          >
            <ScrollAreaContent class="min-w-auto!">
              <SideMenu :menu="mainMenu" />
            </ScrollAreaContent>
          </ScrollAreaViewport>
          <ScrollAreaScrollbar class="bg-transparent">
            <ScrollAreaThumb class="bg-background/10" />
          </ScrollAreaScrollbar>
          <ScrollAreaCorner />
        </ScrollAreaRoot>
        <AccountTrigger
          class="absolute inset-x-0 bottom-0 mx-4 mb-8 group-[.side-menu--collapsed.side-menu--on-hover]:block group-[.side-menu--collapsed]:justify-center xl:group-[.side-menu--collapsed]:flex"
          innerClass="bg-background/10 border-background/20 dark:bg-foreground/[.02] dark:border-foreground/[.09] rounded-full border p-2.5 opacity-80 backdrop-blur-2xl hover:opacity-100"
        >
          <AccountDropdown
            class="absolute bottom-0 left-full origin-bottom-left"
            boxClass="absolute bottom-0 left-[100%] ml-2"
          />
        </AccountTrigger>
      </div>
    </div>
    <div
      :class="[
        'content h-screen transition-[margin,width] duration-100 pt-8 pb-12 px-7 z-10 relative',
        'before:absolute before:inset-y-4 before:-ml-px before:right-4 before:opacity-[.07] before:left-4 xl:before:left-0 before:bg-foreground before:rounded-4xl',
        'after:absolute after:inset-y-4 after:-ml-px after:right-4 after:left-4 xl:after:left-0 after:bg-[color-mix(in_oklch,var(--color-background),var(--color-foreground)_2%)] after:rounded-4xl after:border after:border-foreground/15 dark:after:opacity-[.59]',
        'xl:ml-68.75',
        '[&.content--compact]:xl:ml-27.5',
        { 'content--compact': compactMenu && !compactMenuOnHover },
      ]"
    >
      <div class="h-full overflow-x-hidden">
        <div
          @scroll="onScrollContent"
          :class="[
            'content__scroll-area relative z-20 -mr-7 h-full overflow-y-auto pl-4 pr-11 pb-5 transition-[margin] duration-100 xl:pl-0',
            { '-ml-41.25': compactMenu && compactMenuOnHover && !mobileMenuOpen },
          ]"
        >
          <div
            :class="[
              'top-bar group z-50 relative -mt-2 [&.scrolled]:sticky [&.scrolled]:inset-x-0 [&.scrolled]:top-0 [&.scrolled]:z-999 [&.scrolled]:mt-0',
              { scrolled: scrolled },
            ]"
          >
            <div
              :class="[
                'flex h-16 items-center gap-5 border-b border-foreground/15 transition-all',
                'group-[.scrolled]:px-5 group-[.scrolled]:rounded-2xl group-[.scrolled]:bg-background group-[.scrolled]:border group-[.scrolled]:shadow-lg group-[.scrolled]:shadow-foreground/5',
              ]"
            >
              <div
                @click="openMobileMenu"
                class="open-mobile-menu bg-background mr-auto flex size-9 cursor-pointer items-center justify-center rounded-xl border border-foreground/15 xl:hidden"
              >
                <Lucide class="rotate-90" icon="ChartNoAxesColumn" />
              </div>
              <Breadcrumb
                class="mr-auto hidden xl:flex"
                :items="['Apps', 'Dashboards', 'Overview']"
              />
              <div
                class="quick-search-toggle bg-background hover:ring-foreground/5 flex h-9 cursor-pointer items-center rounded-full border border-foreground/15 px-4 ring-1 ring-transparent ring-offset-2 ring-offset-transparent outline-none"
                @click="quickSearchDialogOpen = true"
              >
                <div class="items-center gap-3 opacity-70 flex">
                  <Lucide icon="Search" />
                  ⌘K
                </div>
              </div>
              <div class="group/notifications relative flex h-9 items-center">
                <Lucide icon="Bell" />
                <NotificationDropdown
                  class="absolute right-0 top-full mt-2 origin-top-right"
                  boxClass="absolute right-0 top-0 -mr-0.5 -mt-0.5"
                />
              </div>
              <div class="group/profile relative size-9 flex-none">
                <AvatarRoot class="size-full rounded-full">
                  <AvatarFallback>PA</AvatarFallback>
                  <AvatarImage :src="fakers[0]!['photos'][2]" alt="avatar" />
                </AvatarRoot>
                <AccountDropdown
                  class="absolute right-0 top-full mt-2 origin-top-right"
                  boxClass="absolute right-0 top-0 -mr-0.5 -mt-0.5"
                />
              </div>
            </div>
            <QuickSearchDialog v-model:open="quickSearchDialogOpen" />
          </div>
          <div class="pt-5">
            <RouterView />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
