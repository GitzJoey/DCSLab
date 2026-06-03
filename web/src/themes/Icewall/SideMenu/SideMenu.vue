<script setup lang="ts">
import '@/assets/css/themes/icewall/side-menu.css'
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
} from "@/components/ui/scroll-area";

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
      'icewall',
      'min-h-screen dark:bg-background',
      'before:bg-primary dark:before:bg-foreground/[.01] before:fixed before:inset-0 before:bg-noise',
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
        '-ml-[300px] before:hidden',
        { 'side-menu--mobile-menu-open': mobileMenuOpen },
        { 'side-menu--collapsed': compactMenu },
        { 'side-menu--on-hover': compactMenuOnHover },
      ]"
    >
      <div
        @click="closeMobileMenu"
        :class="[
          'close-mobile-menu fixed ml-[300px] xl:hidden z-50 cursor-pointer hidden',
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
          'z-20 relative w-[300px] duration-100 transition-[width] group-[.side-menu--collapsed]:xl:w-[150px] group-[.side-menu--collapsed.side-menu--on-hover]:xl:w-[300px] h-screen flex flex-col-reverse xl:flex-col',
          'before:absolute before:inset-y-0 before:w-px before:bg-background/20 dark:before:bg-foreground/10 before:right-0 before:mr-8 before:hidden xl:before:block',
        ]"
      >
        <div
          class="relative z-10 hidden h-[70px] w-[300px] flex-none items-center overflow-hidden pl-5 pr-14 duration-100 xl:flex group-[.side-menu--collapsed.side-menu--on-hover]:xl:w-[300px] group-[.side-menu--collapsed]:xl:w-[150px]"
        >
          <a
            class="flex items-center transition-[margin] duration-100 xl:ml-1 group-[.side-menu--collapsed.side-menu--on-hover]:xl:ml-1 group-[.side-menu--collapsed]:xl:ml-7"
            href=""
          >
            <img class="size-5" :src="logo" />
            <div
              class="ml-3.5 text-nowrap transition-opacity group-[.side-menu--collapsed.side-menu--on-hover]:xl:opacity-100 group-[.side-menu--collapsed]:xl:opacity-0"
            >
              <span class="text-base font-medium">Midone </span>
              <span class="text-base font-light">Icewall</span>
            </div>
          </a>
          <a
            @click="toggleCompactMenu"
            class="toggle-compact-menu border-background/20 bg-background/10 dark:bg-foreground/[.02] dark:border-foreground/[.09] ml-auto hidden items-center justify-center rounded-md border py-0.5 pl-0.5 pr-1 opacity-70 transition-[opacity,transform] hover:opacity-100 group-[.side-menu--collapsed]:xl:rotate-180 group-[.side-menu--collapsed.side-menu--on-hover]:xl:opacity-100 group-[.side-menu--collapsed]:xl:opacity-0 2xl:flex"
            href=""
          >
            <Lucide icon="ChevronLeft" />
          </a>
        </div>
        <AccountTrigger
          class="relative transition-[width] xl:mb-2 xl:mr-8"
          innerClass="border-background/20 dark:border-foreground/[.09] border-t px-5 py-3.5 opacity-80 hover:opacity-100 xl:border-b"
        >
          <AccountDropdown
            class="absolute top-0 left-[100%] origin-bottom-left"
            boxClass="absolute bottom-0 left-[100%] ml-2 xl:bottom-auto xl:top-0"
          />
        </AccountTrigger>
        <ScrollAreaRoot class="flex-1 min-h-0">
          <ScrollAreaViewport :class="[
            'pl-4 pr-4 xl:pr-14 pb-3',
            '[-webkit-mask-image:_linear-gradient(to_top,_rgba(0,_0,_0,_0),_black_30px),_linear-gradient(to_bottom,_rgba(0,_0,_0,_0),_black_30px)]',
            '[-webkit-mask-composite:_destination-in]',
          ].join(' ')">
            <ScrollAreaContent class="!min-w-auto">
              <SideMenu :menu="mainMenu" />
            </ScrollAreaContent>
          </ScrollAreaViewport>
          <ScrollAreaScrollbar class="bg-transparent xl:data-[orientation=vertical]:mr-9">
            <ScrollAreaThumb class="bg-background/10" />
          </ScrollAreaScrollbar>
          <ScrollAreaCorner />
        </ScrollAreaRoot>
      </div>
    </div>
    <div
      :class="[
        'content h-screen transition-[margin,width] duration-100 pt-26 pb-8 px-7 z-10 relative group',
        'before:absolute before:bottom-4 before:top-22 before:-ml-px before:right-4 before:opacity-[.07] before:left-4 xl:before:left-0 before:bg-foreground before:rounded-4xl',
        'after:absolute after:bottom-4 after:top-22 after:-ml-px after:right-4 after:left-4 xl:after:left-0 after:bg-[color-mix(in_oklch,_var(--color-background),_var(--color-foreground)_2%)] after:rounded-4xl after:border after:border-foreground/[.15] dark:after:bg-[color-mix(in_oklch,_var(--color-background),_var(--color-foreground)_10%)]',
        'xl:ml-[300px]',
        '[&.content--compact]:xl:ml-[150px]',
        { 'content--compact': compactMenu && !compactMenuOnHover },
      ]"
    >
      <div
        :class="[
          'relative h-full',
          'after:absolute after:bottom-4 after:mx-2 after:-mt-8 after:top-0 after:right-4 after:left-4 xl:after:left-0 after:bg-background/30 after:rounded-4xl after:border after:border-foreground/[.15] dark:after:opacity-[.59]',
          '[--color-nav-foreground:var(--color-background)] dark:[--color-nav-foreground:var(--color-foreground)]',
        ]"
      >
        <div class="h-full overflow-x-hidden">
          <div
            @scroll="onScrollContent"
            :class="[
              'content__scroll-area relative z-20 -mr-7 h-full overflow-y-auto pl-4 pr-11 pb-5 pt-2 transition-[margin] duration-100 xl:pl-0',
              { '-ml-[150px]': compactMenu && compactMenuOnHover && !mobileMenuOpen },
            ]"
          >
            <div
              :class="[
                'group fixed inset-x-0 top-0 z-20 px-6 transition-[margin,width] duration-100 xl:ml-[300px] xl:mr-5 group-[.content--compact]:xl:ml-[150px]',
                { scrolled: scrolled },
              ]"
            >
              <div :class="['flex h-[70px] items-center gap-5']">
                <div
                  @click="openMobileMenu"
                  class="open-mobile-menu bg-(--color-nav-foreground)/10 border-(--color-nav-foreground)/30 mr-auto flex size-9 cursor-pointer items-center justify-center rounded-xl border xl:hidden"
                >
                  <Lucide
                    class="rotate-90 [--color:var(--color-nav-foreground)]"
                    icon="ChartNoAxesColumn"
                  />
                </div>
                <Breadcrumb
                  class="mr-auto hidden xl:flex [&_ol]:text-(--color-nav-foreground)/70 [&_li]:last:text-(--color-nav-foreground)/90 [&_li]:hover:text-(--color-nav-foreground)/90"
                  :items="['Apps', 'Dashboards', 'Overview']"
                />
                <div
                  class="quick-search-toggle bg-(--color-nav-foreground)/5 border-(--color-nav-foreground)/15 text-(--color-nav-foreground) hover:ring-foreground/5 flex h-9 cursor-pointer items-center rounded-full border px-4 ring-1 ring-transparent ring-offset-2 ring-offset-transparent outline-none"
                  @click="quickSearchDialogOpen = true"
                >
                  <div class="items-center gap-3 opacity-70 flex">
                    <Lucide icon="Search" />
                    ⌘K
                  </div>
                </div>
                <div class="group/notifications relative flex h-9 items-center">
                  <Lucide class="[--color:var(--color-nav-foreground)]" icon="Bell" />
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
                    <AvatarImage :src="fakers[0]!['photos'][0]" alt="avatar" />
                  </AvatarRoot>
                  <AccountDropdown
                    class="absolute right-0 top-full mt-2 origin-top-right"
                    boxClass="absolute right-0 top-0 -mr-0.5 -mt-0.5"
                  />
                </div>
              </div>
              <!-- BEGIN: Quick Search Modal -->
              <QuickSearchDialog v-model:open="quickSearchDialogOpen" />
              <!-- END: Quick Search Modal -->
            </div>
            <RouterView />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
