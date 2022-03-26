import axios from "@/axios";
import _ from "lodash";

export async function canUserAccess(to, userContext, next) {
    try {
        if (to.matched.some(r => r.meta.acl)) {
            let userRoles = userContext.roles_description.includes(',') ? userContext.roles_description.split(',') :  new Array(userContext.roles_description);
            _.forEach(to.meta.acl, (val) => {
                
            });
        }
        /*
        const response = axios.post('/api/post/dashboard/core/user/access', {
            to: to.path
        });
        return (await response).data;
        */
       next();
    } catch (err) {
        
    }
}

export function checkPasswordExpiry(userContext, next) {
    if (userContext.password_expiry_day <= 0) {
        
    }
}

export function checkUserStatus(userContext, next) {
    if (userContext.profile !== undefined && userContext.profile.status === 0) {
        
    }
}