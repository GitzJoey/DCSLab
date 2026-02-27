<script setup lang="ts">
  import { Slideover } from '@/components/Base/Headless';
  import Lucide from '@/components/Base/Lucide';
  import { useThemeStore, type Themes } from '@/stores/theme';
  import {
    useColorSchemeStore,
    type ColorSchemes,
  } from '@/stores/color-scheme';
  import { useDarkModeStore } from '@/stores/dark-mode';
  import { ref } from 'vue';

  const themeSwitcherSlideover = ref(false);
  const setThemeSwitcherSlideover = (value: boolean) => {
    themeSwitcherSlideover.value = value;
  };

  const themeStore = useThemeStore();
  const switchTheme = (theme: Themes['name']) => {
    useThemeStore().setTheme(theme);
  };
  const switchLayout = (layout: Themes['layout']) => {
    useThemeStore().setLayout(layout);
  };

  const setColorSchemeClass = () => {
    const el = document.querySelectorAll('html')[0];
    el.setAttribute('class', useColorSchemeStore().colorSchemeValue);
    useDarkModeStore().darkModeValue && el.classList.add('dark');
  };
  const colorSchemeStore = useColorSchemeStore();
  const switchColorScheme = (colorScheme: ColorSchemes) => {
    useColorSchemeStore().setColorScheme(colorScheme);
    setColorSchemeClass();
  };
  setColorSchemeClass();

  const setDarkModeClass = () => {
    const el = document.querySelectorAll('html')[0];
    useDarkModeStore().darkModeValue
      ? el.classList.add('dark')
      : el.classList.remove('dark');
  };
  const darkModeStore = useDarkModeStore();
  const switchDarkMode = (darkMode: boolean) => {
    useDarkModeStore().setDarkMode(darkMode);
    setDarkModeClass();
  };
  setDarkModeClass();

  const themes: Array<Themes['name']> = [
    'rubick',
    'icewall',
    'tinker',
    'enigma',
  ];
  const layouts: Array<Themes['layout']> = [
    'side-menu',
    'simple-menu',
    'top-menu',
  ];
  const colorSchemes: Array<ColorSchemes> = [
    'default',
    'theme-1',
    'theme-2',
    'theme-3',
    'theme-4',
  ];

  const themeImages = import.meta.glob<{
    default: string;
  }>('/src/assets/images/themes/*.{jpg,jpeg,png,svg}', { eager: true });
  const layoutImages = import.meta.glob<{
    default: string;
  }>('/src/assets/images/layouts/*.{jpg,jpeg,png,svg}', { eager: true });
</script>

