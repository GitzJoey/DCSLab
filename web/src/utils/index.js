import helper from "./helper";
import lodash from "./lodash";
import colors from "./colors";
import pusher from "./pusher";

export default (app) => {
  app.use(helper);
  app.use(lodash);
  app.use(colors);
  app.use(pusher);
};
