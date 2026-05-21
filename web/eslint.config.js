import pluginVue from 'eslint-plugin-vue'
import skipFormatting from '@vue/eslint-config-prettier'

export default [
  {
    files: ['**/*.{js,mjs,cjs,vue}'],
  },
  ...pluginVue.configs['flat/essential'],
  skipFormatting,
]
