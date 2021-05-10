import _ from "lodash";

const install = app => {
  app.config.globalProperties.$_ = _;
};

export { install as default };
