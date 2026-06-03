<script setup lang="ts">
import '@/assets/css/themes/enigma/side-menu.css'
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
      'enigma',
      'min-h-screen bg-[color-mix(in_oklch,_var(--color-background),_var(--color-foreground)_3%)] dark:bg-background',
      'before:bg-noise dark:before:bg-foreground/[.01] before:fixed before:inset-0 before:opacity-20',
      'after:bg-accent after:bg-contain after:fixed after:inset-0 after:blur-xl after:opacity-[.25]',
    ]"
  >
    <div
      @mouseenter="onMouseEnterSideMenu"
      @mouseleave="onMouseLeaveSideMenu"
      :class="[
        'side-menu xl:ml-0 transition-[margin] duration-100 fixed top-0 left-0 z-50 group',
        'before:content-[\'\'] before:fixed before:inset-0 before:bg-black/80 dark:before:bg-foreground/5 before:backdrop-blur before:xl:hidden',
        'after:content-[\'\'] after:absolute after:inset-0 after:bg-background after:xl:hidden',
        '-ml-[320px] before:hidden',
        { 'side-menu--mobile-menu-open ml-0 before:block': mobileMenuOpen },
        { 'side-menu--collapsed': compactMenu },
        { 'side-menu--on-hover': compactMenuOnHover },
      ]"
    >
      <div
        @click="closeMobileMenu"
        :class="[
          'close-mobile-menu fixed ml-[320px] xl:hidden z-50 cursor-pointer text-background dark:text-foreground hidden',
          { 'close-mobile-menu--mobile-menu-open block': mobileMenuOpen },
        ]"
      >
        <div class="ml-5 mt-5 flex size-10 items-center justify-center">
          <Lucide class="size-7 stroke-1" icon="X" />
        </div>
      </div>
      <div
        :class="[
          'side-menu__content',
          'z-20 relative w-[320px] duration-100 transition-[width] group-[.side-menu--collapsed]:xl:w-[165px] group-[.side-menu--collapsed.side-menu--on-hover]:xl:w-[320px] h-screen flex flex-col-reverse xl:flex-col',
          'before:absolute before:inset-y-0 before:w-px before:mt-23 before:bg-foreground/[.11] dark:before:bg-foreground/10 before:right-0 before:mr-8 before:hidden xl:before:block',
        ]"
      >
        <div
          class="relative z-10 mt-3 hidden h-[90px] w-[320px] flex-none items-center overflow-hidden pl-8 pr-14 duration-100 xl:flex group-[.side-menu--collapsed.side-menu--on-hover]:xl:w-[320px] group-[.side-menu--collapsed]:xl:w-[165px]"
        >
          <a
            class="relative flex items-center transition-[margin] duration-100 xl:ml-1.5 group-[.side-menu--collapsed.side-menu--on-hover]:xl:ml-1.5 group-[.side-menu--collapsed]:xl:ml-8"
            href=""
          >
            <img class="size-5" :src="logo" />
            <div
              class="dark:text-foreground text-background ml-3.5 text-nowrap transition-opacity group-[.side-menu--collapsed.side-menu--on-hover]:xl:opacity-100 group-[.side-menu--collapsed]:xl:opacity-0"
            >
              <span class="text-base font-medium">Midone </span>
              <span class="text-base font-light">Enigma</span>
            </div>
          </a>
          <a
            @click="toggleCompactMenu"
            class="toggle-compact-menu text-background dark:text-foreground border-background/20 bg-background/10 dark:bg-foreground/[.02] dark:border-foreground/[.11] relative ml-auto hidden items-center justify-center rounded-md border py-0.5 pl-0.5 pr-1 opacity-70 transition-[opacity,transform] hover:opacity-100 group-[.side-menu--collapsed]:xl:rotate-180 group-[.side-menu--collapsed.side-menu--on-hover]:xl:opacity-100 group-[.side-menu--collapsed]:xl:opacity-0 2xl:flex"
            href=""
          >
            <Lucide icon="ChevronLeft" />
          </a>
        </div>
        <AccountTrigger
          class="relative transition-[width] xl:mb-2 xl:mr-8"
          innerClass="border-foreground/[.11] dark:border-foreground/[.11] border-t py-3 pl-6 pr-5 opacity-90 hover:opacity-100 xl:border-b xl:border-t-0 xl:pb-6 xl:pl-8 xl:pt-2.5"
          avatarClass="h-11 w-11 border-background/70 dark:border-foreground/20 shadow-sm"
          textClass="text-primary dark:text-foreground w-full"
        >
          <AccountDropdown
            class="absolute top-0 left-[100%] origin-bottom-left"
            boxClass="absolute bottom-0 left-[100%] ml-2 xl:bottom-auto xl:top-0"
          />
        </AccountTrigger>
        <ScrollAreaRoot class="flex-1 min-h-0">
          <ScrollAreaViewport :class="[
            'pl-4 xl:pl-7 pr-4 xl:pr-14 pb-3',
            '[-webkit-mask-image:_linear-gradient(to_top,_rgba(0,_0,_0,_0),_black_30px),_linear-gradient(to_bottom,_rgba(0,_0,_0,_0),_black_30px)]',
            '[-webkit-mask-composite:_destination-in]',
          ].join(' ')">
            <ScrollAreaContent class="!min-w-auto">
              <SideMenu :menu="mainMenu" />
            </ScrollAreaContent>
          </ScrollAreaViewport>
          <ScrollAreaScrollbar class="bg-transparent xl:data-[orientation=vertical]:mr-9">
            <ScrollAreaThumb class="bg-foreground/10" />
          </ScrollAreaScrollbar>
          <ScrollAreaCorner />
        </ScrollAreaRoot>
      </div>
    </div>
    <div
      :class="[
        'content h-screen transition-[margin,width] duration-100 pt-32 pb-8 px-7 z-10 relative group',
        'before:absolute before:bottom-4 before:top-27 before:-ml-px before:right-4 before:opacity-[.07] before:left-4 xl:before:left-0 before:bg-foreground before:rounded-4xl',
        'after:absolute after:bottom-4 after:top-27 after:-ml-px after:right-4 after:left-4 xl:after:left-0 after:bg-[color-mix(in_oklch,_var(--color-background),_var(--color-foreground)_2%)] after:rounded-4xl after:border after:border-foreground/[.15] dark:after:opacity-[.59]',
        'xl:ml-[320px]',
        '[&.content--compact]:xl:ml-[165px]',
        { 'content--compact': compactMenu && !compactMenuOnHover },
      ]"
    >
      <div
        :class="[
          'relative h-full',
          '[--color-nav-foreground:var(--color-background)] dark:[--color-nav-foreground:var(--color-foreground)]',
        ]"
      >
        <div class="h-full overflow-x-hidden">
          <div
            @scroll="onScrollContent"
            :class="[
              'content__scroll-area relative z-20 -mr-7 h-full overflow-y-auto pl-4 pr-11 pb-5 pt-2 transition-[margin] duration-100 xl:pl-0',
              { '-ml-[155px]': compactMenu && compactMenuOnHover && !mobileMenuOpen },
            ]"
          >
            <div
              :class="[
                'top-bar group fixed inset-x-0 mt-3 top-0 z-50 pl-9 xl:pl-6 pr-9 xl:pr-5 transition-[margin,width] duration-100 xl:ml-[320px] xl:mr-5 group-[.content--compact]:xl:ml-[165px]',
                'before:inset-y-0 before:left-4 before:transition-[margin] before:duration-100 before:right-0 before:absolute xl:before:-ml-[320px] group-[.content--compact]:xl:before:-ml-[165px] before:bg-primary before:border before:border-primary dark:before:border-[color-mix(in_oklch,_var(--color-background),_white_27%)] dark:before:bg-[color-mix(in_oklch,_var(--color-background),_white_14%)] before:my-3 before:rounded-2xl before:mr-4 xl:before:-mr-1',
                'after:inset-0 after:left-8 after:transition-[margin] after:duration-100 after:right-0 after:absolute xl:after:-ml-[320px] group-[.content--compact]:xl:after:-ml-[165px] after:bg-primary/30 after:border after:border-primary/10 dark:after:border-[color-mix(in_oklch,_var(--color-background),_white_27%)] dark:after:bg-[color-mix(in_oklch,_var(--color-background),_white_14%)] after:z-[-1] after:mb-3 after:rounded-2xl after:mr-8 xl:after:mr-4',
                { scrolled: scrolled },
              ]"
            >
              <div :class="['relative z-20 flex h-[90px] items-center gap-5']">
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
                    class="ring-(--color)/40 size-full [--color:var(--color-nav-foreground)] rounded-full"
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
