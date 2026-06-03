import { defineStore } from 'pinia'
import RubickSideMenu from '@/themes/Rubick/SideMenu'
import RubickTopMenu from '@/themes/Rubick/TopMenu'
import IcewallSideMenu from '@/themes/Icewall/SideMenu'
import IcewallTopMenu from '@/themes/Icewall/TopMenu'
import TinkerSideMenu from '@/themes/Tinker/SideMenu'
import TinkerTopMenu from '@/themes/Tinker/TopMenu'
import EnigmaSideMenu from '@/themes/Enigma/SideMenu'
import EnigmaTopMenu from '@/themes/Enigma/TopMenu'

export const themes = [
  {
    name: 'rubick',
    layout: 'side-menu',
    component: RubickSideMenu,
  },
  {
    name: 'rubick',
    layout: 'top-menu',
    component: RubickTopMenu,
  },
  {
    name: 'icewall',
    layout: 'side-menu',
    component: IcewallSideMenu,
  },
  {
    name: 'icewall',
    layout: 'top-menu',
    component: IcewallTopMenu,
  },
  {
    name: 'tinker',
    layout: 'side-menu',
    component: TinkerSideMenu,
  },
  {
    name: 'tinker',
    layout: 'top-menu',
    component: TinkerTopMenu,
  },
  {
    name: 'enigma',
    layout: 'side-menu',
    component: EnigmaSideMenu,
  },
  {
    name: 'enigma',
    layout: 'top-menu',
    component: EnigmaTopMenu,
  },
] as const

export type Themes = (typeof themes)[number]

interface ThemeState {
  themeValue: {
    name: Themes['name']
    layout: Themes['layout']
  }
}

export const getTheme = (search?: { name: Themes['name']; layout: Themes['layout'] }) => {
  const searchValues =
    search === undefined
      ? {
          name: localStorage.getItem('theme'),
          layout: localStorage.getItem('layout'),
        }
      : search
  return (
    themes.filter((item, key) => {
      return item.name === searchValues.name && item.layout === searchValues.layout
    })[0] || themes[0]
  )
}

export const useThemeStore = defineStore('theme', {
  state: (): ThemeState => ({
    themeValue: {
      name: localStorage.getItem('theme') === null ? themes[0].name : getTheme().name,
      layout: localStorage.getItem('layout') === null ? themes[0].layout : getTheme().layout,
    },
  }),
  getters: {
    theme(state) {
      if (localStorage.getItem('theme') === null) {
        localStorage.setItem('theme', 'rubick')
      }

      if (localStorage.getItem('layout') === null) {
        localStorage.setItem('layout', 'side-menu')
      }

      return state.themeValue
    },
  },
  actions: {
    setTheme(theme: Themes['name']) {
      this.themeValue = {
        name: theme,
        layout: this.themeValue.layout,
      }

      localStorage.setItem('theme', theme)
    },
    setLayout(layout: Themes['layout']) {
      this.themeValue = {
        name: this.themeValue.name,
        layout: layout,
      }

      localStorage.setItem('layout', layout)
    },
  },
})
