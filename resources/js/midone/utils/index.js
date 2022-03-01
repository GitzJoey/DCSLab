import helper from "./helper";
import lodash from "./lodash";
import colors from "./colors";

export default (app) => {
  app.use(helper);
  app.use(lodash);
  app.use(colors);
};
