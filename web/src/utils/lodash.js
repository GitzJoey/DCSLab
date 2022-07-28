import _ from 'lodash'

const install = app => {
  app.config.globalProperties.$_ = _
  app.provide('$_', _);
}

export { install as default }