<template>
  <div>
    <Slideover
      :open="themeSwitcherSlideover"
      @close="
        () => {
          setThemeSwitcherSlideover(false);
        }
      "
    >
      <Slideover.Panel>
        <a
          class="absolute inset-y-0 left-0 right-auto my-auto -ml-[60px] flex h-8 w-8 items-center justify-center rounded-full border border-white/90 bg-white/5 text-white/90 transition-all hover:rotate-180 hover:scale-105 hover:bg-white/10 focus:outline-none sm:-ml-[105px] sm:h-14 sm:w-14"
          @click="
            (event: MouseEvent) => {
              event.preventDefault();
              setThemeSwitcherSlideover(false);
            }
          "
        >
          <Lucide class="h-3 w-3 stroke-[1] sm:h-8 sm:w-8" icon="X" />
        </a>
        <Slideover.Description class="p-0">
          <div class="flex flex-col">
            <div class="px-8 pt-6 pb-8">
              <div class="text-base font-medium">Themes</div>
              <div class="mt-0.5 text-slate-500">Choose your theme</div>
              <div class="mt-5 grid grid-cols-2 gap-x-5 gap-y-3.5">
                <div v-for="theme in themes">
                  <a
                    @click="
                      (event: MouseEvent) => {
                        event.preventDefault();
                        switchTheme(theme);
                      }
                    "
                    :class="[
                      'h-28 cursor-pointer bg-slate-50 box p-1 block',
                      themeStore.theme.name == theme
                        ? 'border-2 border-theme-1/60'
                        : '',
                    ]"
                  >
                    <div
                      class="w-full h-full overflow-hidden rounded-md image-fit"
                    >
                      <img
                        class="w-full h-full"
                        :src="
                          themeImages[
                            '/src/assets/images/themes/' + theme + '.png'
                          ].default
                        "
                        alt="DCSLab"
                      />
                    </div>
                  </a>
                  <div class="mt-2.5 text-center text-xs capitalize">
                    {{ theme }}
                  </div>
                </div>
              </div>
            </div>
            <div class="border-b border-dashed"></div>
            <div class="px-8 pt-6 pb-8">
              <div class="text-base font-medium">Layouts</div>
              <div class="mt-0.5 text-slate-500">Choose your layout</div>
              <div class="mt-5 grid grid-cols-3 gap-x-5 gap-y-3.5">
                <div v-for="layout in layouts">
                  <a
                    @click="
                      (event: MouseEvent) => {
                        event.preventDefault();
                        switchLayout(layout);
                      }
                    "
                    :class="[
                      'h-24 cursor-pointer bg-slate-50 box p-1 block',
                      themeStore.theme.layout == layout
                        ? 'border-2 border-theme-1/60'
                        : '',
                    ]"
                  >
                    <div class="w-full h-full overflow-hidden rounded-md">
                      <img
                        class="w-full h-full"
                        :src="
                          layoutImages[
                            '/src/assets/images/layouts/' + layout + '.png'
                          ].default
                        "
                        alt="DCSLab"
                      />
                    </div>
                  </a>
                  <div class="mt-2.5 text-center text-xs capitalize">
                    {{ layout.replace('-', ' ') }}
                  </div>
                </div>
              </div>
            </div>
            <div class="border-b border-dashed"></div>
            <div class="px-8 pt-6 pb-8">
              <div class="text-base font-medium">Accent Colors</div>
              <div class="mt-0.5 text-slate-500">Choose your accent color</div>
              <div class="mt-5 grid grid-cols-2 gap-3.5">
                <div v-for="colorScheme in colorSchemes">
                  <a
                    @click="
                      (event: MouseEvent) => {
                        event.preventDefault();
                        switchColorScheme(colorScheme);
                      }
                    "
                    :class="[
                      'h-14 cursor-pointer bg-slate-50 box p-1 border-slate-300/80 block',
                      '[&.active]:border-2 [&.active]:border-theme-1/60',
                      colorSchemeStore.colorSchemeValue == colorScheme
                        ? 'active'
                        : '',
                    ]"
                  >
                    <div class="h-full overflow-hidden rounded-md">
                      <div class="flex items-center h-full gap-1 -mx-2">
                        <div
                          :class="[
                            'w-1/2 h-[200%] bg-theme-1 rotate-12',
                            colorScheme,
                          ]"
                        ></div>
                        <div
                          :class="[
                            'w-1/2 h-[200%] bg-theme-2 rotate-12',
                            colorScheme,
                          ]"
                        ></div>
                      </div>
                    </div>
                  </a>
                </div>
              </div>
            </div>
            <div class="border-b border-dashed"></div>
            <div class="px-8 pt-6 pb-8">
              <div class="text-base font-medium">Appearance</div>
              <div class="mt-0.5 text-slate-500">Choose your appearance</div>
              <div class="mt-5 grid grid-cols-2 gap-3.5">
                <div>
                  <a
                    @click="
                      (event: MouseEvent) => {
                        event.preventDefault();
                        switchDarkMode(false);
                      }
                    "
                    :class="[
                      'h-12 cursor-pointer bg-slate-50 box p-1 border-slate-300/80 block',
                      '[&.active]:border-2 [&.active]:border-theme-1/60',
                      !darkModeStore.darkModeValue ? 'active' : '',
                    ]"
                  >
                    <div
                      class="h-full overflow-hidden rounded-md bg-slate-200"
                    ></div>
                  </a>
                  <div class="mt-2.5 text-center text-xs capitalize">Light</div>
                </div>
                <div>
                  <a
                    @click="
                      (event: MouseEvent) => {
                        event.preventDefault();
                        switchDarkMode(true);
                      }
                    "
                    :class="[
                      'h-12 cursor-pointer bg-slate-50 box p-1 border-slate-300/80 block',
                      '[&.active]:border-2 [&.active]:border-theme-1/60',
                      darkModeStore.darkModeValue ? 'active' : '',
                    ]"
                  >
                    <div
                      class="h-full overflow-hidden rounded-md bg-slate-900"
                    ></div>
                  </a>
                  <div class="mt-2.5 text-center text-xs capitalize">Dark</div>
                </div>
              </div>
            </div>
          </div>
        </Slideover.Description>
      </Slideover.Panel>
    </Slideover>
    <div
      class="fixed bottom-0 right-0 z-50 flex items-center justify-center mb-5 mr-5 text-white rounded-full shadow-lg cursor-pointer h-14 w-14 bg-theme-1"
      @click="
        (event: MouseEvent) => {
          event.preventDefault();
          setThemeSwitcherSlideover(true);
        }
      "
    >
      <Lucide class="w-5 h-5 hover:animate-spin" icon="Settings" />
    </div>
  </div>
</template>
