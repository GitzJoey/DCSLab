import { useI18n } from 'vue-i18n';

export default function() {
    const { t } = useI18n();

    function assetPath(assetName) {
        return '/images/' + assetName;
    }

    function isEmptyObject(obj) {
        return _.isEmpty(obj);
    }

    return {
        t,
        assetPath,
        isEmptyObject,
    }
}
