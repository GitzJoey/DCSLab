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

}