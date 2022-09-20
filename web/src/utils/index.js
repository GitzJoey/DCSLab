import helper from "./helper";
import colors from "./colors";
import pusher from "./pusher";
import laravel_echo from "./laravel-echo";

export default (app) => {
  app.use(helper);
  app.use(colors);
  app.use(pusher);
  app.use(laravel_echo)
};
