import { useI18n } from 'vue-i18n';

export default function() {
    const { t } = useI18n();

    function assetPath(assetName) {
        return '/images/' + assetName;
    }

    return {
        t,
        assetPath,
    }
}
