import helper from './helper';
import lodash from './lodash';

export default app => {
    app.use(helper);
    app.use(lodash);
};
