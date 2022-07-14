import _ from "lodash";

export async function userHasRoles(to, userContext, next) {
    try {
        if (to.matched.some(r => r.meta.roles)) {
            let userRoles = userContext.roles_description.includes(',') ? userContext.roles_description.split(',') :  new Array(userContext.roles_description);
            let hasRoles = _.intersection(to.meta.roles, userRoles).length === 0 ? false:true;

            if (!hasRoles) next({ name: 'side-menu-error-code', params: { code: '401' } });
        }
    } catch (err) {
    }
}

export async function userHasPermissions(to, userContext, next) {
    try {
        if (to.matched.some(r => r.meta.permissions)) {
            let hasPermissions = false;

            hasPermissions = _.map(userContext.roles, (role) => {
                if (role.permissions_description.length !== 0) {
                    let found = _.intersection(to.meta.permissions, role.permissions_description.split(',')).length === 0 ? false:true;
                    if (found) return found;
                }
            });

            if (!hasPermissions) next({ name: 'side-menu-error-code', params: { code: '401' } });
        }
    } catch (err) {
    }
}