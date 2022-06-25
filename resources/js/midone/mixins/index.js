function assetPath(assetName) {
    return '/images/' + assetName;
}

function getCachedDDL(ddlname) {
    let dcslabSystems = sessionStorage.getItem('DCSLAB_SYSTEM') == null ? new Object() : JSON.parse(sessionStorage.getItem('DCSLAB_SYSTEM'));
    
    return dcslabSystems.hasOwnProperty(ddlname) ? dcslabSystems[ddlname] : null;
}

function setCachedDDL(ddlname, value) {
    let dcslabSystems = sessionStorage.getItem('DCSLAB_SYSTEM') == null ? new Object() : JSON.parse(sessionStorage.getItem('DCSLAB_SYSTEM'));

    dcslabSystems[ddlname] = value;

    sessionStorage.setItem('DCSLAB_SYSTEM', JSON.stringify(dcslabSystems));
}

export { assetPath, getCachedDDL, setCachedDDL }
