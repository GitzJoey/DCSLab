<script setup lang="ts">
import { SheetRoot, SheetContent } from '@/components/ui/sheet'
import { Lucide } from '@/components/ui/lucide'
import { useThemeStore, type Themes } from '@/stores/theme'
import { useColorSchemeStore, type ColorSchemes } from '@/stores/color-scheme'
import { useDarkModeStore } from '@/stores/dark-mode'
import { ref } from 'vue'

const themeSwitcherSheet = ref(false)
const setThemeSwitcherSheet = (value: boolean) => {
  themeSwitcherSheet.value = value
}

const themeStore = useThemeStore()
const switchTheme = (theme: Themes['name']) => {
  useThemeStore().setTheme(theme)
  setThemeSwitcherSheet(false)
}
const switchLayout = (layout: Themes['layout']) => {
  useThemeStore().setLayout(layout)
  setThemeSwitcherSheet(false)
}

const setColorSchemeClass = () => {
  const el = document.querySelectorAll('html')[0]
  el?.setAttribute('data-theme', useColorSchemeStore().colorSchemeValue)
  if (useDarkModeStore().darkModeValue) el?.classList.add('dark')
}
const colorSchemeStore = useColorSchemeStore()
const switchColorScheme = (colorScheme: ColorSchemes) => {
  useColorSchemeStore().setColorScheme(colorScheme)
  setColorSchemeClass()
  setThemeSwitcherSheet(false)
}
setColorSchemeClass()

const setDarkModeClass = () => {
  const el = document.querySelectorAll('html')[0]
  useDarkModeStore().darkModeValue ? el?.classList.add('dark') : el?.classList.remove('dark')
}
const darkModeStore = useDarkModeStore()
const switchDarkMode = (darkMode: boolean) => {
  useDarkModeStore().setDarkMode(darkMode)
  setDarkModeClass()
  setThemeSwitcherSheet(false)
}
setDarkModeClass()

const themes: Array<Themes['name']> = ['rubick', 'icewall', 'tinker', 'enigma']
const layouts: Array<Themes['layout']> = ['side-menu', 'top-menu']
const colorSchemes: Array<ColorSchemes> = ['default', '1', '2', '3', '4', '5']

const themeImages = import.meta.glob<{
  default: string
}>('/src/assets/images/themes/*.{jpg,jpeg,png,svg}', { eager: true })
</script>

