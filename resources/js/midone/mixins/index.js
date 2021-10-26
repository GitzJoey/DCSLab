import { useI18n } from 'vue-i18n';
import routeFn from 'ziggy-js';
import { Ziggy } from '../ziggy/ziggy';

export default function() {
    const { t } = useI18n();

    function assetPath(assetName) {
        return '/images/' + assetName;
    }

    function isEmptyObject(obj) {
        return _.isEmpty(obj);
    }

    function route(name, params) {
        return routeFn (name, params, undefined, Ziggy);
    }

    return {
        t,
        assetPath,
        isEmptyObject,
        route,
    }
}
