export default function() {
    function assetPath(assetName) {
        return '/images/' + assetName;
    }

    return {
        assetPath
    }
}