<template>
  <div>
    <SheetRoot
      :open="themeSwitcherSheet"
      @openChange="(details) => setThemeSwitcherSheet(details.open)"
    >
      <SheetContent>
        <a
          class="absolute inset-y-0 left-0 right-auto my-auto -ml-[60px] flex h-8 w-8 items-center justify-center rounded-full border border-white/90 bg-white/5 text-white/90 transition-all hover:rotate-180 hover:scale-105 hover:bg-white/10 focus:outline-none sm:-ml-[105px] sm:h-14 sm:w-14"
          @click="
            (event: MouseEvent) => {
              event.preventDefault()
              setThemeSwitcherSheet(false)
            }
          "
        >
          <Lucide class="h-3 w-3 stroke-[1] sm:h-8 sm:w-8" icon="X" />
        </a>
        <div class="absolute inset-0 px-7 py-5 overflow-y-scroll">
          <div class="flex flex-col">
            <div>
              <div class="text-base font-medium">Themes</div>
              <div class="mt-0.5 opacity-70">Choose your theme</div>
              <div class="mt-5 grid grid-cols-2 gap-x-5 gap-y-4">
                <div v-for="theme in themes" :key="theme">
                  <a
                    @click="
                      (event: MouseEvent) => {
                        event.preventDefault()
                        switchTheme(theme)
                      }
                    "
                    :class="[
                      'h-24 cursor-pointer bg-background overflow-hidden block rounded-(--radius) transition-all duration-100 ring-offset-4 ring-offset-background hover:scale-[105%]',
                      themeStore.theme.name == theme
                        ? 'ring-2 ring-foreground/20'
                        : 'ring-1 ring-foreground/10',
                    ]"
                  >
                    <img
                      class="w-full h-full rounded-(--radius)"
                      :src="themeImages['/src/assets/images/themes/' + theme + '.png']?.default"
                      alt="Midone - Admin Dashboard Template"
                    />
                  </a>
                  <div class="mt-2.5 text-center capitalize">
                    {{ theme }}
                  </div>
                </div>
              </div>
            </div>
            <div class="border-b border-dashed border-foreground/15 my-5"></div>
            <div>
              <div class="text-base font-medium">Layouts</div>
              <div class="mt-0.5 opacity-70">Choose your layout</div>
              <div class="mt-5 grid grid-cols-2 gap-x-5 gap-y-4">
                <a
                  v-for="layout in layouts"
                  :key="layout"
                  class="flex flex-col gap-3 transition-transform duration-100 hover:scale-[105%]"
                  href=""
                  @click="
                    (event: MouseEvent) => {
                      event.preventDefault()
                      switchLayout(layout)
                    }
                  "
                >
                  <div
                    :class="[
                      'dark:bg-foreground/20 h-20 overflow-hidden rounded-(--radius) transition-all ring-offset-4 ring-offset-background p-px',
                      themeStore.theme.layout == layout
                        ? 'ring-2 ring-foreground/20'
                        : 'ring-1 ring-foreground/10',
                    ]"
                  >
                    <div v-if="layout === 'side-menu'" class="flex size-full gap-1">
                      <div class="bg-foreground/10 h-full w-[20%]"></div>
                      <div class="bg-foreground/10 h-full w-full"></div>
                    </div>
                    <div v-if="layout === 'top-menu'" class="flex size-full flex-col gap-1">
                      <div class="bg-foreground/10 h-[30%]"></div>
                      <div class="bg-foreground/10 h-full w-full"></div>
                    </div>
                  </div>
                  <div class="text-center capitalize">{{ layout.replace('-', ' ') }}</div>
                </a>
              </div>
            </div>
            <div class="border-b border-dashed border-foreground/15 my-5"></div>
            <div>
              <div class="text-base font-medium">Accent Colors</div>
              <div class="mt-0.5 opacity-70">Choose your accent color</div>
              <div class="mt-5 grid grid-cols-6 gap-x-3 gap-y-4">
                <div v-for="colorScheme in colorSchemes" :key="colorScheme">
                  <a
                    @click="
                      (event: MouseEvent) => {
                        event.preventDefault()
                        switchColorScheme(colorScheme)
                      }
                    "
                    :data-theme="colorScheme"
                    :class="[
                      'h-10 cursor-pointer bg-primary block rounded-(--radius) transition-all duration-100 ring-offset-4 ring-offset-background hover:scale-[105%]',
                      colorSchemeStore.colorSchemeValue == colorScheme
                        ? 'ring-2 ring-foreground/20'
                        : 'ring-1 ring-foreground/10',
                    ]"
                  ></a>
                </div>
              </div>
            </div>
            <div class="border-b border-dashed border-foreground/15 my-5"></div>
            <div>
              <div class="text-base font-medium">Appearance</div>
              <div class="mt-0.5 opacity-70">Choose your appearance</div>
              <div class="mt-5 grid grid-cols-2 gap-x-5 gap-y-4">
                <div>
                  <a
                    @click="
                      (event: MouseEvent) => {
                        event.preventDefault()
                        switchDarkMode(false)
                      }
                    "
                    :class="[
                      'h-12 cursor-pointer bg-background block rounded-(--radius) transition-all duration-100 ring-offset-4 ring-offset-background hover:scale-[105%]',
                      !darkModeStore.darkModeValue
                        ? 'ring-2 ring-foreground/20'
                        : 'ring-1 ring-foreground/10',
                    ]"
                  >
                    <div class="h-full overflow-hidden rounded-(--radius) bg-slate-200"></div>
                  </a>
                  <div class="mt-2.5 text-center text-xs capitalize">Light</div>
                </div>
                <div>
                  <a
                    @click="
                      (event: MouseEvent) => {
                        event.preventDefault()
                        switchDarkMode(true)
                      }
                    "
                    :class="[
                      'h-12 cursor-pointer bg-background block rounded-(--radius) transition-all duration-100 ring-offset-4 ring-offset-background hover:scale-[105%]',
                      darkModeStore.darkModeValue
                        ? 'ring-2 ring-foreground/20'
                        : 'ring-1 ring-foreground/10',
                    ]"
                  >
                    <div class="h-full overflow-hidden rounded-(--radius) bg-slate-900"></div>
                  </a>
                  <div class="mt-2.5 text-center text-xs capitalize">Dark</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </SheetContent>
    </SheetRoot>
    <div
      :class="[
        'fixed inset-y-0 right-0 z-50 my-auto flex hover:w-20 transition-all w-14 h-12 cursor-pointer border-(--color)/50 items-center border justify-center rounded-l-full shadow-lg overflow-hidden bg-background/80 [--color:var(--color-primary)]',
        'before:bg-(--color)/20 before:absolute before:inset-0',
      ]"
      @click="
        (event: MouseEvent) => {
          event.preventDefault()
          setThemeSwitcherSheet(true)
        }
      "
    >
      <Lucide class="animate-spin" icon="Settings" />
    </div>
  </div>
</template>
