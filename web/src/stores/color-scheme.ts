import { defineStore } from 'pinia'

const colorSchemes = ['default', '1', '2', '3', '4', '5'] as const

export type ColorSchemes = (typeof colorSchemes)[number]

interface ColorSchemeState {
  colorSchemeValue: ColorSchemes
}

const getColorScheme = () => {
  const colorScheme = localStorage.getItem('colorScheme')
  return (
    colorSchemes.filter((item, key) => {
      return item === colorScheme
    })[0] ?? 'default'
  )
}

export const useColorSchemeStore = defineStore('colorScheme', {
  state: (): ColorSchemeState => ({
    colorSchemeValue: localStorage.getItem('colorScheme') === null ? 'default' : getColorScheme(),
  }),
  getters: {
    colorScheme(state) {
      if (localStorage.getItem('colorScheme') === null) {
        localStorage.setItem('colorScheme', 'default')
      }

      return state.colorSchemeValue
    },
  },
  actions: {
    setColorScheme(colorScheme: ColorSchemes) {
      localStorage.setItem('colorScheme', colorScheme)
      this.colorSchemeValue = colorScheme
    },
  },
})
